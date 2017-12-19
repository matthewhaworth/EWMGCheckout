<?php
class Vortex_Checkout_Api_Customer_Address_Put implements Vortex_Api_EndpointInterface
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
        if (!array_key_exists('customer_address_id', $body)) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Address must have an id'), 400);
        }

        if (!$this->getCustomerSession()->isLoggedIn()) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Customer must be logged in to change address'), 403);
        }

        $customerAddressId = $body['customer_address_id'];
        $customer = $this->getCustomerSession()->getCustomer();
        if (!$this->getCustomerService()->doesAddressBelongToCustomer($customerAddressId, $customer)) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Address does not belong to logged in customer'), 403);
        }

        try {
            $this->getCustomerService()->saveCustomerAddress($customer, $customerAddressId, $body);
        } catch (Exception $e) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Unable to save address'), 500);
        }

        return $this->getCustomerMapper()->map($customer, true);
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

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}