<?php
class Vortex_Checkout_Api_Basket_Shippingmethod_Put implements Vortex_Api_EndpointInterface
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
        if (!array_key_exists('shipping_method', $body)) {
            throw new Vortex_Api_Exception_BadRequest('shipping_method is required', 400);
        }

        try {
            // Collect totals to determine available shipping methods
            $this->getBasket()->collectTotals()->save();

            $saveShippingMethodResponse = $this->getOnepageSingleton()->saveShippingMethod($body['shipping_method']);

            // Throw event that Magento would throw
            Mage::dispatchEvent(
                'checkout_controller_onepage_save_shipping_method',
                ['request' => $request, 'quote'   => $this->getBasket()]
            );

            // Fix to force Magento to load in the new shipping method amount to the shipping address
            $this->getBasket()->getShippingAddress()->setCollectShippingRates(true)->collectTotals()->save();
        } catch (Exception $e) {
            throw new Vortex_Api_Exception_BadRequest($e->getMessage(), 500);
        }

        if (array_key_exists('error', $saveShippingMethodResponse) && $saveShippingMethodResponse['error'] === -1) {
            throw new Vortex_Api_Exception_BadRequest($saveShippingMethodResponse['message'], 400);
        }

        return $this->getBasketMapper()->map($this->getBasket());
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepageSingleton()
    {
        return Mage::getSingleton('checkout/type_onepage');
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
            $this->getCoreHelper()
        );
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
        return $this->getOnepageSingleton()->getQuote();
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