<?php
class Vortex_Checkout_Api_Customer_Session_Post implements Vortex_Api_EndpointInterface
{
    /**
     * @var Vortex_Checkout_Service_Customer
     */
    protected $customerService;

    /**
     * @var Vortex_Checkout_Mapper_Customer
     */
    protected $customerMapper;

    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        if (!array_key_exists('email', $body) || !array_key_exists('password', $body)) {
            throw new Vortex_Api_Exception_BadRequest('You must provide a customer email and password.', 403);
        }

        if ($this->getCustomerService()->authenticate($body['email'], $body['password'])) {
            $this->getBasket()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)->save();
            $customer = $this->getCustomerService()->getCustomer($body['email']);

            return $this->getCustomerMapper()->map($customer, $this->getCustomerSession()->isLoggedIn());
        } else {
            throw new Vortex_Api_Exception_BadRequest('Not authenticated.', 403);
        }
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getBasket()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * @return Vortex_Checkout_Service_Customer
     */
    protected function getCustomerService()
    {
        if (is_null($this->customerService)) {
            $this->customerService = new Vortex_Checkout_Service_Customer(
                new Vortex_Checkout_Factory_Customer(),
                new Vortex_Checkout_Factory_Customer_Address(),
                $this->getCustomerSession()
            );
        }

        return $this->customerService;
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return Vortex_Checkout_Mapper_Customer
     */
    protected function getCustomerMapper()
    {
        if (is_null($this->customerMapper)) {
            $this->customerMapper = new Vortex_Checkout_Mapper_Customer();
        }

        return $this->customerMapper;
    }
}