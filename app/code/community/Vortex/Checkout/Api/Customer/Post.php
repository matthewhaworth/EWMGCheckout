<?php
class Vortex_Checkout_Api_Customer_Post implements Vortex_Api_EndpointInterface
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
        if (!array_key_exists('password', $body)) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Password is required.'), 400);
        }

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($this->getBasket()->getCustomerEmail());
        /* @var $customer Mage_Customer_Model_Customer */
        if ($customer && $customer->getId() > 0) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('A customer with this email already exists.'), 400);
        }

        try {
            $customer = $this->getCustomerService()->createCustomerFromQuote(
                $this->getBasket(),
                $this->getCheckoutSession()->getLastRealOrder(),
                $body['password']
            );

            $this->getCustomerService()->authenticate($customer->getEmail(), $body['password']);
        } catch (Exception $e) {
            throw new Vortex_Api_Exception_BadRequest($e->getMessage(), 400);
        }

        return $this->getCustomerMapper()->map($customer, true);
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getBasket()
    {
        $quoteId = $this->getCheckoutSession()->getLastRealOrder()->getQuoteId();
        return Mage::getModel('sales/quote')->load($quoteId);
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Vortex_Checkout_Service_Customer
     */
    protected function getCustomerService()
    {
        return new Vortex_Checkout_Service_Customer(
            new Vortex_Checkout_Factory_Customer(),
            new Vortex_Checkout_Factory_Customer_Address(),
            $this->getCustomerSession()
        );
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function getCustomerMapper()
    {
        return new Vortex_Checkout_Mapper_Customer();
    }

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}