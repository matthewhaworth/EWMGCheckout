<?php
class Vortex_Checkout_Api_Basket_Items_Get implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        $basketItemMapper = new Vortex_Checkout_Mapper_Basket_Items(
            $this->getCoreHelper(),
            $this->getProductImageHelper(),
            $this->getConfigurationHelper()
        );

        return $basketItemMapper->map($this->getBasket());
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getBasket()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * @return Mage_Catalog_Helper_Image
     */
    protected function getProductImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('core');
    }

    /**
     * @return Mage_Catalog_Helper_Product_Configuration
     */
    protected function getConfigurationHelper()
    {
        return Mage::helper('catalog/product_configuration');
    }
}