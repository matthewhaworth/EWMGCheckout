<?php
class Vortex_Checkout_Api_Basket_Address_Put implements Vortex_Api_EndpointInterface
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
        // This is required for tests to retain the concept of what the quote is without using sessions
        $this->getOnepageSingleton()->setQuote($this->getBasket());

        $this->getBasketAddressService()->saveAddress($body);

        return $this->getBasketMapper()->map($this->getBasket());
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket
     */
    protected function getBasketMapper()
    {
        return new Vortex_Checkout_Mapper_Basket(
            new Vortex_Checkout_Mapper_Basket_Items(
                $this->getCoreHelper(),
                $this->getProductImageHelper(),
                $this->getConfigurationHelper()
            ),
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
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepageSingleton()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Mage_Checkout_Model_Cart
     */
    protected function getCart()
    {
        return Mage::getSingleton('checkout/cart');
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

    /**
     * @return Vortex_Checkout_Service_Basket_Address
     */
    protected function getBasketAddressService()
    {
        return new Vortex_Checkout_Service_Basket_Address($this->getOnepageSingleton());
    }
}