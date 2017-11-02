<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Customer_GetTest extends TestCase
{
    public function testGetCustomerThatExists()
    {
        $customerGet = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Get::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMock();

        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);
        $customer->setEmail('test@example.com');
        $customer->setFirstname('matthew');

        $customerServiceMock->method('getCustomer')->willReturn($customer);
        $customerGet->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $request->setParam('email', 'test@example.com');
        $response = new Zend_Controller_Response_Http();
        $customerGetResponse = $customerGet->execute([], $request, $response);

        $this->assertEquals(['name' => 'matthew', 'email' => 'test@example.com'], $customerGetResponse);
    }

    /**
     * @expectedException Vortex_Api_Exception_BadRequest
     * @expectedExceptionCode 404
     */
    public function testGetCustomerThatDoesNotExist()
    {
        $customerGet = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Get::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMock();

        $customer = Mage::getModel('customer/customer');
        $customer->setId(null);
        $customer->setEmail(null);

        $customerServiceMock->method('getCustomer')->willReturn($customer);
        $customerGet->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $request->setParam('email', 'test@example.com');
        $response = new Zend_Controller_Response_Http();
        $customerGet->execute([], $request, $response);
    }

    /**
     * @expectedException Vortex_Api_Exception_BadRequest
     * @expectedExceptionCode 400
     */
    public function testInvalidRequestNoEmail()
    {
        $customerGet = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Get::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMock();

        $customer = Mage::getModel('customer/customer');

        $customerServiceMock->method('getCustomer')->willReturn($customer);
        $customerGet->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerGet->execute([], $request, $response);
    }
}