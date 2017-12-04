<?php
class Vortex_Checkout_Service_Basket_Address
{
    const ADDRESS_TYPE_BILLING = 'billing';
    const ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * @var Mage_Checkout_Model_Type_Onepage
     */
    private $magentoCheckoutService;

    public function __construct(Mage_Checkout_Model_Type_Onepage $magentoCheckoutService)
    {
        $this->magentoCheckoutService = $magentoCheckoutService;
    }

    /**
     * @param array $addressData
     * @return Mage_Checkout_Model_Type_Onepage
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function saveAddress(array $addressData)
    {
        $addressType = $addressData['type'] ?: false;
        if (!$addressType || !in_array($addressType, [self::ADDRESS_TYPE_BILLING, self::ADDRESS_TYPE_SHIPPING])) {
            // @todo should the service know about api exceptions?
            throw new Vortex_Api_Exception_BadRequest("Address must include type of 'billing' or 'shipping'", 400);
        }

        $customerAddressId = $addressData['customer_address_id'] ?: false;

        $mappedAddress = $this->mapApiAddressToMagentoAddress($addressData);
        if ($addressType === self::ADDRESS_TYPE_BILLING) {
            $saveResponse = $this->magentoCheckoutService->saveBilling($mappedAddress, $customerAddressId);
        } else {

            $useShippingForBilling = array_key_exists('use_shipping_for_billing', $addressData)
                ? $addressData['use_shipping_for_billing'] : false;

            // Copy to billing address if one does not currently exist, this will set up guest users correctly
            if ($useShippingForBilling) {
                $saveResponseShipping = $this->magentoCheckoutService->saveShipping($mappedAddress, $customerAddressId);
                $saveResponseBilling = $this->magentoCheckoutService->saveBilling($mappedAddress, $customerAddressId);
                $saveResponse = array_merge($saveResponseShipping, $saveResponseBilling);
            } else {
                $saveResponse = $this->magentoCheckoutService->saveShipping($mappedAddress, $customerAddressId);
            }
        }

        if (array_key_exists('error', $saveResponse) && $saveResponse['error']) {
            if (is_array($saveResponse['message'])) {
                $saveResponse['message'] = implode(',', $saveResponse['message']);
            }

            // @todo should the service know about api exceptions?
            throw new Vortex_Api_Exception_BadRequest("Validation failed: {$saveResponse['message']}", 400);
        }

        return $saveResponse;
    }

    /**
     * This is useful for times like click and collect, if you don't need a billing address but Magento requires one
     */
    public function setBillingAddressAsShippingAddress()
    {
        $quote = $this->magentoCheckoutService->getQuote();
        $billingAddress = $quote->getBillingAddress();

        $shippingAddressData = $quote->getShippingAddress()->getData();
        unset($shippingAddressData['address_id']);
        $shippingAddressData['address_type'] = Mage_Customer_Model_Address_Abstract::TYPE_BILLING;
        $shippingAddressData['quote_id'] = $quote->getId();

        $billingAddress->addData($shippingAddressData);
        $billingAddress->save();
    }

    /**
     * @param array $apiAddress
     * @return array
     */
    private function mapApiAddressToMagentoAddress(array $apiAddress)
    {
        $magentoAddress = [
            'firstname' => $apiAddress['first_name'],
            'lastname' => $apiAddress['last_name'],
            'street' => [
                $apiAddress['line1'],
                $apiAddress['line2']
            ],
            'email' => $apiAddress['email'],
            'city' => $apiAddress['city'],
            'postcode' => $apiAddress['postcode'],
            'country_id' => $apiAddress['country'],
            'telephone' => $apiAddress['phone']
        ];

        if (array_key_exists('region_id', $apiAddress) && $apiAddress['region_id'] !== '') {
            $magentoAddress['region_id'] = Mage::getModel('directory/region')->loadByCode(
                $apiAddress['region_id'],
                $magentoAddress['country_id']
            )->getId();
        }

        if (array_key_exists('county', $apiAddress) && $apiAddress['county'] !== '') {
            $magentoAddress['region'] = $apiAddress['county'];
        }

        if (array_key_exists('address_id', $apiAddress)) {
            $magentoAddress['address_id'] = $apiAddress['address_id'];
        }

        if (array_key_exists('save_in_address_book', $apiAddress)) {
            $magentoAddress['save_in_address_book'] = $apiAddress['save_in_address_book'];
        }

        return $magentoAddress;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Customer_Model_Address $customerBillingAddress
     * @param Mage_Customer_Model_Address $customerShippingAddress
     */
    public function setCustomerAddressIfAddressIsNotSet(
        Mage_Sales_Model_Quote $quote,
        $customerBillingAddress,
        $customerShippingAddress
    ) {
        $isBillingAddressValid = $quote->getBillingAddress()->validate();
        if ($isBillingAddressValid !== true && $customerBillingAddress && $customerBillingAddress->getId()) {
            $this->magentoCheckoutService->saveBilling(
                $customerBillingAddress->getData(),
                $customerBillingAddress->getId()
            );
        }

        $isShippingAddressValid = $quote->getShippingAddress()->validate();
        if ($isShippingAddressValid !== true && $customerShippingAddress && $customerShippingAddress->getId()) {
            $this->magentoCheckoutService->saveShipping(
                $customerShippingAddress->getData(),
                $customerShippingAddress->getId()
            );
        }

    }
}
