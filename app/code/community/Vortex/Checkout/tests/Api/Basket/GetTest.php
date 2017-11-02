<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Basket_GetTest extends TestCase
{
    public function testNoBasket()
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quote = Mage::getModel('sales/quote');

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $basketGetResponse = $basketGet->execute([], $request, $response);

        unset($basketGetResponse['available_shipping_methods']);

        $this->assertEquals([
            'basket_id' => null,
            'subtotal' => '0.00',
            'subtotal_with_symbol' => '£0.00',
            'shipping' => '0.00',
            'shipping_with_symbol' => '£0.00',
            'discounts' => [],
            'discount_code' => null,
            'discount_total' => '0.00',
            'discount_total_with_symbol' => '£0.00',
            'total' => '0.00',
            'total_with_symbol' => '£0.00',
            'item_count' => 0,
            'items' => []
        ], $basketGetResponse);
    }

    public function testEmptyBasket()
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quote = Mage::getModel('sales/quote')->setId(1);
        $quote->setDoNotCollectDiscountBreakdown(true);

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $basketGetResponse = $basketGet->execute([], $request, $response);

        unset($basketGetResponse['available_shipping_methods']);
        // Not interested in addresses in this test, just that they're there

        $this->assertEquals([
            'basket_id' => 1,
            'subtotal' => '0.00',
            'subtotal_with_symbol' => '£0.00',
            'shipping' => '0.00',
            'shipping_with_symbol' => '£0.00',
            'discounts' => [],
            'discount_code' => null,
            'discount_total' => '0.00',
            'discount_total_with_symbol' => '£0.00',
            'total' => '0.00',
            'total_with_symbol' => '£0.00',
            'item_count' => 0,
            'items' => []
        ], $basketGetResponse);
    }

    public function testSimpleProducts()
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quoteAddress = Mage::getModel('sales/quote_address');
        $quote = Mage::getModel('sales/quote')->setId(1);
        /* @var $quote Mage_Sales_Model_Quote */
        $quote->setDoNotCollectDiscountBreakdown(true);

        $quote->setBillingAddress($quoteAddress);
        $quote->setShippingAddress($quoteAddress);

        $items = [];
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(1, 'test', 10, 'test-product-1'), 2);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(2, 'test', 15, 'test-product-2'), 1);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(3, 'test', 5, 'test-product-3'),1);

        $itemsCollection = new Varien_Data_Collection();
        foreach ($items as $item) {
            $itemsCollection->addItem($item);
        }

        $quote->setItemsCollection($items);

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $basketGetResponse = $basketGet->execute([], $request, $response);

        // Remove images from items as they're tricky to test, and another test covers it
        $basketGetResponse['items'] = array_map(function($basketItem) {
            unset($basketItem['image']); return $basketItem;
        }, $basketGetResponse['items']);

        unset($basketGetResponse['available_shipping_methods']);

        $this->assertEquals([
            'basket_id' => 1,
            'subtotal' => '40.00',
            'subtotal_with_symbol' => '£40.00',
            'shipping' => '0.00',
            'shipping_with_symbol' => '£0.00',
            'discounts' => [],
            'discount_code' => null,
            'discount_total' => '0.00',
            'discount_total_with_symbol' => '£0.00',
            'total' => '40.00',
            'total_with_symbol' => '£40.00',
            'item_count' => 3,
            'items' => [
                ['item_id' => '11', 'product_id' => 1, 'name' => 'test', 'price' => '10.00', 'price_symbol' => '£10.00', 'qty' => 2],
                ['item_id' => '21', 'product_id' => 2, 'name' => 'test', 'price' => '15.00', 'price_symbol' => '£15.00', 'qty' => 1],
                ['item_id' => '31', 'product_id' => 3, 'name' => 'test', 'price' => '5.00', 'price_symbol' => '£5.00', 'qty' => 1]
            ]
        ], $basketGetResponse);
    }

    public function testSimpleProductsWithShipping()
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getShippingInclTax', 'getShippingAmount', 'getShippingMethod', 'collectTotals'])
            ->getMock();

        /* @var $quoteAddress Mage_Sales_Model_Quote_Address */
        $quoteAddress->method('getShippingInclTax')->willReturn(5);
        $quoteAddress->method('getShippingAmount')->willReturn(5);
        $quoteAddress->method('getShippingMethod')->willReturn('flatrate');


        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['collectTotals', 'getBillingAddress', 'getShippingAddress', 'getId', 'getSubtotal', 'getGrandTotal', 'getItemsCount'])
            ->getMock();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote->method('getBillingAddress')->willReturn($quoteAddress);
        $quote->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->method('getId')->willReturn(1);
        $quote->method('getSubtotal')->willReturn(40.00);
        $quote->method('getGrandTotal')->willReturn(45.00);
        $quote->method('getItemsCount')->willReturn(3);

        $items = [];
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(1, 'test', 10, 'test-product-1'), 2);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(2, 'test', 15, 'test-product-2'), 1);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(3, 'test', 5, 'test-product-3'),1);

        $itemsCollection = new Varien_Data_Collection();
        foreach ($items as $item) {
            $itemsCollection->addItem($item);
        }

        $quote->setItemsCollection($items);

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $basketGetResponse = $basketGet->execute([], $request, $response);

        // Remove images from items as they're tricky to test, and another test covers it
        $basketGetResponse['items'] = array_map(function($basketItem) {
            unset($basketItem['image']); return $basketItem;
        }, $basketGetResponse['items']);

        unset($basketGetResponse['available_shipping_methods']);

        $this->assertEquals([
            'basket_id' => 1,
            'subtotal' => '40.00',
            'subtotal_with_symbol' => '£40.00',
            'shipping' => '5.00',
            'shipping_with_symbol' => '£5.00',
            'discounts' => [],
            'discount_code' => null,
            'discount_total' => '0.00',
            'discount_total_with_symbol' => '£0.00',
            'total' => '45.00',
            'total_with_symbol' => '£45.00',
            'item_count' => 3,
            'items' => [
                ['item_id' => '11', 'product_id' => 1, 'name' => 'test', 'price' => '10.00', 'price_symbol' => '£10.00', 'qty' => 2],
                ['item_id' => '21', 'product_id' => 2, 'name' => 'test', 'price' => '15.00', 'price_symbol' => '£15.00', 'qty' => 1],
                ['item_id' => '31', 'product_id' => 3, 'name' => 'test', 'price' => '5.00', 'price_symbol' => '£5.00', 'qty' => 1]
            ]
        ], $basketGetResponse);
    }

    public function testSimpleProductsWithShippingAndDiscount()
    {
      //  $this->markTestIncomplete('Really difficult to mock collectTotals');
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getShippingInclTax', 'getShippingAmount', 'getShippingMethod', 'collectTotals', 'getDiscountAmount'])
            ->getMock();

        /* @var $quoteAddress Mage_Sales_Model_Quote_Address */
        $quoteAddress->method('getShippingInclTax')->willReturn(5);
        $quoteAddress->method('getShippingAmount')->willReturn(5);
        $quoteAddress->method('getShippingMethod')->willReturn('flatrate');
        $quoteAddress->method('getDiscountAmount')->willReturn(-4.50);

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods([
                'collectTotals',
                'getBillingAddress',
                'getShippingAddress',
                'getId',
                'getSubtotal',
                'getGrandTotal',
                'getItemsCount',
                'getDiscountBreakdown'
            ])
            ->getMock();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote->method('getBillingAddress')->willReturn($quoteAddress);
        $quote->method('getShippingAddress')->willReturn($quoteAddress);
        $quote->method('getId')->willReturn(1);
        $quote->method('getSubtotal')->willReturn(40.00);
        $quote->method('getGrandTotal')->willReturn(40.50);
        $quote->method('getItemsCount')->willReturn(3);
        $quote->method('getDiscountBreakdown')->willReturn([
            ['discount_label' => 'discount1', 'discount_amount' => 2.50, 'discount_code' => ''],
            ['discount_label' => 'discount2', 'discount_amount' => 2.00, 'discount_code' => 'test'],
        ]);

        $items = [];
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(1, 'test', 10, 'test-product-1'), 2);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(2, 'test', 15, 'test-product-2'), 1);
        $items[] = $this->createSalesQuoteItem($quote, $this->createProduct(3, 'test', 5, 'test-product-3'),1);

        $itemsCollection = new Varien_Data_Collection();
        foreach ($items as $item) {
            $itemsCollection->addItem($item);
        }

        $quote->setItemsCollection($items);

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $basketGetResponse = $basketGet->execute([], $request, $response);

        // Remove images from items as they're tricky to test, and another test covers it
        $basketGetResponse['items'] = array_map(function($basketItem) {
            unset($basketItem['image']); return $basketItem;
        }, $basketGetResponse['items']);

        unset($basketGetResponse['available_shipping_methods']);
        $this->assertEquals([
            'basket_id' => 1,
            'subtotal' => '40.00',
            'subtotal_with_symbol' => '£40.00',
            'shipping' => '5.00',
            'shipping_with_symbol' => '£5.00',
            'discount_total' => '4.50',
            'discount_total_with_symbol' => '£4.50',
            'discounts' => [
                [ 'discount_label' => 'discount1', 'discount_amount' => '2.50', 'discount_amount_with_symbol' => '£2.50', 'discount_code' => ''],
                [ 'discount_label' => 'discount2', 'discount_amount' => '2.00', 'discount_amount_with_symbol' => '£2.00', 'discount_code' => 'test']
            ],
            'discount_code' => null,
            'total' => '40.50',
            'total_with_symbol' => '£40.50',
            'item_count' => 3,
            'items' => [
                ['item_id' => '11', 'product_id' => 1, 'name' => 'test', 'price' => '10.00', 'price_symbol' => '£10.00', 'qty' => 2],
                ['item_id' => '21', 'product_id' => 2, 'name' => 'test', 'price' => '15.00', 'price_symbol' => '£15.00', 'qty' => 1],
                ['item_id' => '31', 'product_id' => 3, 'name' => 'test', 'price' => '5.00', 'price_symbol' => '£5.00', 'qty' => 1]
            ]
        ], $basketGetResponse);
    }

    public function testGetShippingMethods()
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Get::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getAllShippingRates'])
            ->getMock();

        $quoteAddress->method('getAllShippingRates')->willReturn([
            Mage::getModel('sales/quote_address_rate')->setData([
                'code' => 'shipping_rate_1',
                'carrier' => 'shipping_carrier_1',
                'carrier_title' => 'Shipping Carrier 1',
                'method' => 'shipping_method_1',
                'method_title' => 'Shipping Method 1',
                'method_description' => 'Test Description',
                'price' => '1.00'
            ]),
            Mage::getModel('sales/quote_address_rate')->setData([
                'code' => 'shipping_rate_2',
                'carrier' => 'shipping_carrier_2',
                'carrier_title' => 'Shipping Carrier 2',
                'method' => 'shipping_method_2',
                'method_title' => 'Shipping Method 2',
                'method_description' => 'Test Description',
                'price' => '2.00'
            ])
        ]);

        $quoteAddress->method('getId')->willReturn(1);

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getShippingAddress'])
            ->getMock();

        $quote->method('getShippingAddress')->willReturn($quoteAddress);

        $basketGet->method('getBasket')->willReturn($quote);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $shippingMethodPutResponse = $basketGet->execute(
            [],
            $request,
            $response
        );

        $this->assertEquals(
            [
                [
                    'code' => 'shipping_rate_1',
                    'method' => 'shipping_method_1',
                    'method_title' => 'Shipping Method 1',
                    'method_description' => 'Test Description',
                    'price' => '1.00',
                    'price_with_symbol' => '£1.00'
                ],
                [
                    'code' => 'shipping_rate_2',
                    'method' => 'shipping_method_2',
                    'method_title' => 'Shipping Method 2',
                    'method_description' => 'Test Description',
                    'price' => '2.00',
                    'price_with_symbol' => '£2.00'
                ],
            ],
            $shippingMethodPutResponse['available_shipping_methods']
        );
    }

    /**
     * @param $id
     * @param $name
     * @param $price
     * @param $sku
     * @param string $type
     * @return Mage_Catalog_Model_Product
     */
    private function createProduct($id, $name, $price, $sku, $type = 'simple')
    {
        $product = Mage::getModel('catalog/product');
        /** @var $product Mage_Catalog_Model_Product */
        $product->setId($id);
        $product->setAttributeSetId(1);
        $product->setPrice($price);
        $product->setFinalPrice($price);
        $product->setName($name);
        $product->setSku($sku);
        $product->setTypeId($type);

        return $product;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Catalog_Model_Product $product
     * @param $qty
     * @param array $options
     * @return Mage_Sales_Model_Quote_Item
     */
    public function createSalesQuoteItem(
        Mage_Sales_Model_Quote $quote,
        Mage_Catalog_Model_Product $product,
        $qty,
        $options = []
    ) {
        $salesQuoteItem = Mage::getModel('sales/quote_item');

        $salesQuoteItem->setId($product->getId() . '1');
        $salesQuoteItem->setProduct($product);
        $salesQuoteItem->setName($product->getName());
        $salesQuoteItem->setSku($product->getSku());
        $salesQuoteItem->setPrice($product->getPrice());
        $salesQuoteItem->setPriceInclTax($product->getFinalPrice());
        $salesQuoteItem->setQty($qty);
        $salesQuoteItem->setQuote($quote);

        // This is done in here as a product is _really_ configured only when added to the quote item
        if (count($options) > 0) {
            $typeInstanceMock = $this->getMockBuilder(Mage_Catalog_Model_Product_Type_Configurable::class)
                ->setMethods(['getSelectedAttributesInfo'])
                ->getMock();

            $outputOptions = [];
            foreach ($options as $optionKey => $optionValue) {
                $outputOptions[] = ['label' => $optionKey, 'value' => $optionValue];
            }

            $typeInstanceMock->method('getSelectedAttributesInfo')
                ->with($salesQuoteItem->getProduct())
                ->willReturn($outputOptions);

            $product->setTypeInstance($typeInstanceMock, true);
        }

        return $salesQuoteItem;
    }
}