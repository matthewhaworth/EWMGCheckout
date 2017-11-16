<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'OnepageController.php';

class Vortex_Checkout_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        $serveNewCheckout = $session->getData(Vortex_Checkout_Model_Observer::IS_NEW_CHECKOUT);

        if (!$serveNewCheckout) {
            return parent::successAction();
        }

        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();

        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($session->getLastOrderId())));

        $this->_redirect('checkout/cart', ['_fragment' => '/success/'. $session->getLastRealOrder()->getIncrementId()]);
    }
}