<?php
class Vortex_Checkout_Helper_Config
    extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PCA_LOOKUP_KEY = 'ewmg_checkout/postcode_anywhere/lookup_key';
    const XML_CONFIG_PCA_FIND_ENDPOINT = 'ewmg_checkout/postcode_anywhere/find_endpoint';
    const XML_CONFIG_PCA_RETRIEVE_ENDPOINT = 'ewmg_checkout/postcode_anywhere/retrieve_endpoint';

    const XML_CONFIG_PCA_GEOCODE_KEY = 'ewmg_checkout/postcode_anywhere/geocode_key';
    const XML_CONFIG_PCA_GEOCODE_COUNTRY = 'ewmg_checkout/postcode_anywhere/geocode_country';
    const XML_CONFIG_PCA_GEOCODE_ENDPOINT = 'ewmg_checkout/postcode_anywhere/geocode_endpoint';

    const XML_CONFIG_EXPERIMENT_ID = 'ewmg_checkout/ab_testing/experiment_id';
    const XML_CONFIG_PERCENTAGE_TO_NEW_CHECKOUT = 'ewmg_checkout/ab_testing/percentage_to_new_checkout';

    const XML_CONFIG_REMAINING_ENABLED = 'ewmg_checkout/remaining_message/enabled';
    const XML_CONFIG_REMAINING_TOTAL = 'ewmg_checkout/remaining_message/total';

    const XML_CONFIG_CLICKCOLLECT_ACTIVE = 'carriers/clickcollectshipping/active';
    const XML_CONFIG_CLICKCOLLECT_PAID_ACTIVE = 'carriers/clickcollectshipping_paid/active';

    const XML_CONFIG_AMAZONPAY_ACTIVE = 'amazonpayments/general/active';

    const XML_CONFIG_PAYPAL_ACTIVE = 'payment/paypal_express/active';
    const XML_CONFIG_PAYPAL_UK_ACTIVE = 'payment/paypaluk_express/active';

    public function getPcaLookupKey()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PCA_LOOKUP_KEY);
    }

    public function getPcaFindEndpoint()
    {
        return 'https://services.postcodeanywhere.co.uk/CapturePlus/Interactive/Find/v2.10/json3ex.ws';//Mage::getStoreConfig(self::XML_CONFIG_PCA_FIND_ENDPOINT);
    }

    public function getPcaRetrieveEndpoint()
    {
        return 'https://services.postcodeanywhere.co.uk/CapturePlus/Interactive/RetrieveFormatted/v2.10/json3ex.ws';//Mage::getStoreConfig(self::XML_CONFIG_PCA_RETRIEVE_ENDPOINT);
    }

    public function getPcaGeocodeKey()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PCA_GEOCODE_KEY);
    }

    public function getPcaGeocodeCountry()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PCA_GEOCODE_COUNTRY);
    }

    public function getPcaGeocodeEndpoint()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PCA_GEOCODE_ENDPOINT);
    }

    public function getPercentageToPushToNewCheckout()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PERCENTAGE_TO_NEW_CHECKOUT);
    }

    public function getExperimentId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_EXPERIMENT_ID);
    }

    public function getRemainingEnabled()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_REMAINING_ENABLED);
    }

    public function getRemainingTotal()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_REMAINING_TOTAL);
    }

    public function getClickCollectActive()
    {
        return (bool)(Mage::getStoreConfig(self::XML_CONFIG_CLICKCOLLECT_ACTIVE) || Mage::getStoreConfig(self::XML_CONFIG_CLICKCOLLECT_PAID_ACTIVE));
    }

    public function getAmazonPayActive()
    {
        return (bool)(Mage::getStoreConfig(self::XML_CONFIG_AMAZONPAY_ACTIVE));
    }

    public function getPaypalActive()
    {
        return (bool)(Mage::getStoreConfig(self::XML_CONFIG_PAYPAL_ACTIVE) || Mage::getStoreConfig(self::XML_CONFIG_PAYPAL_UK_ACTIVE));
    }
}