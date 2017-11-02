<?php
class Vortex_Checkout_Api_Basket_Get implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        $this->getBasket()->collectTotals();
        $basketMapper = new Vortex_Checkout_Mapper_Basket(
            $this->getBasketItemsMapper(),
            new Vortex_Checkout_Mapper_Basket_Address(),
            $this->getCoreHelper()
        );

        return $basketMapper->map($this->getBasket());
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getBasket()
    {
       return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket_Items
     */
    protected function getBasketItemsMapper()
    {
        return new Vortex_Checkout_Mapper_Basket_Items(
            $this->getCoreHelper(),
            $this->getImageHelper(),
            $this->getConfigurationHelper()
        );
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('core');
    }

    /**
     * @return Mage_Catalog_Helper_Image
     */
    protected function getImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    /**
     * @return Mage_Catalog_Helper_Product_Configuration
     */
    protected function getConfigurationHelper()
    {
        return Mage::helper('catalog/product_configuration');
    }
}