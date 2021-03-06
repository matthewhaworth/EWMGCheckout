<?php
class Vortex_Checkout_Api_Clickandcollect_Stores_Get implements Vortex_Api_EndpointInterface
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
        $longitude = $request->getParam('lng', false);
        $latitude = $request->getParam('lat', false);

        if (!$longitude || !$latitude) {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Sorry, we were not able to find that post code.'), 400);
        }

        if (!$longitude === 'notfound' || $latitude === 'notfound') {
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Unable to find stores in that area'), 400);
        }

        $location = json_encode(['lat' => $latitude, 'lng' => $longitude]);
        $distance = $request->getParam('distance', 100);

        try {
            $peacockStores = $this->getLookupService()->getStores(
                $this->getBasket(),
                $location,
                $distance
            );
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Could not find stores in that area.'), 500);
        }

        $stores = [];
        foreach ($peacockStores as $store) {
            if (count($stores) === 10) break; // Only retrieve 10 stores.
            $coordinates = $store->getCoordinates();
            /* @var $store MicrosMultiChannel_StoreLocator_Model_Store_Db */
            $streetName = ucwords(strtolower(implode(', ', $store->getStreet())));
            $stores[] = [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'address' => "{$streetName}, {$store->getCity()}, {$store->getPostcode()}, {$store->getCounty()}",
                'opening_hours' => ($store->getOpeningHours()) ? array_filter($store->getOpeningHours(), 'strlen') : null,
                'distance' => number_format($store->getDistance(), 1),
                'geolocation' => ['lat' => (float) $coordinates['latitude'], 'lng' => (float) $coordinates['longitude']]
            ];
        }

        return $stores;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getBasket()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @return MicrosMultiChannel_StoreLocator_Model_Lookup_Service
     */
    private function getLookupService()
    {
        return Mage::getModel('microsmultichannel_storelocator/lookup_service');
    }

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}