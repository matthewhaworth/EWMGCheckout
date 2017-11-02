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
     * Vortex_Checkout_Mapper_Basket constructor.
     * @param Vortex_Checkout_Mapper_Basket_Items $basketItemsMapper
     * @param Vortex_Checkout_Mapper_Basket_Address $basketAddressMapper
     * @param Mage_Core_Helper_Data $coreHelper
     */
    public function __construct(
        Vortex_Checkout_Mapper_Basket_Items $basketItemsMapper,
        Vortex_Checkout_Mapper_Basket_Address $basketAddressMapper,
        Mage_Core_Helper_Data $coreHelper
    ) {
        $this->basketItemsMapper = $basketItemsMapper;
        $this->basketAddressMapper = $basketAddressMapper;
        $this->coreHelper = $coreHelper;

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
        $response['subtotal'] = $this->formatPrice($quote->getSubtotal());
        $response['subtotal_with_symbol'] = $this->formatPriceWithSymbol($quote->getSubtotal());
        $response['subtotal_incl_tax'] = $this->formatPrice($this->getSubtotalInclTax($quote));
        $response['subtotal_incl_tax_currency'] = $this->formatPriceWithSymbol($response['subtotal_incl_tax']);
        $response['shipping'] = $this->formatPrice($quote->getShippingAddress()->getShippingAmount());
        $response['shipping_with_symbol'] = $this->formatPriceWithSymbol($quote->getShippingAddress()->getShippingAmount());
        $response['discount_code'] = $quote->getCouponCode();
        $response['discount_total'] = $this->formatPrice(-$quote->getShippingAddress()->getDiscountAmount());
        $response['discount_total_with_symbol'] = $this->formatPriceWithSymbol(-$quote->getShippingAddress()->getDiscountAmount());
        $response['discounts'] = $this->mapDiscounts($quote);
        $response['total'] = $this->formatPrice($quote->getGrandTotal());
        $response['total_with_symbol'] = $this->formatPriceWithSymbol($quote->getGrandTotal());
        $response['item_count'] = $quote->getItemsCount() ?: 0;

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

        if ($quote->hasGiftCards()) {
            $response['gift_cards'] = $this->mapGiftCards($quote);
        } else {
            $response['gift_cards'] = [];
        }

        $response['is_click_and_collect'] = $quote->getIsClickAndCollect() ? true : false;
        if ($response['is_click_and_collect']) {
            $response['click_and_collect_store'] = $this->mapClickAndCollectStore($quote->getClickAndCollectStoreId());
        }

        $response['items'] = $this->basketItemsMapper->map($quote);

        return $response;
    }
}