<?php
class Vortex_Checkout_Api_Order_Post implements Vortex_Api_EndpointInterface
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
        $quote = $this->getOnepageSession()->getQuote();
        try {
            if (array_key_exists('newsletter', $body) && $body['newsletter']) {
                $email = $quote->getCustomerEmail();
                $newsletterSubscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                if (!$newsletterSubscriber || !$newsletterSubscriber->getId()) {
                    Mage::getModel('newsletter/subscriber')->subscribe($email);
                }
            }

            $this->getOnepageSingleton()->savePayment($body);
            $this->getOnepageSingleton()->saveOrder();
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Vortex_Api_Exception_BadRequest($e->getMessage(), 500);
        }

        $orderId = $this->getOnepageSession()->getLastOrderId();

        $quote->setIsActive(false)->save();
        $this->getOnepageSession()->clear();

        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            /** @var $order Mage_Sales_Model_Order */
            $order->addStatusHistoryComment('This order was placed using EWMG checkout.')->save();

            Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($orderId)));
            return [
                'increment_id' => $order->getIncrementId(),
                'order' => $this->getOrderMapper()->map($order)
            ];
        } else {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Could not place order'), 500);
        }
    }

    public function getOrderMapper()
    {
        return new Vortex_Checkout_Mapper_Order(
            $this->getBasketItemsMapper(),
            new Vortex_Checkout_Mapper_Order_Address(),
            $this->getCoreHelper()
        );
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepageSingleton()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getOnepageSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Vortex_Checkout_Mapper_Order_Items
     */
    protected function getBasketItemsMapper()
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

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}