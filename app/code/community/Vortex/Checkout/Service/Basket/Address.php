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
            $this->magentoCheckoutService->getQuote()->getShippingAddress()->setSameAsBilling(false)->save();
        } else {

            $useShippingForBilling = array_key_exists('use_shipping_for_billing', $addressData)
                ? $addressData['use_shipping_for_billing'] : false;

            // Copy to billing address if one does not currently exist, this will set up guest users correctly
            if ($useShippingForBilling) {
                $saveResponseShipping = $this->magentoCheckoutService->saveShipping($mappedAddress, $customerAddressId);
                $saveResponseBilling = $this->magentoCheckoutService->saveBilling($mappedAddress, $customerAddressId);

                /**
                 * The following additional save only seems to be necessary for logged in users.
                 * It's due to the quote object in the 'saveBilling' method call being in a pristine state and hence
                 * not triggering _afterSave() on save(), which saves the addresses.
                 */
                $this->magentoCheckoutService->getQuote()->getBillingAddress()->save();
                $this->magentoCheckoutService->getQuote()->getShippingAddress()->setSameAsBilling(true)->save();

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

        $billingAddressData = array_intersect_key(
            $quote->getShippingAddress()->getData(),
            array_flip(['prefix', 'firstname', 'middle', 'lastname', 'suffix', 'company', 'street', 'city', 'postcode',
                'country_id', 'region', 'region_id', 'email', 'telephone'])
        );

        $shippingAddressData['address_type'] = Mage_Customer_Model_Address_Abstract::TYPE_BILLING;
        $shippingAddressData['quote_id'] = $quote->getId();

        $quote->getBillingAddress()->setData($billingAddressData)
            ->save();
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
            'company' => $apiAddress['company'],
            'street' => [
                $apiAddress['line1'],
                ($apiAddress['line3']) ? $apiAddress['line2'] . ', ' . $apiAddress['line3'] : $apiAddress['line2']
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
     * This method sets the quote address as the default customer address. In the event that the customer does not have
     * a default address set, it reverts to the address used in the customer's previous order.
     *
     * This feels like it should be looped, but I feel moving this into a loop would make it even harder to read.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Customer_Model_Customer $customer
     */
    public function setCustomerAddressIfAddressIsNotSet(
        Mage_Sales_Model_Quote $quote,
        Mage_Customer_Model_Customer $customer
    ) {
        // Billing address
        $chosenDefaultBillingAddress = false;
        $isBillingAddressValid = $quote->getBillingAddress()->validate();
        if ($isBillingAddressValid !== true) {
            if ($customer->getDefaultBillingAddress() && $customer->getDefaultBillingAddress()->getId()) {
                $chosenDefaultBillingAddress = $customer->getDefaultBillingAddress();
            } else {
                $orderCollection = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->setOrder('created_at', 'desc');

                $lastOrder = $orderCollection->getFirstItem();
                /** @var $lastOrder Mage_Sales_Model_Order */
                if ($lastOrder) {
                    $lastCustomerAddressId = $lastOrder->getBillingAddress() ?
                        $lastOrder->getBillingAddress()->getCustomerAddressId() :
                        false;

                    if ($lastCustomerAddressId) {
                        $chosenDefaultBillingAddress = Mage::getModel('customer/address')
                            ->load($lastCustomerAddressId);
                    }
                }
            }
        }

        if ($chosenDefaultBillingAddress !== false) {
            $this->magentoCheckoutService->saveBilling(
                $chosenDefaultBillingAddress->getData(),
                $chosenDefaultBillingAddress->getId()
            );
        }

        // Shipping address
        $chosenDefaultShippingAddress = false;
        $isShippingAddressValid = $quote->getShippingAddress()->validate();
        if ($isShippingAddressValid !== true) {
            if ($customer->getDefaultShippingAddress() && $customer->getDefaultShippingAddress()->getId()) {
                $chosenDefaultShippingAddress = $customer->getDefaultShippingAddress();
            } else {
                $orderCollection = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->setPageSize(1)
                    ->setCurPage(1)
                    ->setOrder('created_at', 'desc');

                $lastOrder = $orderCollection->getFirstItem();
                /** @var $lastOrder Mage_Sales_Model_Order */
                if ($lastOrder) {
                    $lastCustomerAddressId = $lastOrder->getShippingAddress() ?
                        $lastOrder->getShippingAddress()->getCustomerAddressId() :
                        false;

                    if ($lastCustomerAddressId) {
                        $chosenDefaultShippingAddress = Mage::getModel('customer/address')
                            ->load($lastCustomerAddressId);
                    }
                }
            }
        }

        if ($chosenDefaultShippingAddress !== false) {
            $this->magentoCheckoutService->saveShipping(
                $chosenDefaultShippingAddress->getData(),
                $chosenDefaultShippingAddress->getId()
            );
        }
    }
}
