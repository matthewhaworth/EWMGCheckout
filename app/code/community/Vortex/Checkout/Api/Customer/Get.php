<?php
class Vortex_Checkout_Api_Customer_Get implements Vortex_Api_EndpointInterface
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
        if (!$this->getCustomerSession()->isLoggedIn() && !$request->has('email')) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('You must provide a customer email.'), 403);
        }

        // Assume guest unless changed later by logging in
        $this->getBasket()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST)->save();

        $customerEmail = $request->getParam('email', false);
        if ($customerEmail) {
            $customer = $this->getCustomerService()->getCustomer($customerEmail);
        } else {
            // If the customer is logged in, just take that email
            $customer = $this->getCustomerSession()->getCustomer();
        }

        if ($customer && $customer->getId() > 0) {
            /**
             * Set email against basket. Technically, a violation of the responsibility of this API call.
             * However it's a necessary evil.
             */
            $this->getBasket()->setCustomerEmail($request->getParam('email'))->save();

            // Set customer's default addresses on quote if they have no already been set
            $this->getAddressService()->setCustomerAddressIfAddressIsNotSet($this->getBasket(), $customer);

            return $this->getCustomerMapper()->map($customer, $this->getCustomerSession()->isLoggedIn());
        } else {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Customer does not exist'), 404);
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

    /**
     * @return Vortex_Checkout_Service_Basket_Address
     */
    protected function getAddressService()
    {
        return new Vortex_Checkout_Service_Basket_Address(
            Mage::getSingleton('checkout/type_onepage')
        );
    }

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}