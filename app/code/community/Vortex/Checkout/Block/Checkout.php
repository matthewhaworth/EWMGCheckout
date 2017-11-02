<?php
class Vortex_Checkout_Block_Checkout extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        //$this->getLayout()->getBlock('checkout.head')->addCss($this->getCompiledCssFilename());
        return parent::_prepareLayout();
    }

    /**
     * @todo this is potentially a bit flaky
     * @return string
     */
    public function getCompiledJsFilename()
    {
        $path = 'skin/frontend/base/default/vortex/checkout/static/js/';
        $jsFile = scandir(MAGENTO_ROOT . DS . $path)[2];
        $skinPath = "vortex/checkout/static/js/{$jsFile}";
        return $this->getSkinUrl($skinPath);
    }

    /**
     * @todo this is potentially a bit flaky
     * @return string
     */
    public function getCompiledCssFilename()
    {
        $path = 'skin/frontend/base/default/vortex/checkout/static/css/';
        $jsFile = scandir(MAGENTO_ROOT . DS . $path)[2];
        $skinPath = "vortex/checkout/static/css/{$jsFile}";
        return $this->getSkinUrl($skinPath);
    }

    public function isDeveloperMode()
    {
        return true;
    }
}