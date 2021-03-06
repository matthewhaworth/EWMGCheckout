<?php

/**
 * Interface Vortex_Api_EndpointInterface
 */
interface Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response);
}