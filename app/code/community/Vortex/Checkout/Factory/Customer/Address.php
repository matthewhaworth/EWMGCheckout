<?php
class Vortex_Checkout_Factory_Customer_Address
{
    /**
     * @return Mage_Customer_Model_Address
     */
    public function create()
    {
        return Mage::getModel('customer/address');
    }
}