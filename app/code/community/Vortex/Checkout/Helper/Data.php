<?php
class Vortex_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mage_Directory_Model_Resource_Country_Collection
     */
    protected $_countryCollection;

    private function getAllowedCountries()
    {
        return Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true);
    }

    public function getCountriesJson(){

        return ($this->hasAllowedCountries()) ? Zend_Json_Encoder::encode($this->getAllowedCountries()) : "[]";
    }

    public function hasAllowedCountries()
    {
        $allowedCountries = $this->getAllowedCountries();

        return is_array($allowedCountries) && count($allowedCountries) > 0;
    }

    /**
     * Returns the list of countries, for which region is required
     *
     * @param boolean $asJson
     * @return array
     */
    public function getCountriesWithStatesRequired($asJson = false)
    {
        $countryList = explode(',', Mage::getStoreConfig(Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED));
            return Mage::helper('core')->jsonEncode($countryList);

    }

    /**
     * Retrieve country collection
     *
     * @return Mage_Directory_Model_Resource_Country_Collection
     */
    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getModel('directory/country')->getResourceCollection();
        }
        return $this->_countryCollection;
    }


    /**
     * Retrieve regions data json
     *
     * @return string
     */
    public function getRegionJson()
    {
        $countryIds = array();

        $countryCollection = $this->getCountryCollection()->loadByStore(Mage::app()->getStore()->getId());
        foreach ($countryCollection as $country) {
            $countryIds[] = $country->getCountryId();
        }

        /** @var $regionModel Mage_Directory_Model_Region */
        $regionModel = Mage::getModel('directory/region');
        /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
        $collection = $regionModel->getResourceCollection()
            ->addCountryFilter($countryIds)
            ->load();

        $countryRegions = [];
        foreach ($collection as $region) {
            /** @var $region Mage_Directory_Model_Region */
            if (!$region->getRegionId()) {
                continue;
            }

            $countryCode = $countryCollection->getItemById($region->getCountryId())->getCountryId();

            $countryRegions[$countryCode][] = [
                'value' => $region->getCode() === "" ? null : $region->getCode(),
                'label' => $this->__($region->getName())
            ];
        }

        return Zend_Json_Encoder::encode($countryRegions);
    }

    public function getConfigValue($configPath)
    {
        return Mage::getStoreConfig('');
    }
}