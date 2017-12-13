<?php
class Vortex_Checkout_Mapper_Basket_Address
{
    /**
     * @param $regionId
     * @return string
     */
    private function getRegionCodeByRegionId($regionId)
    {
        $region = Mage::getModel('directory/region')->load($regionId);
        /** @var $region Mage_Directory_Model_Region */
        return $region->getCode();
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $quoteAddress
     * @return array
     */
    public function map(Mage_Sales_Model_Quote_Address $quoteAddress)
    {
        $response = [
            'address_id' => $quoteAddress->getId(),
            'first_name' => $quoteAddress->getFirstname(),
            'last_name' => $quoteAddress->getLastname(),
            'email' => $quoteAddress->getEmail(),
            'phone' => $quoteAddress->getTelephone(),
            'company' => $quoteAddress->getCompany(),
            'line1' => $quoteAddress->getStreet1(),
            'line2' => $quoteAddress->getStreet2(),
            'postcode' => $quoteAddress->getPostcode(),
            'city' => $quoteAddress->getCity(),
            'county' => $quoteAddress->getRegion(),
            'region_id' => $this->getRegionCodeByRegionId($quoteAddress->getRegionId()),
            'country' => $quoteAddress->getCountryId(),
        ];

        if ($quoteAddress->getCustomerAddressId()) {
            $response['customer_address_id'] = $quoteAddress->getCustomerAddressId();
        } else {
            $response['customer_address_id'] = null;
        }


        $response['save_in_address_book'] = $quoteAddress->getSaveInAddressBook() ? true : false;

        $response['is_valid'] = is_array($quoteAddress->validate()) ? false: true;

        return $response;
    }
}