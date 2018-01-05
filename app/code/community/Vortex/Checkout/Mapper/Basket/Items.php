<?php
class Vortex_Checkout_Mapper_Basket_Items
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
     * Vortex_Checkout_Mapper_Basket_Items constructor.
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
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return string
     */
    protected function getProductImage(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        return $this->imageHelper->init($quoteItem->getProduct(), 'small_image')->resize(120, 160)->__toString();
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param array $itemData
     * @return array
     */
    protected function addConfiguredOptions(Mage_Sales_Model_Quote_Item $quoteItem, array $itemData)
    {
        $existingConfiguredOptions = (array_key_exists('configured_options', $itemData)) ? $itemData['configured_options'] : [];

        $itemData['configured_options'] = array_merge(
            $existingConfiguredOptions,
            $quoteItem->getProduct()
                ->getTypeInstance(true)
                ->getSelectedAttributesInfo($quoteItem->getProduct())
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
     * @param Vortex_Checkout_Model_CatalogInventory_Stock_Item $stockItem
     * @param $requestedQty
     * @return array|bool
     */
    protected function getQtyStatus(Mage_CatalogInventory_Model_Stock_Item $stockItem, $requestedQty)
    {
        $result = $stockItem->checkQuoteItemQty($requestedQty, $requestedQty);

        if ($result->getHasError()) {
            $return = array();
            $error = null;

            switch ($result->getErrorCode()) {
                case 'qty_increments':
                    $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_INCREMENTS;
                    $return['qty_increments'] = $stockItem->getQtyIncrements();
                    $error = Mage::helper('vortex_checkout')->__('This product is available in increments of %s only.', $stockItem->getQtyIncrements());
                    break;
                case 'qty_min':
                    $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART;
                    $return['qty_min_allowed'] = $stockItem->getMinSaleQty();
                    $error = Mage::helper('vortex_checkout')->__('The minimum quantity allowed is %s.', $stockItem->getMinSaleQty());
                    break;
                case 'qty_max':
                    $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART;
                    $return['qty_max_allowed'] = $stockItem->getMaxSaleQty();
                    $error = Mage::helper('vortex_checkout')->__('The requested quantity is not available.');
                    break;
                default:
                    if (!$stockItem->getIsInStock()) {
                        $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK;
                        $error = Mage::helper('vortex_checkout')->__('Out of stock');
                        break;
                    } else if ($result->getQuoteMessageIndex() == 'qty') {
                        $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED;
                        $return['qty_max_allowed'] = $stockItem->getStockQty();
                        $error = Mage::helper('vortex_checkout')->__('The requested quantity is not available.');
                        break;
                    }
                    $status = Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED;
                    $return['qty_max_allowed'] = $stockItem->getStockQty();
            }

            $return['error_code'] = $result->getErrorCode();
            $return['status'] = $status;
            $return['error'] = $error ? $error : $result->getMessage();

            return $return;
        }
        return true;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function map(Mage_Sales_Model_Quote $quote)
    {
        $response = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $itemData = [];
            $itemData['item_id'] = $quoteItem->getId();
            $itemData['product_id'] = $quoteItem->getProduct()->getId();
            $itemData['product_url'] = $quoteItem->getProduct()->getProductUrl();
            $itemData['name'] = $quoteItem->getName();
            $itemData['price'] = number_format($quoteItem->getPriceInclTax(), 2);
            $itemData['price_currency'] = $this->coreHelper->formatCurrency($itemData['price'], false);
            $itemData['original_price'] = $quoteItem->getOriginalPrice();
            $itemData['original_price_currency'] = $this->coreHelper->formatCurrency($itemData['original_price'], false);
            $itemData['subtotal_price'] = $this->coreHelper->formatCurrency($quoteItem->getRowTotalInclTax(), false);
            $itemData['qty'] = (int) $quoteItem->getQty();
            $itemData['image'] = $this->getProductImage($quoteItem);
            $itemData['sku'] = $quoteItem->getSku();

            /**
             * @var Mage_CatalogInventory_Model_Stock_Item $stockItem
             * @var Mage_CatalogInventory_Model_Stock_Item $parentStockItem
             */
            if ($quoteItem->getProduct()->getTypeId() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
                $parentStockItem = $quoteItem->getProduct()->getStockItem();
                $stockItem = $quoteItem->getOptionByCode('simple_product')->getProduct()->getStockItem();

                $stockItem->setParentItem($parentStockItem);
                $stockItem->setIsChildItem(true);

                if (!$stockItem->getIsInStock() || ($parentStockItem && !$parentStockItem->getIsInStock())) {
                    $qtyStatus = [
                        'error_code' => 'out_of_stock',
                        'status' => Enterprise_Checkout_Helper_Data::ADD_ITEM_STATUS_FAILED_OUT_OF_STOCK,
                        'error' => Mage::helper('vortex_checkout')->__('Out of stock')
                    ];
                } else{
                    $qtyStatus = $this->getQtyStatus($stockItem, $quoteItem->getQty());
                }

                $itemData = $this->addConfiguredOptions($quoteItem, $itemData);
            } else{
                $stockItem = $quoteItem->getProduct()->getStockItem();
                $qtyStatus = $this->getQtyStatus($stockItem, $quoteItem->getQty());
            }

            $itemData['qty_status'] = $qtyStatus;

            $customOptions = $this->configurationHelper->getCustomOptions($quoteItem);
            if ($customOptions && count($customOptions) > 0) {
                $itemData = $this->addCustomOptions($itemData, $customOptions);
            }

            $response[] = $itemData;
        }

        return $response;
    }
}