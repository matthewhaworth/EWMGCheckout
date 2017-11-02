<?php
class Vortex_Checkout_Api_Cms_Get implements Vortex_Api_EndpointInterface
{
    const LAYOUT_HANDLE = 'vortex_checkout_cms_%s';

    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        $layoutHandle = $request->getParam('layout_handle', false);
        if (!$layoutHandle) {
            throw new Vortex_Api_Exception_BadRequest('Could not load CMS content', 400);
        }

        try {
            $layout = $this->getLayout();

            $layoutUpdate = $layout->getUpdate();
            $layoutUpdate->addHandle(sprintf(self::LAYOUT_HANDLE, $layoutHandle));
            $layoutUpdate->load();

            $layout->generateXml();
            $layout->generateBlocks();
            $layout->setDirectOutput(false);

            return ['html' => $layout->getOutput()];
        } catch (Exception $e) {
            Mage::logException($e);
            return ['html' => ''];
        }
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    protected function getLayout()
    {
        return Mage::app()->getLayout();
    }
}