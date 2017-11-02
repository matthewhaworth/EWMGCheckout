<?php
class Vortex_Checkout_Model_Carrier_ClickCollectShippingPaid
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'clickcollectshipping_paid';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $requestItems = $request->getAllItems();
        if (count($requestItems)) {

            $firstItem = array_pop($requestItems);
            if ($firstItem->getQuote()->getIsClickAndCollect() && $this->getConfigData('active')) {
                $helper = Mage::helper('clickcollectshipping');
                if ($request->getBaseSubtotalInclTax() <= $this->getConfigData('threshold')) {
                    //this is the correct way to return a shipping method (not used)
                    $result = Mage::getModel('shipping/rate_result');
                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->getConfigData('title'));
                    $method->setMethod($this->_code);
                    $method->setMethodTitle($this->getConfigData('name'));
                    $method->setPrice($this->getConfigData('price'));
                    $method->setCost(0);
                    $result->append($method);

                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }
}
