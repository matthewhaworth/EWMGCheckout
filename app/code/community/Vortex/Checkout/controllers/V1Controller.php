<?php

class Vortex_Checkout_V1Controller extends Mage_Core_Controller_Front_Action
{
    /**
     * Delegate to the api router
     */
    public function apiAction()
    {
        if ($this->isFormkeyValidationOnCheckoutEnabled() && !$this->_validateFormKey()) {
            $response = $this->getResponse();
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody(json_encode(['errorCode' => 403, 'errorMessage' => 'Invalid form key.']));
            $response->setHttpResponseCode(403);
            return;
        }

        $handler = new Vortex_Api_Handler(
            $this->getRequest(),
            $this->getResponse(),
            'Vortex_Checkout_Api',
            '/fast-checkout/v1/api'
        );

        $handler->handle();
    }
}