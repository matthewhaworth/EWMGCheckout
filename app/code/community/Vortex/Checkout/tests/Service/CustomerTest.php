<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Service_CustomerTest extends TestCase
{
    public function testGetCustomer_customerEmailExists()
    {
        $customerSession = $this->getMockBuilder(Mage_Customer_Model_Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->setMethods(['create'])
            ->getMock();

        $customer = $this->getMockBuilder(Mage_Customer_Model_Customer::class)
            ->setMethods(['setWebsiteId', 'loadByEmail'])
            ->getMock();

        $customer->method('setWebsiteId')->willReturn($customer);
        $customer->method('loadByEmail')->willReturn($customer->setId(1));
        $customerFactory->method('create')->willReturn($customer);

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertEquals($customer, $service->getCustomer('test@example.com'));
    }

    public function testGetCustomer_customerIdExists()
    {
        $customerSession = $this->getMockBuilder(Mage_Customer_Model_Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->setMethods(['create'])
            ->getMock();

        $customer = $this->getMockBuilder(Mage_Customer_Model_Customer::class)
            ->setMethods(['load'])
            ->getMock();

        $customer->method('load')->willReturn($customer->setId(1));
        $customerFactory->method('create')->willReturn($customer);

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertEquals($customer, $service->getCustomer(1));
    }

    public function testGetCustomer_customerDoesNotExist()
    {
        $customerSession = $this->getMockBuilder(Mage_Customer_Model_Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->setMethods(['create'])
            ->getMock();

        $customer = $this->getMockBuilder(Mage_Customer_Model_Customer::class)
            ->setMethods(['load', 'getId'])
            ->getMock();

        $customer->method('getId')->willReturn(null);
        $customer->method('load')->willReturn($customer->setId(1));
        $customerFactory->method('create')->willReturn($customer);

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertFalse($service->getCustomer(1));
    }

    public function testAuthenticateCustomer_validCredentials()
    {
        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerSession = $this->getMockBuilder(Mage_Customer_Model_Session::class)
            ->setMethods(['login'])
            ->getMock();

        $customerSession->method('login')->willReturn(true);

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertTrue($service->authenticate('test@example.com', 'password'));
    }

    public function testAuthenticateCustomer_nonValidCredentials()
    {
        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->getMock();

        $customerSession = $this->getMockBuilder(Mage_Customer_Model_Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['login'])
            ->getMock();

        $customerSession->method('login')->willReturn(false);

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertFalse($service->authenticate('test@example.com', 'password'));
    }

    /**
     * @group integration
     */
    public function testAuthenticateCustomer_invalidCredentials_integration()
    {
        $customerFactory = $this->getMockBuilder(Vortex_Checkout_Factory_Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerSession = Mage::getSingleton('customer/session');

        $service = new Vortex_Checkout_Service_Customer($customerFactory, $customerSession);

        $this->assertFalse($service->authenticate('test@example.com', 'password'));
    }
}