<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Basket_PutTest extends TestCase
{
    protected $testProducts = [
        ['id' => 1, 'name' => 'Product 1', 2.99, 'product-1', 'qty' => 3],
        ['id' => 2, 'name' => 'Product 2', 3.50, 'product-2', 'qty' => 1],
        ['id' => 3, 'name' => 'Configurable Product 3', 56.99, 'configurable-product-3', 'type' => 'configurable', 'qty' => 5]
    ];

    public function testRemoveFromCart()
    {
        $basketPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $salesQuoteStub = $this->createSalesQuoteStub($this->createExampleCart($this->testProducts));
        $basketPut->method('getBasket')->willReturn($salesQuoteStub);

        $requestBody = [ 'items' => [[ 'item_id' => 11, 'qty' => 0 ]] ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketPutResponse = $basketPut->execute($requestBody, $request, $response)['items'];

        $itemUnderTest = array_filter($basketPutResponse, function($i) { return $i['item_id'] == 11; });
        $otherItems = array_filter($basketPutResponse, function($i) { return $i['item_id'] != 11; });

        $this->assertEquals(0, count($itemUnderTest));
        $this->assertEquals(2, count($otherItems));
    }

    public function testIncreaseQuantityInCart()
    {
        $basketPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $salesQuoteStub = $this->createSalesQuoteStub($this->createExampleCart($this->testProducts));
        $basketPut->method('getBasket')->willReturn($salesQuoteStub);

        $requestBody = [ 'items' => [[ 'item_id' => 11, 'qty' => 6 ]] ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketPutResponse = $basketPut->execute($requestBody, $request, $response)['items'];

        $itemUnderTest = array_filter($basketPutResponse, function($i) { return $i['item_id'] == 11; });

        $this->assertEquals(1, count($itemUnderTest));
        $this->assertEquals(6, $itemUnderTest[0]['qty']);
    }

    public function testDecreaseQuantityInCart()
    {
        $basketPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $salesQuoteStub = $this->createSalesQuoteStub($this->createExampleCart($this->testProducts));
        $basketPut->method('getBasket')->willReturn($salesQuoteStub);

        $requestBody = [ 'items' => [[ 'item_id' => 31, 'qty' => 1 ]]];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketPutResponse = $basketPut->execute($requestBody, $request, $response)['items'];

        $itemUnderTest = array_filter($basketPutResponse, function($i) { return $i['item_id'] == 31; });

        $this->assertEquals(1, count($itemUnderTest));
        $this->assertEquals(1, $itemUnderTest[2]['qty']);
    }

    public function testAddDiscount()
    {
        $basketPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $salesQuoteStub = $this->createSalesQuoteStub($this->createExampleCart($this->testProducts));
        $salesQuoteStub->setCouponCode('MY_DISCOUNT_CODE');

        $basketPut->method('getBasket')->willReturn($salesQuoteStub);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $body = ['discount' => 'MY_DISCOUNT_CODE'];
        $response = $basketPut->execute($body, $request, $response);

        $this->assertEquals('MY_DISCOUNT_CODE', $response['discount_code']);
    }

    public function testRemoveDiscount()
    {
        $basketPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $salesQuoteStub = $this->createSalesQuoteStub($this->createExampleCart($this->testProducts));
        $salesQuoteStub->setCouponCode('MY_DISCOUNT_CODE');

        $basketPut->method('getBasket')->willReturn($salesQuoteStub);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $body = ['remove_discount' => true];
        $response = $basketPut->execute($body, $request, $response);

        $this->assertEquals('', $response['discount_code']);
    }

    /**
     * @param $products
     * @return Varien_Data_Collection
     */
    private function createExampleCart($products)
    {
        $salesItems = array_map(function($p) {
            $product = $this->createProduct($p['id'], $p['name'], $p['price'], $p['sku'], $p['type']);
            return $this->createSalesQuoteItem($product, $p['qty']);
        }, $products);

        $salesItemCollection = new Varien_Data_Collection();
        foreach ($salesItems as $salesItem) {
            $salesItemCollection->addItem($salesItem);
        }

        return $salesItemCollection;
    }

    /**
     * @todo this is duplicated, re-factor to helper
     *
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
     * @todo this is duplicated re-factor
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $qty
     * @param array $options
     * @return Mage_Sales_Model_Quote_Item
     */
    public function createSalesQuoteItem(Mage_Catalog_Model_Product $product, $qty, $options = [])
    {
        $salesQuoteItem = Mage::getModel('sales/quote_item');

        $salesQuoteItem->setId($product->getId() . '1');
        $salesQuoteItem->setProduct($product);
        $salesQuoteItem->setName($product->getName());
        $salesQuoteItem->setSku($product->getSku());
        $salesQuoteItem->setPrice($product->getPrice());
        $salesQuoteItem->setPriceInclTax($product->getFinalPrice());
        $salesQuoteItem->setQty($qty);

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

    /**
     * @param Varien_Data_Collection $salesItems
     * @return Mage_Sales_Model_Quote
     */
    private function createSalesQuoteStub(Varien_Data_Collection $salesItems)
    {
        $stub = Mage::getModel('sales/quote');

        $stub->setItemsCollection($salesItems);

        return $stub;
    }
}