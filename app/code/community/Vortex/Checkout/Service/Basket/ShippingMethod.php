<?php
class Vortex_Checkout_Service_Basket_ShippingMethod
{
    /**
     * @var Mage_Checkout_Model_Cart
     */
    private $checkoutCart;

    /**
     * Vortex_Checkout_Service_Basket_ShippingMethod constructor.
     * @param Mage_Checkout_Model_Cart $checkoutCart
     */
    public function __construct(Mage_Checkout_Model_Cart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * @return array
     */
    public function getShippingRates()
    {
        $quote = $this->checkoutCart->getQuote();
        $quote->getShippingAddress()->collectShippingRates()->save();
        return $quote->getShippingAddress()->getAllShippingRates();
    }
}