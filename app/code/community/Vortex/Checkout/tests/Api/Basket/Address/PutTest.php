<?php
use PHPUnit\Framework\TestCase;

class Vortex_Checkout_Api_Basket_Address_PutTest extends TestCase
{
    public function testSetGuestCustomerBillingAddress()
    {
        $basketAddressPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Address_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getEmail', 'getFirstname', 'getLastname', 'getStreet1', 'getStreet2', 'getCity', 'getRegion', 'getPostcode', 'getCountryId', 'getTelephone'])
            ->getMock();

        $quoteAddress->method('getId')->willReturn(1);
        $quoteAddress->method('getEmail')->willReturn('test@example.com');
        $quoteAddress->method('getFirstname')->willReturn('bob');
        $quoteAddress->method('getLastname')->willReturn('jones');
        $quoteAddress->method('getStreet1')->willReturn('1 test street');
        $quoteAddress->method('getStreet2')->willReturn('off test lane');
        $quoteAddress->method('getCity')->willReturn('testville');
        $quoteAddress->method('getPostcode')->willReturn('AB1 2DE');
        $quoteAddress->method('getRegion')->willReturn('Test');
        $quoteAddress->method('getCountryId')->willReturn('GB');
        $quoteAddress->method('getTelephone')->willReturn('01234567890');

        $quote->method('getBillingAddress')->willReturn($quoteAddress);

        $basketAddressPut->method('getBasket')->willReturn($quote);

        $requestBody = [
            'email' => 'test@example.com',
            'first_name' => 'bob',
            'last_name' => 'jones',
            'line1' => '1 test street',
            'line2' => 'off test lane',
            'city' => 'testville',
            'postcode' => 'AB1 2DE',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
            'type' => 'billing'
        ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketAddressPutResponse = $basketAddressPut->execute($requestBody, $request, $response);

        $this->assertEquals([
            'address_id' => 1,
            'email' => 'test@example.com',
            'first_name' => 'bob',
            'last_name' => 'jones',
            'line1' => '1 test street',
            'line2' => 'off test lane',
            'city' => 'testville',
            'postcode' => 'AB1 2DE',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
        ], $basketAddressPutResponse['billing_address']);
    }

    public function testSetGuestCustomerShippingAddress()
    {
        $basketAddressPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Address_Put::class)
            ->setMethods(['getBasket'])
            ->getMock();

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getShippingAddress'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getEmail', 'getFirstname', 'getLastname', 'getStreet1', 'getStreet2', 'getCity', 'getRegion', 'getPostcode', 'getCountryId', 'getTelephone', 'getAllShippingRates'])
            ->getMock();

        $quoteAddress->method('getId')->willReturn(2);
        $quoteAddress->method('getEmail')->willReturn('bob@example.com');
        $quoteAddress->method('getFirstname')->willReturn('bobby');
        $quoteAddress->method('getLastname')->willReturn('elsewhere');
        $quoteAddress->method('getStreet1')->willReturn('2 test street');
        $quoteAddress->method('getStreet2')->willReturn('off test lane');
        $quoteAddress->method('getCity')->willReturn('testville 2');
        $quoteAddress->method('getPostcode')->willReturn('AB1 2DD');
        $quoteAddress->method('getRegion')->willReturn('Test');
        $quoteAddress->method('getCountryId')->willReturn('GB');
        $quoteAddress->method('getTelephone')->willReturn('01234567890');

        $quote->method('getShippingAddress')->willReturn($quoteAddress);

        $basketAddressPut->method('getBasket')->willReturn($quote);

        $requestBody = [
            'email' => 'bob@example.com',
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
            'type' => 'shipping'
        ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketAddressPutResponse = $basketAddressPut->execute($requestBody, $request, $response);

        $this->assertEquals([
            'address_id' => 2,
            'email' => 'bob@example.com',
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
        ], $basketAddressPutResponse['shipping_address']);
    }

    public function testSetExistingCustomerBillingAddress()
    {
        $basketAddressPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Address_Put::class)
            ->setMethods(['getBasket', 'getOnepageSingleton'])
            ->getMock();

        $onepageSingletonMock = $this->getMockBuilder(Mage_Checkout_Model_Type_Onepage::class)
            ->setMethods(['saveBilling'])
            ->getMock();

        $onepageSingletonMock->method('saveBilling')->willReturn([]);

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getBillingAddress'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getCustomerAddressId', 'getEmail', 'getFirstname', 'getLastname', 'getStreet1', 'getStreet2', 'getCity', 'getRegion', 'getPostcode', 'getCountryId', 'getTelephone'])
            ->getMock();

        $quoteAddress->method('getCustomerAddressId')->willReturn(6);

        $quoteAddress->method('getId')->willReturn(1);
        $quoteAddress->method('getEmail')->willReturn('bob@example.com');
        $quoteAddress->method('getFirstname')->willReturn('bobby');
        $quoteAddress->method('getLastname')->willReturn('elsewhere');
        $quoteAddress->method('getStreet1')->willReturn('2 test street');
        $quoteAddress->method('getStreet2')->willReturn('off test lane');
        $quoteAddress->method('getCity')->willReturn('testville 2');
        $quoteAddress->method('getPostcode')->willReturn('AB1 2DD');
        $quoteAddress->method('getRegion')->willReturn('Test');
        $quoteAddress->method('getCountryId')->willReturn('GB');
        $quoteAddress->method('getTelephone')->willReturn('01234567890');

        $quote->method('getBillingAddress')->willReturn($quoteAddress);

        $basketAddressPut->method('getBasket')->willReturn($quote);
        $basketAddressPut->method('getOnepageSingleton')->willReturn($onepageSingletonMock);

        $requestBody = [
            'customer_address_id' => 6,
            'email' => 'bob@example.com',
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
            'type' => 'billing'
        ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketAddressPutResponse = $basketAddressPut->execute($requestBody, $request, $response);

        $this->assertEquals([
            'customer_address_id' => 6,
            'email' => 'bob@example.com',
            'address_id' => 1,
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
        ], $basketAddressPutResponse['billing_address']);
    }

    public function testSetExistingCustomerShippingAddress()
    {
        $basketAddressPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_Address_Put::class)
            ->setMethods(['getBasket', 'getOnepageSingleton'])
            ->getMock();

        $onepageSingletonMock = $this->getMockBuilder(Mage_Checkout_Model_Type_Onepage::class)
            ->setMethods(['saveShipping'])
            ->getMock();

        $onepageSingletonMock->method('saveShipping')->willReturn([]);

        $quote = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getShippingAddress'])
            ->getMock();

        $quoteAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getCustomerAddressId', 'getEmail', 'getFirstname', 'getLastname', 'getStreet1', 'getStreet2', 'getCity', 'getRegion', 'getPostcode', 'getCountryId', 'getTelephone', 'getAllShippingRates'])
            ->getMock();

        $quoteAddress->method('getCustomerAddressId')->willReturn(6);

        $quoteAddress->method('getId')->willReturn(1);
        $quoteAddress->method('getEmail')->willReturn('bob@example.com');
        $quoteAddress->method('getFirstname')->willReturn('bobby');
        $quoteAddress->method('getLastname')->willReturn('elsewhere');
        $quoteAddress->method('getStreet1')->willReturn('2 test street');
        $quoteAddress->method('getStreet2')->willReturn('off test lane');
        $quoteAddress->method('getCity')->willReturn('testville 2');
        $quoteAddress->method('getPostcode')->willReturn('AB1 2DD');
        $quoteAddress->method('getRegion')->willReturn('Test');
        $quoteAddress->method('getCountryId')->willReturn('GB');
        $quoteAddress->method('getTelephone')->willReturn('01234567890');

        $quote->method('getShippingAddress')->willReturn($quoteAddress);

        $basketAddressPut->method('getBasket')->willReturn($quote);
        $basketAddressPut->method('getOnepageSingleton')->willReturn($onepageSingletonMock);

        $requestBody = [
            'customer_address_id' => 6,
            'email' => 'bob@example.com',
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
            'type' => 'shipping'
        ];

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $basketAddressPutResponse = $basketAddressPut->execute($requestBody, $request, $response);

        $this->assertEquals([
            'customer_address_id' => 6,
            'email' => 'bob@example.com',
            'address_id' => 1,
            'first_name' => 'bobby',
            'last_name' => 'elsewhere',
            'line1' => '2 test street',
            'line2' => 'off test lane',
            'city' => 'testville 2',
            'postcode' => 'AB1 2DD',
            'county' => 'Test',
            'country' => 'GB',
            'phone' => '01234567890',
        ], $basketAddressPutResponse['shipping_address']);
    }
}