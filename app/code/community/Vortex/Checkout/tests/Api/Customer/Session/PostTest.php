<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Customer_Session_PostTest extends TestCase
{
    public function testCustomerSessionPost_validCredentials()
    {
        $customerPost = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Session_Post::class)
            ->setMethods(['getCustomerService', 'getCustomerSession'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer', 'authenticate'])
            ->getMock();

        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);
        $customer->setEmail('test@example.com');
        $customer->setFirstname('matthew');

        $customerServiceMock->method('getCustomer')->willReturn($customer);
        $customerServiceMock->method('authenticate')->willReturn(true);
        $customerPost->method('getCustomerService')->willReturn($customerServiceMock);
        $customerPost->method('getCustomerSession')->willReturn(new class extends Mage_Customer_Model_Session {
            public function isLoggedIn() { return true; }
        });

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerGetResponse = $customerPost->execute(
            ['email' => 'test@example.com', 'password' => 'password'],
            $request,
            $response
        );

        $this->assertEquals(
            [ 'id' => 1, 'name' => 'matthew', 'email' => 'test@example.com', 'addresses' => []],
            $customerGetResponse
        );
    }

    /**
     * @expectedException Vortex_Api_Exception_BadRequest
     * @expectedExceptionCode 403
     */
    public function testCustomerSessionPost_invalidCredentials()
    {
        $customerPost = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Session_Post::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['authenticate'])
            ->getMock();

        $customerServiceMock->method('authenticate')->willReturn(false);
        $customerPost->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerPost->execute(
            ['name'=> 'matthew', 'email' => 'test@example.com', 'password' => 'password'],
            $request,
            $response
        );
    }

    /**
     * @expectedException Vortex_Api_Exception_BadRequest
     * @expectedExceptionCode 403
     */
    public function testCustomerSessionPost_invalidRequest_noPassword()
    {
        $customerPost = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Session_Post::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['authenticate'])
            ->getMock();

        $customerServiceMock->method('authenticate')->willReturn(false);
        $customerPost->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerPost->execute(
            ['name' => 'matthew', 'email' => 'test@example.com'],
            $request,
            $response
        );
    }

    /**
     * @expectedException Vortex_Api_Exception_BadRequest
     * @expectedExceptionCode 403
     */
    public function testCustomerSessionPost_invalidRequest_noEmail()
    {
        $customerPost = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Session_Post::class)
            ->setMethods(['getCustomerService'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['authenticate'])
            ->getMock();

        $customerServiceMock->method('authenticate')->willReturn(false);
        $customerPost->method('getCustomerService')->willReturn($customerServiceMock);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerPost->execute(
            ['name' => 'matthew', 'email' => 'test@example.com'],
            $request,
            $response
        );
    }

    public function testGetLoggedInCustomerAddresses()
    {
        $customerPost = $this->getMockBuilder(Vortex_Checkout_Api_Customer_Session_Post::class)
            ->setMethods(['getCustomerService', 'getCustomerSession'])
            ->getMock();

        $customerServiceMock = $this->getMockBuilder(Vortex_Checkout_Service_Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer', 'authenticate'])
            ->getMock();

        $customer = $this->getMockBuilder(Mage_Customer_Model_Customer::class)
            ->setMethods(['getId', 'getEmail', 'getFirstname', 'getMiddlename', 'getLastname', 'getAddressesCollection'])
            ->getMock();

        $customer->method('getId')->willReturn(1);
        $customer->method('getEmail')->willReturn('test@example.com');
        $customer->method('getFirstname')->willReturn('test1');
        $customer->method('getMiddlename')->willReturn('test2');
        $customer->method('getLastname')->willReturn('test3');

        $customer->method('getAddressesCollection')->willReturn($this->getMockAddressCollection($customer));

        $customerServiceMock->method('getCustomer')->willReturn($customer);
        $customerServiceMock->method('authenticate')->willReturn(true);
        $customerPost->method('getCustomerService')->willReturn($customerServiceMock);
        $customerPost->method('getCustomerSession')->willReturn(new class extends Mage_Customer_Model_Session {
            public function isLoggedIn() { return true; }
        });

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $customerGetResponse = $customerPost->execute(
            ['email' => 'test@example.com', 'password' => 'password'],
            $request,
            $response
        );

        $this->assertEquals(
            [
                [
                    'customer_address_id' => 1,
                    'line1' => 'Street Line 1',
                    'line2' => 'Street Line 2',
                    'postcode' => 'M1 1FF',
                    'city' => 'Test City',
                    'phone' => '01234567890',
                    'county' => 'Test Region',
                    'country' => 'GB',
                ],
                [
                    'customer_address_id' => 2,
                    'line1' => '1 Street',
                    'line2' => '',
                    'postcode' => 'M2 1FF',
                    'city' => 'Testville',
                    'phone' => '01234567899',
                    'county' => 'Test Region 2',
                    'country' => 'GB',
                ]
            ],
            $customerGetResponse['addresses']
        );
    }

    /**
     * @param $customer
     * @return Varien_Data_Collection
     */
    private function getMockAddressCollection($customer)
    {
        $addressCollection = new Varien_Data_Collection();
        $customerAddress = Mage::getModel('customer/address');
        /* @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress->setId(1)
            ->setFirstname($customer->getFirstname())
            ->setMiddleName($customer->getMiddlename())
            ->setLastname($customer->getLastname())
            ->setCountryId('GB')
            ->setPostcode('M1 1FF')
            ->setCity('Test City')
            ->setRegion('Test Region')
            ->setTelephone('01234567890')
            ->setFax('01234567890')
            ->setStreet("Street Line 1\nStreet Line 2");

        $addressCollection->addItem($customerAddress);

        $customerAddress = Mage::getModel('customer/address');
        /* @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress->setId(2)
            ->setFirstname($customer->getFirstname())
            ->setMiddleName($customer->getMiddlename())
            ->setLastname($customer->getLastname())
            ->setCountryId('GB')
            ->setPostcode('M2 1FF')
            ->setCity('Testville')
            ->setRegion('Test Region 2')
            ->setTelephone('01234567899')
            ->setFax('01234567899')
            ->setStreet('1 Street');

        $addressCollection->addItem($customerAddress);

        return $addressCollection;
    }
}