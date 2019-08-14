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

    /**
     * Finalising a 3D Secure transaction payment
     *
     * @param string $PaRes
     * @param string $MD
     * @return unknown
     */
    public function saveOrderAfter3DSecure($PaRes, $MD)
    {
	 	$this->validateOrder();
        $billing = $this->getQuote()->getBillingAddress();
        
        if (!$this->getQuote()->isVirtual())
        {
            $shipping = $this->getQuote()->getShippingAddress();
        }

        switch ($this->getQuote()->getCheckoutMethod())
        {
	        case 'guest':
	            $this->getQuote()->setCustomerEmail($billing->getEmail())
	                ->setCustomerIsGuest(true)
	                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
	            break;
	        default:
	            $customer = Mage::getSingleton('customer/session')->getCustomer();
	
	            if (!$billing->getCustomerId() ||
	            	$billing->getSaveInAddressBook())
	            {
	                $customerBilling = $billing->exportCustomerAddress();
	                $customer->addAddress($customerBilling);
	            }
	            if (!$this->getQuote()->isVirtual() &&
	                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) ||
	                (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook())))
	          	{
	                $customerShipping = $shipping->exportCustomerAddress();
	                $customer->addAddress($customerShipping);
	            }
	            
	            $customer->setSavedFromQuote(true);
	            $customer->save();
	
	            $changed = false;
	            if (isset($customerBilling) &&
	            	!$customer->getDefaultBilling())
	            {
	                $customer->setDefaultBilling($customerBilling->getId());
	                $changed = true;
	            }
	            
	            if (!$this->getQuote()->isVirtual() &&
	            	isset($customerBilling) &&
	            	!$customer->getDefaultShipping() &&
	            	$shipping->getSameAsBilling())
	            {
	                $customer->setDefaultShipping($customerBilling->getId());
	                $changed = true;
	            }
	            elseif (!$this->getQuote()->isVirtual() &&
	            		isset($customerShipping) &&
	            		!$customer->getDefaultShipping())
	            {
	                $customer->setDefaultShipping($customerShipping->getId());
	                $changed = true;
	            }
	
	            if ($changed)
	            {
	                $customer->save();
	            }
        }

        // make sure that the order id is not incremented for the second phase of a 3D Secure transaction 
        //$this->getQuote()->reserveOrderId();
        
        $convertQuote = Mage::getModel('sales/convert_quote');
        if ($this->getQuote()->isVirtual())
        {
            $order = $convertQuote->addressToOrder($billing);
        }
        else
        {
            $order = $convertQuote->addressToOrder($shipping);
        }
        
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual())
        {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item)
        {
            $order->addItem($convertQuote->itemToOrderItem($item));
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));

        Mage::getSingleton('checkout/session')->setSecure3d(true);
        Mage::getSingleton('checkout/session')->setMd($MD);
        Mage::getSingleton('checkout/session')->setPares($PaRes);

        $order->place();

        if ($order->getPayment()->getMethodInstance()->getCode()=="tpg" &&
        	$order->getStatus() != 'pending' )
        {
			#set it as pending
			#get the xml configuration ($this)
			#$order_status = 'pending';

			$order_status = Mage::getStoreConfig('payment/tpg/order_status',  Mage::app()->getStore()->getId());

			$order->addStatusToHistory($order_status);
			$order->setStatus($order_status);
        }

        $order->save();

        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));

        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());

        if ($this->getQuote()->getCheckoutMethod()=='register')
        {
            Mage::getSingleton('customer/session')->loginById($customer->getId());
        }

        return $this;
    }
    
    /**
     * Completing the order on the Mage system after a Hosted Payment (save the updated order)
     *
     */
    public function saveOrderAfterHostedPayment()
    {
    	$this->validateOrder();
    	
    	// get the order from the billing or the shipping details
    	$billing = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual())
        {
            $shipping = $this->getQuote()->getShippingAddress();
        }
        
    	switch ($this->getQuote()->getCheckoutMethod())
        {
	        case 'guest':
	            $this->getQuote()->setCustomerEmail($billing->getEmail())
	                ->setCustomerIsGuest(true)
	                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
	            break;
	        default:
	            $customer = Mage::getSingleton('customer/session')->getCustomer();
	
	            if (!$billing->getCustomerId() ||
	            	$billing->getSaveInAddressBook())
	            {
	                $customerBilling = $billing->exportCustomerAddress();
	                $customer->addAddress($customerBilling);
	            }
	            if (!$this->getQuote()->isVirtual() &&
	                ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook())))
	          	{
	                $customerShipping = $shipping->exportCustomerAddress();
	                $customer->addAddress($customerShipping);
	            }
	            
	            $customer->setSavedFromQuote(true);
	            $customer->save();
	
	            $changed = false;
	            if (isset($customerBilling) &&
	            	!$customer->getDefaultBilling())
	            {
	                $customer->setDefaultBilling($customerBilling->getId());
	                $changed = true;
	            }
	            
	            if (!$this->getQuote()->isVirtual() &&
	            	isset($customerBilling) &&
	            	!$customer->getDefaultShipping() &&
	            	$shipping->getSameAsBilling())
	            {
	                $customer->setDefaultShipping($customerBilling->getId());
	                $changed = true;
	            }
	            elseif (!$this->getQuote()->isVirtual() &&
	            		isset($customerShipping) &&
	            		!$customer->getDefaultShipping())
	            {
	                $customer->setDefaultShipping($customerShipping->getId());
	                $changed = true;
	            }
	
	            if ($changed)
	            {
	                $customer->save();
	            }
        }
        
        $convertQuote = Mage::getModel('sales/convert_quote');
    	if ($this->getQuote()->isVirtual())
        {
            $order = $convertQuote->addressToOrder($billing);
        }
        else
        {
            $order = $convertQuote->addressToOrder($shipping);
        }
        
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));

        if (!$this->getQuote()->isVirtual())
        {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

        foreach ($this->getQuote()->getAllItems() as $item)
        {
            $order->addItem($convertQuote->itemToOrderItem($item));
        }

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
        
        $order->save();
        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));
        
        $this->getQuote()->setIsActive(false);
        $this->getQuote()->save();

        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId())
				            ->setLastOrderId($order->getId())
				            ->setLastRealOrderId($order->getIncrementId());
            
        return $this;
    }
}
