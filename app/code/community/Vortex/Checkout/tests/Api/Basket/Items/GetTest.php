<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Basket_Items_GetTest extends TestCase
{
    public function testEmptyBasket()
    {
        $this->runner([]);
    }

    public function testOneSimpleProduct()
    {
        $simpleProduct = [
            [
                'product' => $this->createProduct(1, 'Test Product', 9.99, 'test-product'),
                'qty' => 1
            ]
        ];

        $this->runner($simpleProduct);
    }

    public function testMultipleSimpleProducts()
    {
        $simpleProducts = [
            [
                'product' => $this->createProduct(1, 'Test Product 1', 1.99, 'test-product-1'),
                'qty' => 1
            ],
            [
                'product' => $this->createProduct(2, 'Test Product 2', 2.9902, 'test-product-2'),
                'qty' => 2
            ],
            [
                'product' => $this->createProduct(3, 'Test Product 3', 3.991, 'test-product-3'),
                'qty' => 3
            ]
        ];

        $this->runner($simpleProducts);
    }

    public function testConfigurableProducts()
    {
        $configurableProducts = [
            [
                'product' => $this->createProduct(
                    1,
                    'Test Product 1',
                    1.99,
                    'test-product-1',
                    'configurable'
                ),
                'qty' => 1,
                'options' => ['size' => 'large']
            ]
        ];

        $this->runner($configurableProducts);
    }

    public function testConfigurableProductsWithCustomOptions()
    {
        $configurableProducts = [
            [
                'product' => $this->createProduct(
                    1,
                    'Test Product 1',
                    1.99,
                    'test-product-1',
                    'configurable'
                ),
                'qty' => 1,
                'options' => ['size' => 'large'],
                'custom_options' => ['my_custom_option' => 'my_value']
            ]
        ];

        $this->runner($configurableProducts);
    }

    public function testMultipleConfigurableProducts()
    {
        $configurableProducts = [
            [
                'product' => $this->createProduct(
                    1,
                    'Test Product 1',
                    1.99,
                    'test-product-1',
                    'configurable'
                ),
                'qty' => 1,
                'options' => ['size' => 'small']
            ],
            [
                'product' => $this->createProduct(
                    2,
                    'Test Product 2',
                    2.99,
                    'test-product-2',
                    'configurable'
                ),
                'qty' => 2,
                'options' => ['color' => 'blue']
            ],
            [
                'product' => $this->createProduct(
                    3,
                    'Test Product 3',
                    3.99,
                    'test-product-3',
                    'configurable'
                ),
                'qty' => 3,
                'options' => ['size' => 'small', 'color' => 'red'],
                'custom_options' => ['my_option_1' => 'my_value_1', 'my_option_2' => 'my_value_2']
            ]
        ];

        $this->runner($configurableProducts);
    }

    /**
     * @sk
     * @param $productData
     */
    private function runner($productData)
    {
        $basketGet = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Items_Get::class)
            ->setMethods(['getBasket', 'getConfigurationHelper'])
            ->getMock();

        $mockConfigurationHelper = $this->getMockBuilder(Mage_Catalog_Helper_Product_Configuration::class)
            ->setMethods(['getCustomOptions'])
            ->getMock();


        $productCollection = new Varien_Data_Collection();
        $i = 0;
        foreach ($productData as $productDatum) {
            $salesItem = $this->createSalesQuoteItem(
                $productDatum['product'],
                $productDatum['qty'],
                $productDatum['options'] ?: null,
                $productDatum['custom_options'] ?: null
            );

            $productCollection->addItem($salesItem);

            $mockConfigurationHelper->expects($this->at($i))
                ->method('getCustomOptions')
                ->will($this->returnCallback(function($salesItem) use ($productDatum) {
                    $customOptions = $productDatum['custom_options'] ?: [];
                    $mockCustomOptionsReturn = [];
                    if (count($customOptions) > 0) {
                        foreach ($customOptions as $customOptionKey => $customOptionValue) {
                            $mockCustomOptionsReturn[] = [
                                'label' => $customOptionKey,
                                'value' => $customOptionValue
                            ];
                        }
                    }

                    return $mockCustomOptionsReturn;
                }));
            $i++;
        }

        $basketGet->expects($this->once())
            ->method('getBasket')
            ->willReturn($this->createSalesQuoteStub($productCollection));

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketGet->method('getConfigurationHelper')
            ->willReturn($mockConfigurationHelper);

        $basketGetResponse = $basketGet->execute([], $request, $response);

        $this->assertEquals(count($productData), count($basketGetResponse));

        $i = 0;
        foreach ($productData as $productDatum) {
            $product = $productDatum['product'];
            $resultImage = $basketGetResponse[$i]['image'];
            unset($basketGetResponse[$i]['image']);

            $expectedResponse = [
                'item_id' => $product->getId() . '1',
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'price' => number_format($product->getPrice(), 2),
                'price_symbol' => '£' . number_format($product->getPrice(), 2),
                'qty' => $productDatum['qty']
            ];

            if ('configurable' === $product->getTypeId()) {
                $configuredOptions = [];
                foreach ($productDatum['options'] as $optionKey => $optionValue) {
                    $configuredOptions[] = ['label' => $optionKey, 'value' => $optionValue];
                }

                $expectedResponse['configured_options'] = $configuredOptions;
            }

            if (array_key_exists('custom_options', $productDatum)) {
                $customOptions = [];
                foreach ($productDatum['custom_options'] as $customOptionKey => $customOptionValue) {
                    $customOptions[] = ['label' => $customOptionKey, 'value' => $customOptionValue];
                }

                $expectedResponse['configured_options'] = array_merge(
                    $expectedResponse['configured_options'] ?: [],
                    $customOptions
                );
            }

            $this->assertEquals($expectedResponse, $basketGetResponse[$i]);

            $this->assertLessThanOrEqual(2, strlen(explode('.', $basketGetResponse[$i]['price'])[1]));
            $this->assertContains('cache', $resultImage, 'Image was not dynamically created');
            $i++;
        }
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
        $stub = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $stub->method('getAllVisibleItems')
            ->willReturn($salesItems);

        return $stub;
    }
}