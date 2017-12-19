<?php
class Vortex_Checkout_Api_Clickandcollect_Stores_Put implements Vortex_Api_EndpointInterface
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
        $storeId = $body['store_id'] ?: false;
        if (!$storeId) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('store_id is required'), 400);
        }

        $storeCollection = Mage::getModel('microsmultichannel_storelocator/lookup_service')->getStores(
            $this->getBasket()
        );

        /** @var MicrosMultiChannel_StoreLocator_Model_Store_Interface $store */
        $store = $storeCollection->getItemById($storeId);
        if ($store) {
            Mage::dispatchEvent(
                'microsmultichannel_store_locator_store_selected',
                array('store' => $store)
            );
        }

        $quote = $this->getBasket();
        if ($quote->getGrandTotal() == 0) {
            $this->getBasketAddressService()->setBillingAddressAsShippingAddress();
        }

        return $this->getBasketMapper()->map($this->getBasket());
    }

    /**
     * @return Vortex_Checkout_Service_Basket_Address
     */
    private function getBasketAddressService()
    {
        return new Vortex_Checkout_Service_Basket_Address(
            Mage::getSingleton('checkout/type_onepage')
        );
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket
     */
    private function getBasketMapper()
    {
        return new Vortex_Checkout_Mapper_Basket(
            $this->getBasketItemsMapper(),
            new Vortex_Checkout_Mapper_Basket_Address(),
            $this->getCoreHelper()
        );
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

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepageSingleton()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}