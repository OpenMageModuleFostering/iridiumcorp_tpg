<?php

class Iridiumcorp_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
	/**
     * Create order based on checkout type. Create customer if necessary. Overrided for necessary redirection
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveOrder()
    {
        $this->validate();
        $isNewCustomer = false;
        
        switch ($this->getCheckoutMehod())
        {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $order = $service->submit();

        if ($isNewCustomer)
        {
            try
            {
                $this->_involveNewCustomer();
            }
            catch (Exception $e)
            {
                Mage::logException($e);
            }
        }
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        /**
         *  a flag to set for redirecting to a third party URL (ie: for 3D Secure authorization)
         * 
         */
        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        
        /**
         * we only want to send email to customer about new order when there is no redirect to third party
         */
        if(!$redirectUrl)
        {
            try
            {
                $order->sendNewOrderEmail();
            }
            catch (Exception $exc)
            {
                Mage::logException($exc);
            }
        }

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
				            ->setLastOrderId($order->getId())
				            ->setLastRealOrderId($order->getIncrementId())
				            ->setRedirectUrl($redirectUrl)
				            ->setLastSuccessQuoteId($this->getQuote()->getId());
            
        return $this;
    }
}
