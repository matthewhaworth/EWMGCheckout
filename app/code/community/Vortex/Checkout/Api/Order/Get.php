<?php
class Vortex_Checkout_Api_Order_Get implements Vortex_Api_EndpointInterface
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
        $incrementId = $request->getParam('increment_id', false);
        if (!$incrementId) {
            throw new Vortex_Api_Exception_BadRequest('Increment id required.', 400);
        }

        $lastOrder = $this->getCheckoutSession()->getLastRealOrder();
        if (!$lastOrder || !$lastOrder->getId() || $incrementId !== $this->getCheckoutSession()->getLastRealOrder()->getIncrementId()) {
            throw new Vortex_Api_Exception_BadRequest('You are not authorised to view this order.', 400);
        }

        $orderMapper = new Vortex_Checkout_Mapper_Order(
            $this->getOrderItemsMapper(),
            new Vortex_Checkout_Mapper_Order_Address(),
            $this->getCoreHelper()
        );

        return $orderMapper->map($lastOrder);
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Vortex_Checkout_Mapper_Order_Items
     */
    protected function getOrderItemsMapper()
    {
        return new Vortex_Checkout_Mapper_Order_Items(
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