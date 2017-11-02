<?php
require('app/code/core/Enterprise/GiftCardAccount/controllers/CartController.php');

class Vortex_Checkout_Api_Basket_Giftcard_Post implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        if (!isset($body['giftcard_code']) || !isset($body['giftcard_pin'])) {
            throw new Vortex_Api_Exception_BadRequest('Giftcard code and pin are required', 400);
        }

        $this->handleObservers($body, $request, $response);

        $code = $body['giftcard_code'];
        try {
            if (strlen($code) > Enterprise_GiftCardAccount_Helper_Data::GIFT_CARD_CODE_MAX_LENGTH) {
                throw new Vortex_Api_Exception_BadRequest('Invalid code', 400);
            }
            Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                ->loadByCode($code)
                ->addToCart();
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            throw new Vortex_Api_Exception_BadRequest('Invalid card', 400);
        } catch (Exception $e) {
            throw new Vortex_Api_Exception_BadRequest('Cannot apply gift card', 500);
        }

        $basketMapper = new Vortex_Checkout_Mapper_Basket(
            $this->getBasketItemsMapper(),
            new Vortex_Checkout_Mapper_Basket_Address(),
            $this->getCoreHelper()
        );


        // @todo remove the requirement for this to happen
        $this->getBasket()->collectTotals();
        return $basketMapper->map($this->getBasket());
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getBasket()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket_Items
     */
    protected function getBasketItemsMapper()
    {
        return new Vortex_Checkout_Mapper_Basket_Items(
            $this->getCoreHelper(),
            $this->getImageHelper(),
            $this->getConfigurationHelper()
        );
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('core');
    }

    /**
     * @return Mage_Catalog_Helper_Image
     */
    protected function getImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    /**
     * @return Mage_Catalog_Helper_Product_Configuration
     */
    protected function getConfigurationHelper()
    {
        return Mage::helper('catalog/product_configuration');
    }

    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return mixed
     * @throws Vortex_Api_Exception_BadRequest
     */
    protected function handleObservers($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        // Observer requires parameters to be GET
        $body['ajax'] = true;
        $request->setParams($body);

        // This event modifies the response object, when we need to have full control, so we copy it
        $oldResponse = $response;
        $clonedResponse = clone $response;

        Mage::app()->setResponse($clonedResponse);
        Mage::dispatchEvent(
            'controller_action_predispatch_enterprise_giftcardaccount_cart_add',
            ['controller_action' => new \Enterprise_GiftCardAccount_CartController($request, $response)]
        );

        Mage::app()->setResponse($oldResponse);

        if ($jsonResponse = json_decode($clonedResponse->getBody(), true)) {
            throw new Vortex_Api_Exception_BadRequest($jsonResponse['message'], 400);
        }
        return $body;
    }
}