<?php
class Vortex_Checkout_Mapper_Order
{
    /**
     * @var Vortex_Checkout_Mapper_Order_Items
     */
    protected $basketItemsMapper;

    /**
     * @var Vortex_Checkout_Mapper_Order_Address
     */
    protected $basketAddressMapper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $coreHelper;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * Vortex_Checkout_Mapper_Basket constructor.
     * @param Vortex_Checkout_Mapper_Order_Items $basketItemsMapper
     * @param Vortex_Checkout_Mapper_Order_Address $basketAddressMapper
     * @param Mage_Core_Helper_Data $coreHelper
     */
    public function __construct(
        Vortex_Checkout_Mapper_Order_Items $basketItemsMapper,
        Vortex_Checkout_Mapper_Order_Address $basketAddressMapper,
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
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Quote
     */
    private function getOrderQuote(Mage_Sales_Model_Order $order)
    {
        if (is_null($this->quote)) {
            $this->quote = Mage::getModel('sales/quote')->load($order->getQuoteId())
                ->setGiftCardsTotalCollected(false)
                ->collectTotals();
        }

        return $this->quote;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    private function mapDiscounts(Mage_Sales_Model_Order $order)
    {
        $quote = $this->getOrderQuote($order);
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

    /**
     * @param Mage_Sales_Model_Order $order
     * @return object
     */
    private function getDataLayerUpdates(Mage_Sales_Model_Order $order)
    {
        $layout = Mage::getSingleton('core/layout');
        /** @var $layout Mage_Core_Model_Layout */

        $layout->getUpdate()->addHandle('default');
        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateBlocks();

        $block = $layout->getBlock('googletagmanager_order');
        /** @var $block Yireo_GoogleTagManager_Block_Order */
        if (!$block) {
            return new stdClass();
        }

        $block->setOrder($order);
        // This must be triggered as the attributes are added in the template
        $block->toHtml();
        return $block->getAttributes();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    private function mapAvailableShippingMethods(Mage_Sales_Model_Order $order)
    {
        $shippingRates = [];
        foreach ($order->getShippingAddress()->getAllShippingRates() as $shippingRate) {
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
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    private function mapGiftCards(Mage_Sales_Model_Order $order)
    {
        $mappedGiftCards = [];
        $cards = Mage::helper('enterprise_giftcardaccount')->getCards($order);
        if (is_array($cards)) {
            foreach ($cards as $card) {
                $mappedGiftCards[] = [
                    'card_number' => $card['c'],
                    'amount' => $this->formatPrice($card['ba']),
                    'amount_with_symbol' => $this->formatPriceWithSymbol($card['ba']),
                ];
            }
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
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function map(Mage_Sales_Model_Order $order)
    {
        $response = [];

        $response['order_id'] = $order->getId();
        $response['increment_id'] = $order->getIncrementId();

        $quote = $this->getOrderQuote($order);
        $totals = $quote->getTotals();

        $shipping = array_key_exists('shipping', $totals) ? $totals['shipping']->getValue() : 0;
        $response['shipping'] = $this->formatPrice($shipping);
        $response['shipping_with_symbol'] = $this->formatPriceWithSymbol($shipping);

        $subtotalInclTax = $this->getSubtotalInclTax($quote);
        $response['subtotal_incl_tax'] = $this->formatPrice($subtotalInclTax);
        $response['subtotal_incl_tax_currency'] = $this->formatPriceWithSymbol($subtotalInclTax);

        $discount = array_key_exists('discount', $totals) ? $totals['discount']->getValue() : 0;
        $response['discount_total'] = $this->formatPrice(-$discount);
        $response['discount_total_with_symbol'] = $this->formatPriceWithSymbol(-$discount);

        $response['discount_code'] = $order->getCouponCode();

        if ($order->hasGiftCards()) {
            $response['gift_cards'] = $this->mapGiftCards($order);
            $response['gift_card_total'] = $this->formatPrice($this->calculateGiftCardTotal($response['gift_cards']));
            $response['gift_card_total_currency'] = $this->formatPriceWithSymbol($response['gift_card_total']);
        } else {
            $response['gift_cards'] = [];
            $response['gift_card_total'] = $this->formatPrice(0);
            $response['gift_card_total_currency'] = $this->formatPriceWithSymbol($response['gift_card_total']);
        }

        $response['discounts'] = $this->mapDiscounts($order);
        $discountWithoutShipping = $this->calculateDiscountWithoutShipping($response['discounts']);

        $response['subtotal_incl_discount'] = $this->formatPrice(
            $response['subtotal_incl_tax'] - $discountWithoutShipping - $response['gift_card_total']
        );
        $response['subtotal_incl_discount_currency'] = $this->formatPriceWithSymbol($response['subtotal_incl_discount']);

        $response['total'] = $this->formatPrice($order->getGrandTotal());
        $response['total_with_symbol'] = $this->formatPriceWithSymbol($order->getGrandTotal());
        $response['item_count'] = $order->getTotalItemCount() ?: 0;

        if ($order->getCustomerIsGuest()) {
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->loadByEmail($order->getCustomerEmail());

            $response['is_guest'] = !$customer || !$customer->getId();
        } else {
            $response['is_guest'] = false;
        }

        if ($order->getShippingAddress() && $order->getShippingAddress()->getId()) {
            $response['shipping_method'] = $order->getShippingDescription();
            $response['available_shipping_methods'] = $this->mapAvailableShippingMethods($order);
            $response['shipping_address'] = $this->basketAddressMapper->map($order->getShippingAddress());
        } else {
            $response['shipping_address'] = new stdClass;
            $response['available_shipping_methods'] = [];
            $response['shipping_method'] = '';
        }

        if ($order->getBillingAddress() && $order->getBillingAddress()->getId()) {
            $response['billing_address'] = $this->basketAddressMapper->map($order->getBillingAddress());
        } else {
            $response['billing_address'] = new stdClass;
        }

        $response['is_click_and_collect'] = $order->getIsClickAndCollect() ? true : false;
        if ($response['is_click_and_collect']) {
            $response['click_and_collect_store'] = $this->mapClickAndCollectStore($order->getClickAndCollectStoreId());
        }

        $response['payment_method'] = $order->getPayment()->getMethodInstance()->getTitle();
        $response['items'] = $this->basketItemsMapper->map($order);

        $response['customer_email'] = $order->getCustomerEmail();

        $response['data_layer'] = $this->getDataLayerUpdates($order);

        return $response;
    }
}