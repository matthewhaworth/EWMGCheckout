<?php
class Vortex_Checkout_Model_Observer
{
    const IS_NEW_CHECKOUT = 'is_new_checkout';
    const CHECKOUT_CHOSEN = 'checkout_chosen';

    /**
     * Be aware that this is triggered once per item in the cart
     *
     * @param Varien_Event_Observer $observer
     */
    public function addDiscountBreakdownToQuote(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */

        $rule = $observer->getEvent()->getRule();
        /* @var $rule Mage_SalesRule_Model_Rule */

        $result = $observer->getEvent()->getResult();

        if ($result['discount_amount'] == 0) {
            return;
        }

        $discountBreakdown = $quote->getDiscountBreakdown() ?: [];

        $currentDiscount = (count($discountBreakdown) && array_key_exists($rule->getId(), $discountBreakdown))
            ? $discountBreakdown[$rule->getId()]['discount_amount']
            : 0;
        $discountBreakdown[$rule->getId()] = [
            'discount_label' => $rule->getName(),
            'discount_amount' => $currentDiscount + $result['discount_amount'],
            'discount_code' => $rule->getCouponCode() ?: ($rule->getCode() ?: '')
        ];

        $quote->setDiscountBreakdown($discountBreakdown);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function forceSplitTestCheckout(Varien_Event_Observer $observer)
    {
        $controllerAction = $observer->getControllerAction();
        /** @var $controllerAction Mage_Core_Controller_Front_Action */
        $checkoutSession = $this->getCheckoutSession();

        $forceCheckout = $controllerAction->getRequest()->getParam(self::IS_NEW_CHECKOUT, false);
        if ($forceCheckout) {
            $checkoutSession->setData(self::IS_NEW_CHECKOUT, true)
                ->setData(self::CHECKOUT_CHOSEN, true);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Controller_Varien_Exception
     */
    public function splitTestCheckout(Varien_Event_Observer $observer)
    {
        $checkoutSession = $this->getCheckoutSession();

        $isCheckoutChosen = $checkoutSession->getData(self::CHECKOUT_CHOSEN, false);
        if (!$isCheckoutChosen) {
            $checkoutSession->setData(self::CHECKOUT_CHOSEN, true);
            $rand = rand(1, 100);
            $chosenForNewCheckout = ($rand <= $this->getConfigHelper()->getPercentageToPushToNewCheckout());
            $checkoutSession->setData(self::IS_NEW_CHECKOUT, $chosenForNewCheckout);
        }

        $serveNewCheckout = $checkoutSession->getData(self::IS_NEW_CHECKOUT);
        if ($serveNewCheckout) {
            $e = new Mage_Core_Controller_Varien_Exception();
            $e->prepareForward('index','index','fast-checkout');
            throw $e;
        }
    }

    /**
     * @return Vortex_Checkout_Helper_Config
     */
    private function getConfigHelper()
    {
        return Mage::helper('vortex_checkout/config');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    private function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Most Magento checkouts have specific calls to action to force a re-select an re-save a customer address
     * against the quote. This checkout does not. As a result, if you edit your customer address whilst a quote
     * exists, the quote address, even if associated with a customer address, will not be updated.
     *
     * This method updates the quote address on customer address save.
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateQuoteAddressOnCustomerAddressSave(Varien_Event_Observer $observer)
    {
        $customerAddress = $observer->getEvent()->getCustomerAddress();
        $quoteAddresses = $this->getCheckoutSession()->getQuote()->getAddressesCollection();

        foreach ($quoteAddresses as $quoteAddress) {
            /** @var $quoteAddress Mage_Sales_Model_Quote_Address */
            if ($quoteAddress->getCustomerAddressId() === $customerAddress->getId()) {
                $quoteAddress->importCustomerAddress($customerAddress)->save();
            }
        }
    }

//    public function disableCartMergeOnLogin(Varien_Event_Observer $observer)
//    {
//        if ($observer->getSource()->hasItems()) {
//            if (is_object($observer->getQuote()) && $observer->getQuote()->getId()) {
//                $observer->getQuote()->removeAllItems();
//            }
//        }
//    }
}
