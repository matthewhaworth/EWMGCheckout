<?php

class Vortex_Checkout_Block_Tagmanager extends Yireo_GoogleTagManager_Block_Default
{
    protected function _prepareLayout()
    {
        $configHelper = Mage::helper('vortex_checkout/config');
        /** @var $configHelper Vortex_Checkout_Helper_Config */

        $checkoutSession = Mage::getSingleton('checkout/session');
        /** @var $checkoutSession Mage_Checkout_Model_Session */

        $isNewCheckout = $checkoutSession->getData(Vortex_Checkout_Model_Observer::IS_NEW_CHECKOUT);
        $experimentId = $configHelper->getExperimentId();

        if (!is_null($experimentId) && !is_null($isNewCheckout)) {
            $variationId = $isNewCheckout ? '1' : '0';
            $this->addAttribute('googleOptimizeExperimentId', $experimentId);
            $this->addAttribute('googleOptimizeExperimentVariation', $variationId);
        }

        return parent::_prepareLayout();
    }
}