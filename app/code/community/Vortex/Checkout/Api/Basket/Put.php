<?php
class Vortex_Checkout_Api_Basket_Put implements Vortex_Api_EndpointInterface
{
    /**
     * @param $body
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return array
     * @throws Vortex_Api_Exception_BadRequest
     */
    public function execute($body, Zend_Controller_Request_Http $request, Zend_Controller_Response_Http $response)
    {
        $products = array_key_exists('items', $body) ? $body['items'] : [];
        $basket = $this->getBasket();
        foreach ($products as $product) {
            if ($product['qty'] <= 0) {
                $basket->removeItem($product['item_id']);
            } else {
                $item = $basket->getItemById($product['item_id']);

                // Check first item child if item is configurable
                if ($item->getHasChildren()) {
                    $itemChildren = $item->getChildren();
                    $itemToCheck = $itemChildren[0];
                } else {
                    $itemToCheck = $item;
                }

                if ($product['qty'] > $itemToCheck->getProduct()->getStockItem()->getQty()) {
                    throw new Vortex_Api_Exception_BadRequest($this->getHelper()->__('Insufficient quantity'), 400);
                }

                $item->setQty($product['qty']);
            }
        }

        if (array_key_exists('discount_code', $body)) {
            $this->getBasket()->setCouponCode($body['discount_code']);
            $this->getBasket()->collectTotals();

            if ($this->getBasket()->getGrandTotal() == 0 && $this->getBasket()->getIsClickAndCollect()) {
                $this->getBasketAddressService()->setBillingAddressAsShippingAddress();
            }
        }

        if (array_key_exists('remove_discount', $body) && $body['remove_discount']) {
            $this->getBasket()->setCouponCode('');
            $this->getBasket()->collectTotals();
        }

        $this->getCart()->save();

        return $this->getBasketMapper()->map($basket);
    }

    /**
     * @return Vortex_Checkout_Service_Basket_Address
     */
    protected function getBasketAddressService()
    {
        return new Vortex_Checkout_Service_Basket_Address(
            Mage::getSingleton('checkout/type_onepage')
        );
    }

    /**
     * @return Vortex_Checkout_Mapper_Basket
     */
    protected function getBasketMapper()
    {
        return new Vortex_Checkout_Mapper_Basket(
            new Vortex_Checkout_Mapper_Basket_Items(
                $this->getCoreHelper(),
                $this->getProductImageHelper(),
                $this->getConfigurationHelper()
            ),
            new Vortex_Checkout_Mapper_Basket_Address(),
            $this->getCoreHelper(),
            $this->getCheckoutSession()
        );
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Mage_Checkout_Model_Cart
     */
    protected function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getBasket()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
    }

    /**
     * @return Mage_Catalog_Helper_Image
     */
    protected function getProductImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    /**
     * @return Mage_Core_Helper_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('core');
    }

    /**
     * @return Mage_Catalog_Helper_Product_Configuration
     */
    protected function getConfigurationHelper()
    {
        return Mage::helper('catalog/product_configuration');
    }

    /**
     * @return Vortex_Checkout_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('vortex_checkout');
    }
}