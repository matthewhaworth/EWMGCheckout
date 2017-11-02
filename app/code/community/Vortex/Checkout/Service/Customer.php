<?php
class Vortex_Checkout_Service_Customer
{
    /**
     * @var Vortex_Checkout_Factory_Customer
     */
    protected $customerFactory;

    /**
     * @var Vortex_Checkout_Factory_Customer_Address
     */
    protected $customerAddressFactory;

    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $customerSession;

    /**
     * Vortex_Checkout_Service_Customer constructor.
     * @param Vortex_Checkout_Factory_Customer $customerFactory
     * @param Vortex_Checkout_Factory_Customer_Address $customerAddressFactory
     * @param Mage_Customer_Model_Session $customerSession
     */
    public function __construct(
        Vortex_Checkout_Factory_Customer $customerFactory,
        Vortex_Checkout_Factory_Customer_Address $customerAddressFactory,
        Mage_Customer_Model_Session $customerSession
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $email
     * @param $password
     * @return bool
     */
    public function authenticate($email, $password)
    {
        try {
            $authenticationSuccess = $this->customerSession->login($email, $password);
        } catch (Exception $e) {
            return false;
        }

        return $authenticationSuccess;
    }

    /**
     * @param string|int $id
     * @return Mage_Customer_Model_Customer|false
     */
    public function getCustomer($id)
    {
        if (is_string($id)) {
            $customer = $this->customerFactory->create()->setWebsiteId($this->getWebsiteId())->loadByEmail($id);
        } else {
            $customer = $this->customerFactory->create()->load($id);
        }

        return $customer && $customer->getId() > 0 ? $customer : false;
    }

    /**
     * @param $addressId
     * @param Mage_Customer_Model_Customer $customer
     * @return bool
     */
    public function doesAddressBelongToCustomer($addressId, Mage_Customer_Model_Customer $customer)
    {
        $address = $customer->getAddressById($addressId);
        return $address && $address->getId() > 0;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param $addressId
     * @param array $addressData
     * @return Mage_Customer_Model_Address
     */
    public function saveCustomerAddress(Mage_Customer_Model_Customer $customer, $addressId, array $addressData)
    {
        $addressData = $this->mapApiAddressToMagentoAddress($addressData, $customer->getId());

        $address = $customer->getAddressById($addressId);
        $address->setData(array_merge($address->getData(), $addressData));
        $address->save();

        return $address;
    }

    /**
     * @param array $apiAddress
     * @param $customerId
     * @return array
     */
    private function mapApiAddressToMagentoAddress(array $apiAddress, $customerId)
    {
        $magentoAddress = [
            'parent_id' => $customerId,
            'firstname' => $apiAddress['first_name'],
            'lastname' => $apiAddress['last_name'],
            'street' => [
                $apiAddress['line1'],
                $apiAddress['line2']
            ],
            'email' => $apiAddress['email'],
            'city' => $apiAddress['city'],
            'postcode' => $apiAddress['postcode'],
            'region' => $apiAddress['county'],
            'country_id' => $apiAddress['country'],
            'telephone' => $apiAddress['phone']
        ];

        return $magentoAddress;
    }

    /**
     * @return int
     */
    protected function getWebsiteId()
    {
        return Mage::app()->getWebsite()->getId();
    }
}