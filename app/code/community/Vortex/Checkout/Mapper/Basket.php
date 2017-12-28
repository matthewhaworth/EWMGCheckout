<?php
class Vortex_Checkout_Mapper_Basket
{
    /**
     * @var Vortex_Checkout_Mapper_Basket_Items
     */
    protected $basketItemsMapper;

    /**
     * @var Vortex_Checkout_Mapper_Basket_Address
     */
    protected $basketAddressMapper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $checkoutSession;

    /**
     * Vortex_Checkout_Mapper_Basket constructor.
     * @param Vortex_Checkout_Mapper_Basket_Items $basketItemsMapper
     * @param Vortex_Checkout_Mapper_Basket_Address $basketAddressMapper
     * @param Mage_Core_Helper_Data $coreHelper
     * @param Mage_Checkout_Model_Session $checkoutSession
     */
    public function __construct(
        Vortex_Checkout_Mapper_Basket_Items $basketItemsMapper,
        Vortex_Checkout_Mapper_Basket_Address $basketAddressMapper,
        Mage_Core_Helper_Data $coreHelper,
        Mage_Checkout_Model_Session $checkoutSession
    ) {
        $this->basketItemsMapper = $basketItemsMapper;
        $this->basketAddressMapper = $basketAddressMapper;
        $this->coreHelper = $coreHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param float $price
     * @return float
     */
    private function formatPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * @param float $price
     * @return string
     */
    private function formatPriceWithSymbol($price)
    {
        return $this->coreHelper->formatCurrency($price, false);
    }

    /**
     * Map messages that are placed into the session by third parties i.e. observers, paypal, etc.
     * We won't propagate success messages, as success is virtually always dealt with explicitly by this checkout.
     *
     * @return array
     */
    private function mapSessionMessages()
    {
        $errors = [];

        foreach ($this->checkoutSession->getMessages(true)->getItems() as $message) {
            /** @var $message Mage_Core_Model_Message_Abstract */

            // Don't propagate success messages, the checkout will handle anything that needs confirming
            if ($message->getType() !== Mage_Core_Model_Message::SUCCESS) {
                $errors[] = $message->getText();
            }
        }

        return $errors;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    private function mapDiscounts(Mage_Sales_Model_Quote $quote)
    {
        $discountBreakdown = $quote->getDiscountBreakdown();
        $mappedDiscountAmount = [];

        if(!$discountBreakdown){
            return [];
        }

        foreach ($discountBreakdown as $discount) {
            $mappedDiscountAmount[] = [
                'discount_label' => $discount['discount_label'],
                'discount_amount' => $this->formatPrice($discount['discount_amount']),
                'discount_amount_with_symbol' => $this->formatPriceWithSymbol($discount['discount_amount']),
                'discount_code' => $discount['discount_code']
            ];
        }

        return $mappedDiscountAmount;
    }

    private function mapClickAndCollectStore($storeId)
    {
        $store = Mage::getModel('storefinder/store')->load($storeId);
        return [
            'id' => $store->getId(),
            'name' => $store->getName(),
            'address' => implode(", ", $this->getStoreAddress($store)),
            'opening_hours' => array_filter(explode("\n", $store->getOpeningHours()), 'strlen'),
            'distance' => number_format($store->getDistance(), 1),
            'geolocation' => ['lat' => (float) $store->getLatitude(), 'lng' => (float) $store->getLongitude()]
        ];
    }

    private function getStoreAddress($store)
    {
        return array_filter(
            array_values(
                array(
                    'city'     => $store->getCity(),
                    'county'   => $store->getCounty(),
                    'postcode' => $store->getPostcode()
                )
            ), 'strlen');
    }

    private function mapAvailableShippingMethods(Mage_Sales_Model_Quote $quote)
    {
        $shippingRates = [];
        foreach ($quote->getShippingAddress()->getAllShippingRates() as $shippingRate) {
            /** @var $shippingRate Mage_Sales_Model_Quote_Address_Rate */
            $shippingRates[] = [
                'code' => $shippingRate->getCode(),
                'method' => $shippingRate->getMethod(),
                'method_title' => $shippingRate->getMethodTitle(),
                'method_description' => $shippingRate->getMethodDescription(),
                'price' => $this->formatPrice($shippingRate->getPrice()),
                'price_with_symbol' => $this->formatPriceWithSymbol($shippingRate->getPrice())
            ];
        }

        return $shippingRates;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    private function mapGiftCards(Mage_Sales_Model_Quote $quote)
    {
        // This is the only way to obtain a total from a quote
        $giftCardTotal = null;
        foreach ($quote->getTotals() as $total) {
            if ($total->getCode() === 'giftcardaccount') {
                $giftCardTotal = $total;
            }
        }

        if ($giftCardTotal === null) {
            return [];
        }

        $giftCards = $giftCardTotal->getGiftCards();

        $mappedGiftCards = [];
        foreach ($giftCards as $giftCard) {
            $mappedGiftCards[] = [
                'card_number' => $giftCard['c'],
                'amount' => $this->formatPrice($giftCard['ba']),
                'amount_with_symbol' => $this->formatPriceWithSymbol($giftCard['ba'])
            ];
        }

        return $mappedGiftCards;
    }

    /**
     * @param array $giftCards
     * @return float
     */
    private function calculateGiftCardTotal(array $giftCards)
    {
        $giftCardTotal = 0;
        foreach ($giftCards as $giftCard) {
            $giftCardTotal += $giftCard['amount'];
        }

        return $giftCardTotal;
    }

    /**
     * @param array $discounts
     * @return float
     */
    private function calculateDiscountWithoutShipping(array $discounts)
    {
        $discountWithoutShipping = 0.0;
        foreach ($discounts as $discount) {
            $discountWithoutShipping += $discount['discount_amount'];
        }

        return $discountWithoutShipping;
    }

    /**
     * There is no way in Magento to get the subtotal including tax, but excluding delivery, discounts, etc
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    private function getSubtotalInclTax(Mage_Sales_Model_Quote $quote)
    {
        $items = $quote->getAllItems();
        $priceInclVat = 0;
        foreach ($items as $item) {
            $priceInclVat += $item->getRowTotalInclTax();
        }
        return $priceInclVat;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function map(Mage_Sales_Model_Quote $quote)
    {
        $response = [];

        $response['basket_id'] = $quote->getId();

        $totals = $quote->getTotals();

        $shipping = array_key_exists('shipping', $totals) ? $totals['shipping']->getValue() : 0;
        $response['shipping'] = $this->formatPrice($shipping);
        $response['shipping_with_symbol'] = $this->formatPriceWithSymbol($shipping);

        $subtotalInclTax = $this->getSubtotalInclTax($quote);
        $response['subtotal_incl_tax'] = $this->formatPrice($subtotalInclTax);
        $response['subtotal_incl_tax_currency'] = $this->formatPriceWithSymbol($subtotalInclTax);

        $response['discount_code'] = $quote->getCouponCode();

        $discount = array_key_exists('discount', $totals) ? $totals['discount']->getValue() : 0;
        $response['discount_total'] = $this->formatPrice(-$discount);
        $response['discount_total_with_symbol'] = $this->formatPriceWithSymbol(-$discount);

        if ($quote->hasGiftCards()) {
            $response['gift_cards'] = $this->mapGiftCards($quote);
            $response['gift_card_total'] = $this->formatPrice($this->calculateGiftCardTotal($response['gift_cards']));
            $response['gift_card_total_currency'] = $this->formatPriceWithSymbol($response['gift_card_total']);
        } else {
            $response['gift_cards'] = [];
            $response['gift_card_total'] = $this->formatPrice(0);
            $response['gift_card_total_currency'] = $this->formatPriceWithSymbol($response['gift_card_total']);
        }

        $response['discounts'] = $this->mapDiscounts($quote);
        $discountWithoutShipping = $this->calculateDiscountWithoutShipping($response['discounts']);

        $response['subtotal_incl_discount'] = $this->formatPrice($response['subtotal_incl_tax'] - $discountWithoutShipping);
        $response['subtotal_incl_discount_currency'] = $this->formatPriceWithSymbol($response['subtotal_incl_discount']);

        $response['total'] = $this->formatPrice($quote->getGrandTotal());
        $response['total_with_symbol'] = $this->formatPriceWithSymbol($quote->getGrandTotal());
        $response['item_count'] = $quote->getItemsCount() ?: 0;

        $response['is_virtual'] = (boolean) $quote->isVirtual();

        if ($quote->getShippingAddress()->getId()) {
            $response['shipping_method'] = $quote->getShippingAddress()->getShippingMethod();
            $response['available_shipping_methods'] = $this->mapAvailableShippingMethods($quote);
            $response['shipping_address'] = $this->basketAddressMapper->map($quote->getShippingAddress());
        } else {
            $response['shipping_address'] = new stdClass;
            $response['available_shipping_methods'] = [];
            $response['shipping_method'] = '';
        }

        if ($quote->getBillingAddress()->getId()) {
            $response['billing_address'] = $this->basketAddressMapper->map($quote->getBillingAddress());
        } else {
            $response['billing_address'] = new stdClass;
        }

        $response['is_click_and_collect'] = $quote->getIsClickAndCollect() ? true : false;
        if ($response['is_click_and_collect']) {
            $response['click_and_collect_store'] = $this->mapClickAndCollectStore($quote->getClickAndCollectStoreId());
        }

        $response['items'] = $this->basketItemsMapper->map($quote);

        // Irrelevant to basket really, included out of necessity of reducing requests
        $response['errors'] = $this->mapSessionMessages();

        return $response;
    }
}