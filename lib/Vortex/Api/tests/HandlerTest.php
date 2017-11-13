<?php
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    const TEST_API_PREFIX = '/my-prefix/api';

    public function testHandleExistingRoute()
    {
        $_SERVER = ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => self::TEST_API_PREFIX . '/test'];
        $request = new Zend_Controller_Request_Http();
        $response = $this->getResponseMock();

        $handler = new Vortex_Api_Handler($request, $response,'Vortex_Api', self::TEST_API_PREFIX);

        $handler->handle();

        $contentTypeHeader = array_filter($response->getHeaders(), function($elm) {
            return $elm['name'] === 'Content-Type';
        });

        $this->assertEquals('application/json', $contentTypeHeader[0]['value']);
        $this->assertJsonStringEqualsJsonString('{ "test": "success" }', $response->getBody());
    }


    public function testHandleEmbeddedRoute()
    {
        $_SERVER = ['REQUEST_METHOD' => 'PATCH', 'REQUEST_URI' => self::TEST_API_PREFIX . '/test/more'];
        $request = new Zend_Controller_Request_Http();
        $response = $this->getResponseMock();

        $handler = new Vortex_Api_Handler($request, $response,'Vortex_Api', self::TEST_API_PREFIX);

        $handler->handle();

        $contentTypeHeader = array_filter($response->getHeaders(), function($elm) {
            return $elm['name'] === 'Content-Type';
        });

        $this->assertEquals('application/json', $contentTypeHeader[0]['value']);
        $this->assertJsonStringEqualsJsonString('{ "test": "embed_success" }', $response->getBody());
    }

    public function testHandleEmbeddedRouteWithForwardSlashAtEndOfUrl()
    {
        $_SERVER = ['REQUEST_METHOD' => 'PATCH', 'REQUEST_URI' => self::TEST_API_PREFIX . '/test/more/'];
        $request = new Zend_Controller_Request_Http();
        $response = $this->getResponseMock();

        $handler = new Vortex_Api_Handler($request, $response,'Vortex_Api', self::TEST_API_PREFIX);

        $handler->handle();

        $contentTypeHeader = array_filter($response->getHeaders(), function($elm) {
            return $elm['name'] === 'Content-Type';
        });

        $this->assertEquals('application/json', $contentTypeHeader[0]['value']);
        $this->assertJsonStringEqualsJsonString('{ "test": "embed_success" }', $response->getBody());
    }


    public function testHandleNoRoute()
    {
        $_SERVER = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => self::TEST_API_PREFIX . '/test'];
        $request = new Zend_Controller_Request_Http();
        $response = $this->getResponseMock();

        $handler = new Vortex_Api_Handler($request, $response,'Vortex_Api', self::TEST_API_PREFIX);

        $handler->handle();

        $contentTypeHeader = array_filter($response->getHeaders(), function($elm) {
            return $elm['name'] === 'Content-Type';
        });

        $this->assertEquals('application/json', $contentTypeHeader[0]['value']);
        $this->assertJsonStringEqualsJsonString('{ "errorCode": "404", "errorMessage": "Not found." }', $response->getBody());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getResponseMock()
    {
        $response = $this->getMockBuilder(Zend_Controller_Response_Http::class)
            ->setMethods(['canSendHeaders', 'sendHeaders'])
            ->getMock();

        $response->method('canSendHeaders')->willReturn(true);
        $response->method('sendHeaders')->willReturn(true);

        return $response;
    }
}

class Vortex_Api_Test_Post implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        return ['test' => 'success'];
    }
}

class Vortex_Api_Test_More_Patch implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        return ['test' => 'embed_success'];
    }
}