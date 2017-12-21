<?php
class Vortex_Checkout_Api_Clickandcollect_Clear_Put implements Vortex_Api_EndpointInterface
{

    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        $this->getBasket()->getShippingAddress()->setShippingMethod(null)->save();
        Mage::dispatchEvent('microsmultichannel_store_locator_clear_store');

        /**
         * This is far from ideal, but other code in the codebase modifies the database without updated the object in
         * memory.
         */
        $newBasket = Mage::getModel('sales/quote')->load($this->getBasket()->getId());

        return $this->getBasketMapper()->map($newBasket);
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket
     */
    private function getBasketMapper()
    {
        return new Vortex_Checkout_Mapper_Basket(
            $this->getBasketItemsMapper(),
            new Vortex_Checkout_Mapper_Basket_Address(),
            $this->getCoreHelper(),
            $this->getCheckoutSession()
        );
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getBasket()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
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