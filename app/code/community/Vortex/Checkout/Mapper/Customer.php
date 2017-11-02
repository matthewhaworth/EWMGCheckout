<?php
class Vortex_Checkout_Mapper_Customer
{
    /**
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    private function mapSavedCards(Mage_Customer_Model_Customer $customer)
    {
        $collection = Mage::getModel('worldpay/recurring')->getCollection()
            ->filterByCustomer($customer)->addCardDetails();

        $savedCards = [];
        foreach ($collection as $savedCard) {
            $orderCodes = explode('-', $savedCard->getOrderCode());
            $orderIncrementId = $orderCodes[0];

            $method = Mage::helper('worldpay')->loadMethod($savedCard->getMethod())->getName()->__toString();

            $savedCardData = [
                'id' => $savedCard->getOrderCode(),
                'card_number' => $savedCard->getCardNumber(),
                'last_four' => substr($savedCard->getCardNumber(), -4),
                'type' => $method
            ];

            $order = Mage::getModel('sales/order')->load($orderIncrementId);
            /** @var Mage_Sales_Model_Order $order */
            if ($order->getShippingAddress() && $order->getShippingAddress()->getCustomerAddressId()) {
                $savedCardData['customer_address_id'] = $order->getShippingAddress()->getCustomerAddressId();
            }

            $savedCards[] = $savedCardData;
        }

        return $savedCards;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isLoggedIn
     * @return array
     */
    public function map(Mage_Customer_Model_Customer $customer, $isLoggedIn = false)
    {
        $mappedData = [
            'name' => $customer->getFirstname(),
            'email' => $customer->getEmail()
        ];

        if ($isLoggedIn) {
            $mappedData['id'] = $customer->getId();

            $addresses = [];
            foreach ($customer->getAddressesCollection() as $address) {
                /* @var $address Mage_Customer_Model_Address */
                $addresses[] = [
                    'customer_address_id' => $address->getId(),
                    'first_name' => $address->getFirstname(),
                    'last_name' => $address->getLastname(),
                    'line1' => $address->getStreet1(),
                    'line2' => $address->getStreet2(),
                    'postcode' => $address->getPostcode(),
                    'city' => $address->getCity(),
                    'county' => $address->getRegion(),
                    'country' => $address->getCountry(),
                    'phone' => $address->getTelephone()
                ];
            }

            $mappedData['addresses'] = $addresses;
            $mappedData['savedCards'] = $this->mapSavedCards($customer);
        }

        return $mappedData;
    }
}