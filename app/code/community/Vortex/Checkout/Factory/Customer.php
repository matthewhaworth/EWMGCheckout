<?php
class Vortex_Checkout_Factory_Customer
{
    /**
     * @return Mage_Customer_Model_Customer
     */
    public function create()
    {
        return Mage::getModel('customer/customer');
    }
}