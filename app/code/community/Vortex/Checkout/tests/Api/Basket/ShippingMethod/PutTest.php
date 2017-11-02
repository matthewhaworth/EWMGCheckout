<?php

use PHPUnit\Framework\TestCase;

class Vortex_Api_Basket_ShippingMethod_PutTest extends TestCase
{
    public function testSetShippingMethod()
    {
        $shippingMethodPut = $this->getMockBuilder(Vortex_Checkout_Api_Basket_ShippingMethod_Put::class)
            ->setMethods(['getOnepageSingleton', 'getBasket'])
            ->getMock();


        $onepageSingletonMock = $this->getMockBuilder(Mage_Checkout_Model_Type_Onepage::class)
            ->setMethods(['saveShippingMethod'])
            ->getMock();

        $onepageSingletonMock->method('saveShippingMethod')->with('shipping_rate_1')->willReturn([]);
        $shippingMethodPut->method('getOnepageSingleton')->willReturn($onepageSingletonMock);

        $quoteMock = $this->getMockBuilder(Mage_Sales_Model_Quote::class)
            ->setMethods(['getShippingAddress'])
            ->getMock();

        $shippingAddressMock = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getId', 'getAllShippingRates', 'getShippingMethod'])
            ->getMock();

        $shippingAddressMock->method('getId')->willReturn(1);
        $shippingAddressMock->method('getAllShippingRates')->willReturn([]);
        $shippingAddressMock->method('getShippingMethod')->willReturn('shipping_rate_1');

        $quoteMock->method('getShippingAddress')->willReturn($shippingAddressMock);

        $shippingMethodPut->method('getBasket')->willReturn($quoteMock);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $shippingMethodPutResponse = $shippingMethodPut->execute(
            ['shipping_method' => 'shipping_rate_1'],
            $request,
            $response
        );

        $this->assertEquals('shipping_rate_1', $shippingMethodPutResponse['shipping_method']);
    }
}