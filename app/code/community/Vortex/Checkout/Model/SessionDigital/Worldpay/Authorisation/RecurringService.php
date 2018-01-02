<?php
class Vortex_Checkout_Model_SessionDigital_Worldpay_Authorisation_RecurringService extends SessionDigital_WorldPay_Model_Authorisation_RecurringService
{
    public function authorizePayment(
        Mage_Sales_Model_Order $mageOrder,
        Mage_Sales_Model_Quote $quote,
        $orderCode,
        $orderStoreId,
        $paymentDetails
    )
    {
        $recurringOrderParams = Mage::getModel('worldpay/mapping_service')->collectRecurringOrderParameters(
            $orderCode,
            $quote,
            $orderStoreId,
            $paymentDetails
        );

        $response = Mage::getModel('worldpay/Request_PaymentServiceRequest')->orderRecurring($recurringOrderParams);
        $directResponse = Mage::getModel('worldpay/Response_DirectResponse')->setResponse($response);

        $worldPayOrder = Mage::getModel('worldpay/order_service')->getByIncrementId($mageOrder->getIncrementId());

        $paymentUpdate = $this->_createPaymentUpdateFromResponse($directResponse);
        $paymentUpdate->apply($worldPayOrder);

        $this->_abortIfPaymentError($paymentUpdate);

        $quote->setActive(false);
//        $worldPayOrder->sendOrderEmail(); Commented out as this sends multiple confirmation emails.
    }

    private function _createPaymentUpdateFromResponse($directResponse)
    {
        $paymentService = Mage::getModel('worldpay/payment_service');
        return $paymentService->createPaymentUpdateFromWorldPayXml($directResponse->getXml());
    }

    private function _abortIfPaymentError($paymentUpdate)
    {
        if ($paymentUpdate instanceof SessionDigital_WorldPay_Model_Payment_Update_Cancelled) {
            throw new SessionDigital_WorldPay_Model_Authorisation_Exception('Payment CANCELLED');
        }
    }

}