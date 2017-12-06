<?php
class Vortex_Checkout_Mapper_Order_Items
{
    /**
     * @var Mage_Core_Helper_Data
     */
    private $coreHelper;

    /**
     * @var Mage_Catalog_Helper_Image
     */
    private $imageHelper;

    /**
     * @var Mage_Catalog_Helper_Product_Configuration
     */
    private $configurationHelper;

    /**
     * Vortex_Checkout_Mapper_Order_Items constructor.
     * @param Mage_Core_Helper_Data $coreHelper
     * @param Mage_Catalog_Helper_Image $imageHelper
     * @param Mage_Catalog_Helper_Product_Configuration $configurationHelper
     */
    public function __construct(
        Mage_Core_Helper_Data $coreHelper,
        Mage_Catalog_Helper_Image $imageHelper,
        Mage_Catalog_Helper_Product_Configuration $configurationHelper
    ) {
        $this->coreHelper = $coreHelper;
        $this->imageHelper = $imageHelper;
        $this->configurationHelper = $configurationHelper;
    }


    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return string
     */
    protected function getProductImage(Mage_Sales_Model_Order_Item $orderItem)
    {
        return $this->imageHelper->init($orderItem->getProduct(), 'small_image')->resize(120, 160)->__toString();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param array $itemData
     * @return array
     */
    protected function addConfiguredOptions(Mage_Sales_Model_Order_Item $orderItem, array $itemData)
    {
        $existingConfiguredOptions = (array_key_exists('configured_options', $itemData)) ? $itemData['configured_options'] : [];

        $productOptions = $orderItem->getProductOptions();
        $itemData['configured_options'] = array_merge(
            $existingConfiguredOptions,
            $productOptions['attributes_info']
        );

        return $itemData;
    }

    /**
     * @param array $itemData
     * @param array $customOptions
     * @return array
     */
    protected function addCustomOptions(array $itemData, array $customOptions)
    {
        $existingConfiguredOptions = $itemData['configured_options'] ?: [];
        $itemData['configured_options'] = array_merge($existingConfiguredOptions, $customOptions);
        return $itemData;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function map(Mage_Sales_Model_Order $order)
    {
        $response = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var $orderItem Mage_Sales_Model_Order_Item */
            $itemData = [];
            $itemData['item_id'] = $orderItem->getId();
            $itemData['product_id'] = $orderItem->getProduct()->getId();
            $itemData['name'] = $orderItem->getName();
            $itemData['price'] = number_format($orderItem->getPriceInclTax(), 2);
            $itemData['price_currency'] = $this->coreHelper->formatCurrency($itemData['price'], false);
            $itemData['original_price'] = $orderItem->getOriginalPrice();
            $itemData['original_price_currency'] = $this->coreHelper->formatCurrency($itemData['original_price'], false);
            $itemData['subtotal_price'] = $this->coreHelper->formatCurrency($orderItem->getRowTotalInclTax(), false);
            $itemData['qty'] = (int) $orderItem->getQtyOrdered();
            $itemData['image'] = $this->getProductImage($orderItem);
            $itemData['product_url'] = $orderItem->getProduct()->getProductUrl();

            if ($orderItem->getProduct()->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                $itemData = $this->addConfiguredOptions($orderItem, $itemData);
            }

//            $customOptions = $this->configurationHelper->getCustomOptions($orderItem);
//            if ($customOptions && count($customOptions) > 0) {
//                $itemData = $this->addCustomOptions($itemData, $customOptions);
//            }

            $response[] = $itemData;
        }

        return $response;
    }
}