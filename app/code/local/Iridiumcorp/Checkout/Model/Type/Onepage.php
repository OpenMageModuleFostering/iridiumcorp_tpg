<?php

class Iridiumcorp_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
	/**
     * Create an order
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveOrder()
    {
    	$nVersion = Mage::getModel('tpg/direct')->getVersion();
    	
    	if($nVersion >= 1600)
    	{
    		$this->validate();
	        $isNewCustomer = false;
	        switch ($this->getCheckoutMethod())
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
	        $service->submitAll();
	
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
	
	        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
	            ->setLastSuccessQuoteId($this->getQuote()->getId())
	            ->clearHelperData();
	
	        $order = $service->getOrder();
	        if ($order)
	        {
	            Mage::dispatchEvent('checkout_type_onepage_save_order_after',
	                array('order'=>$order, 'quote'=>$this->getQuote()));
	
	            /**
	             * a flag to set that there will be redirect to third party after confirmation
	             * eg: paypal standard ipn
	             */
	            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
	            /**
	             * we only want to send to customer about new order when there is no redirect to third party
	             */
	            if (!$redirectUrl && $order->getCanSendNewEmailFlag())
	            {
	                try
	                {
	                    $order->sendNewOrderEmail();
	                }
	                catch (Exception $e)
	                {
	                    Mage::logException($e);
	                }
	            }
	
	            // add order information to the session
	            $this->_checkoutSession->setLastOrderId($order->getId())
	                ->setRedirectUrl($redirectUrl)
	                ->setLastRealOrderId($order->getIncrementId());
	
	            // as well a billing agreement can be created
	            $agreement = $order->getPayment()->getBillingAgreement();
	            if ($agreement)
	            {
	                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
	            }
	        }
	
	        // add recurring profiles information to the session
	        $profiles = $service->getRecurringPaymentProfiles();
	        if ($profiles)
	        {
	            $ids = array();
	            foreach ($profiles as $profile)
	            {
	                $ids[] = $profile->getId();
	            }
	            $this->_checkoutSession->setLastRecurringProfileIds($ids);
	            // TODO: send recurring profile emails
	        }
	
	        Mage::dispatchEvent(
	            'checkout_submit_all_after',
	            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
	        );
    	}
    	else if($nVersion >= 1410)
    	{
    		// logic for version 1.4.1.0 and above
	    	$this->validate();
	        $isNewCustomer = false;
	        
	        switch ($this->getCheckoutMethod())
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
        	$redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
        	
	    	if(!$redirectUrl ||
	    		$this->getQuote()->getPayment()->getMethodInstance()->getCode() != 'tpg')
	    	{
	            $service->submitAll();
	        }
	
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
	        
	        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            	->setLastSuccessQuoteId($this->getQuote()->getId());
            	#->clearHelperData();
         	
	    	$order = $service->getOrder();
	        if ($order)
	        {
	            Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));
	
	            /**
	             * a flag to set that there will be redirect to third party after confirmation
	             * eg: paypal standard ipn
	             */
	            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
	            /**
	             * we only want to send to customer about new order when there is no redirect to third party
	             */
	            if(!$redirectUrl)
	            {
	                try
	                {
	                    $order->sendNewOrderEmail();
	                }
	                catch (Exception $e)
	                {
	                    Mage::logException($e);
	                }
	            }
				
	            // add order information to the session
	            $this->_checkoutSession->setLastOrderId($order->getId())
	                ->setRedirectUrl($redirectUrl)
	                ->setLastRealOrderId($order->getIncrementId());
	
	            // as well a billing agreement can be created
	            $agreement = $order->getPayment()->getBillingAgreement();
	            if ($agreement)
	            {
	                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
	            }
	        }
	        
	        // add recurring profiles information to the session
	        $profiles = $service->getRecurringPaymentProfiles();
	        if ($profiles)
	        {
	            $ids = array();
	            foreach($profiles as $profile)
	            {
	                $ids[] = $profile->getId();
	            }
	            $this->_checkoutSession->setLastRecurringProfileIds($ids);
	            // TODO: send recurring profile emails
	        }
    	}
    	else if($nVersion == 1400 || $nVersion == 1401)
    	{
    		// logic for version below 1.4.0.1 and below
	    	$this->validateOrder();
	        $billing = $this->getQuote()->getBillingAddress();
	        
	        if (!$this->getQuote()->isVirtual())
	        {
	            $shipping = $this->getQuote()->getShippingAddress();
	        }
	        
	        switch ($this->getQuote()->getCheckoutMethod())
	        {
		        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
		            if (!$this->getQuote()->isAllowedGuestCheckout())
		            {
		                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
		            }
		            $this->getQuote()->setCustomerId(null)
		                ->setCustomerEmail($billing->getEmail())
		                ->setCustomerIsGuest(true)
		                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
		            break;
		
		        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
		            $customer = Mage::getModel('customer/customer');
		
		            $customerBilling = $billing->exportCustomerAddress();
		            $customer->addAddress($customerBilling);
		
		            if (!$this->getQuote()->isVirtual() &&
		            	!$shipping->getSameAsBilling())
		            {
		                $customerShipping = $shipping->exportCustomerAddress();
		                $customer->addAddress($customerShipping);
		            }
		
		            if ($this->getQuote()->getCustomerDob() &&
		            	!$billing->getCustomerDob())
		            {
		                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
		            }
		
		            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
		
		            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
		            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
		
		            $this->getQuote()->setCustomer($customer);
		            Mage::log(time());
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
	
	        $this->getQuote()->reserveOrderId();
	        $convertQuote = Mage::getModel('sales/convert_quote');
	        // @var $convertQuote Mage_Sales_Model_Convert_Quote
	        if ($this->getQuote()->isVirtual())
	        {
	            $order = $convertQuote->addressToOrder($billing);
	        }
	        else
	        {
	            $order = $convertQuote->addressToOrder($shipping);
	        }
	        // @var $order Mage_Sales_Model_Order
	        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));
	
	        if (!$this->getQuote()->isVirtual())
	        {
	            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
	        }
	
	        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));
	
	        foreach ($this->getQuote()->getAllItems() as $item)
	        {
	            $orderItem = $convertQuote->itemToOrderItem($item);
	            if ($item->getParentItem())
	            {
	                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
	            }
	            $order->addItem($orderItem);
	        }
	
	        // We can use configuration data for declare new order status
	        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
			// check again, if customer exists
	        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER)
	        {
	            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId()))
	            {
	                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
	            }
	        }
	
	        // clear 3dSecure session variables
	        Mage::getSingleton('checkout/session')->setThreedsecurerequired(null)
	        										->setMd(null)
	        										->setPares(null)
	        										->setAcsurl(null);
	
	        $order->place();
	
	        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER)
	        {
	            $customer->save();
	            $customerBillingId = $customerBilling->getId();
	            if (!$this->getQuote()->isVirtual())
	            {
	                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
	                $customer->setDefaultShipping($customerShippingId);
	            }
	            $customer->setDefaultBilling($customerBillingId);
	            $customer->save();
	
	            $this->getQuote()->setCustomerId($customer->getId());
	
	            $order->setCustomerId($customer->getId());
	            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);
	
	            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
	            if (!$this->getQuote()->isVirtual())
	            {
	                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
	            }
	
	            if ($customer->isConfirmationRequired())
	            {
	                $customer->sendNewAccountEmail('confirmation');
	            }
	            else
	            {
	                $customer->sendNewAccountEmail();
	            }
	        }
	
	        /**
	         * a flag to set that there will be redirect to third party after confirmation
	         * eg: paypal standard ipn
	         */
	        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
	        if(!$redirectUrl)
	        {
	            $order->setEmailSent(true);
	        }
	
	        if(!$redirectUrl ||
	        	$this->getQuote()->getPayment()->getMethodInstance()->getCode() != 'tpg')
	        {
	        	$order->save();
	        }
	
	        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));
	
	
	        /**
	         * need to have some logic to set order as new status to make sure order is not finished yet
	         * quote will be still active when we send the customer to paypal
	         */
	
	        $orderId = $order->getIncrementId();
	        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
	        $this->getCheckout()->setLastOrderId($order->getId());
	        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
	        $this->getCheckout()->setRedirectUrl($redirectUrl);
	
	        /**
	         * we only want to send to customer about new order when there is no redirect to third party
	         */
	        if(!$redirectUrl)
	        {
	            $order->sendNewOrderEmail();
	        }
	
	        if ($this->getQuote()->getCheckoutMethod(true) == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER &&
	        	!Mage::getSingleton('customer/session')->isLoggedIn())
	      	{
	            /**
	             * we need to save quote here to have it saved with Customer Id.
	             * so when loginById() executes checkout/session method loadCustomerQuote
	             * it would not create new quotes and merge it with old one.
	             */
	            $this->getQuote()->save();
	            if ($customer->isConfirmationRequired())
	            {
	                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
	                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
	                ));
	            }
	            else
	            {
	                Mage::getSingleton('customer/session')->loginById($customer->getId());
	            }
	        }
	
	        //Setting this one more time like control flag that we haves saved order
	        //Must be checkout on success page to show it or not.
	        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());
	
	        /*
	         * Fix for v1.4.1.0 and above - need to comment the below lines
	         */
	        //$this->getQuote()->setIsActive(false);
	        //$this->getQuote()->save();
    	}
    	else if($nVersion == 1324 || $nVersion == 1330)
    	{
    		$this->validateOrder();
	        $billing = $this->getQuote()->getBillingAddress();
	        
	        if (!$this->getQuote()->isVirtual())
	        {
	            $shipping = $this->getQuote()->getShippingAddress();
	        }
	        
	        switch ($this->getQuote()->getCheckoutMethod())
	        {
		        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
		            if (!$this->getQuote()->isAllowedGuestCheckout())
		            {
		                Mage::throwException(Mage::helper('checkout')->__('Sorry, guest checkout is not enabled. Please try again or contact store owner.'));
		            }
		            $this->getQuote()->setCustomerId(null)
		                ->setCustomerEmail($billing->getEmail())
		                ->setCustomerIsGuest(true)
		                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
		            break;
		
		        case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
		            $customer = Mage::getModel('customer/customer');
		
		            $customerBilling = $billing->exportCustomerAddress();
		            $customer->addAddress($customerBilling);
		
		            if (!$this->getQuote()->isVirtual() &&
		            	!$shipping->getSameAsBilling())
		            {
		                $customerShipping = $shipping->exportCustomerAddress();
		                $customer->addAddress($customerShipping);
		            }
		
		            if ($this->getQuote()->getCustomerDob() &&
		            	!$billing->getCustomerDob())
		            {
		                $billing->setCustomerDob($this->getQuote()->getCustomerDob());
		            }
		            
	        		if ($this->getQuote()->getCustomerTaxvat() && !$billing->getCustomerTaxvat())
		            {
		                $billing->setCustomerTaxvat($this->getQuote()->getCustomerTaxvat());
		            }
		
		            Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
		
		            $customer->setPassword($customer->decryptPassword($this->getQuote()->getPasswordHash()));
		            $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
		
		            $this->getQuote()->setCustomer($customer);
		            Mage::log(time());
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
	
	        $this->getQuote()->reserveOrderId();
	        $convertQuote = Mage::getModel('sales/convert_quote');
	        // @var $convertQuote Mage_Sales_Model_Convert_Quote
	        if ($this->getQuote()->isVirtual())
	        {
	            $order = $convertQuote->addressToOrder($billing);
	        }
	        else
	        {
	            $order = $convertQuote->addressToOrder($shipping);
	        }
	        // @var $order Mage_Sales_Model_Order
	        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));
	
	        if (!$this->getQuote()->isVirtual())
	        {
	            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
	        }
	
	        $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));
	
	        foreach ($this->getQuote()->getAllItems() as $item)
	        {
	            $orderItem = $convertQuote->itemToOrderItem($item);
	            if ($item->getParentItem())
	            {
	                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
	            }
	            $order->addItem($orderItem);
	        }
	
	        // We can use configuration data for declare new order status
	        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
			// check again, if customer exists
	        if ($this->getQuote()->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER)
	        {
	            if ($this->_customerEmailExists($customer->getEmail(), Mage::app()->getWebsite()->getId()))
	            {
	                Mage::throwException(Mage::helper('checkout')->__('There is already a customer registered using this email address'));
	            }
	        }
	
	        // clear 3dSecure session variables
	        Mage::getSingleton('checkout/session')->setThreedsecurerequired(null);
	        Mage::getSingleton('checkout/session')->setMd(null);
	        Mage::getSingleton('checkout/session')->setPares(null);
	        Mage::getSingleton('checkout/session')->setAcsurl(null);
	
	        $order->place();
	
	        if ($this->getQuote()->getCheckoutMethod()==Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER)
	        {
	            $customer->save();
	            $customerBillingId = $customerBilling->getId();
	            if (!$this->getQuote()->isVirtual())
	            {
	                $customerShippingId = isset($customerShipping) ? $customerShipping->getId() : $customerBillingId;
	                $customer->setDefaultShipping($customerShippingId);
	            }
	            $customer->setDefaultBilling($customerBillingId);
	            $customer->save();
	
	            $this->getQuote()->setCustomerId($customer->getId());
	
	            $order->setCustomerId($customer->getId());
	            Mage::helper('core')->copyFieldset('customer_account', 'to_order', $customer, $order);
	
	            $billing->setCustomerId($customer->getId())->setCustomerAddressId($customerBillingId);
	            if (!$this->getQuote()->isVirtual())
	            {
	                $shipping->setCustomerId($customer->getId())->setCustomerAddressId($customerShippingId);
	            }
	
	            if ($customer->isConfirmationRequired())
	            {
	                $customer->sendNewAccountEmail('confirmation');
	            }
	            else
	            {
	                $customer->sendNewAccountEmail();
	            }
	        }
	
	        /**
	         * a flag to set that there will be redirect to third party after confirmation
	         * eg: paypal standard ipn
	         */
	        $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
	        if(!$redirectUrl)
	        {
	            $order->setEmailSent(true);
	        }
	
	        if(!$redirectUrl ||
	        	$this->getQuote()->getPayment()->getMethodInstance()->getCode() != 'tpg')
	        {
	        	$order->save();
	        }
	
	        Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));
	
	
	        /**
	         * need to have somelogic to set order as new status to make sure order is not finished yet
	         * quote will be still active when we send the customer to paypal
	         */
	
	        $orderId = $order->getIncrementId();
	        $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
	        $this->getCheckout()->setLastOrderId($order->getId());
	        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());
	        $this->getCheckout()->setRedirectUrl($redirectUrl);
	
	        /**
	         * we only want to send to customer about new order when there is no redirect to third party
	         */
	        if(!$redirectUrl)
	        {
	            $order->sendNewOrderEmail();
	        }
	
	        if ($this->getQuote()->getCheckoutMethod(true) == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER &&
	        	!Mage::getSingleton('customer/session')->isLoggedIn())
	      	{
	            /**
	             * we need to save quote here to have it saved with Customer Id.
	             * so when loginById() executes checkout/session method loadCustomerQuote
	             * it would not create new quotes and merge it with old one.
	             */
	            $this->getQuote()->save();
	            if ($customer->isConfirmationRequired())
	            {
	                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
	                    Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
	                ));
	            }
	            else
	            {
	                Mage::getSingleton('customer/session')->loginById($customer->getId());
	            }
	        }
	
	        //Setting this one more time like control flag that we haves saved order
	        //Must be checkout on success page to show it or not.
	        $this->getCheckout()->setLastSuccessQuoteId($this->getQuote()->getId());
	
	        $this->getQuote()->setIsActive(false);
	        $this->getQuote()->save();
    	}

        return $this;
    }
    
    /**
     * Create an order for a Direct (API) 3D Secure enabled payment on the callback
     *
     * @param unknown_type $pares
     * @param unknown_type $md
     * @return unknown
     */
	public function saveOrderAfter3dSecure($pares, $md)
   	{
   		$nVersion = Mage::getModel('tpg/direct')->getVersion();
   		$orderId;
   		
   		if($nVersion >= 1410)
   		{
   			$orderId = Mage::getSingleton('checkout/session')->getTpgOrderId();
   			$_order = Mage::getModel('sales/order')->load($orderId);

	        if(!$_order->getId())
	        {
	            Mage::throwException('Could not load order.');
	        }
			
	        Mage::getSingleton('checkout/session')->setThreedsecurerequired(true)
	        										->setMd($md)
	        										->setPares($pares);
	        
	        $method = Mage::getSingleton('checkout/session')->getRedirectionmethod();
        	$_order->getPayment()->getMethodInstance()->{$method}($_order->getPayment(), $pares, $md);
        	
        	if ($_order->getFailedThreed() !== true &&
        		$_order->getPayment()->getMethodInstance()->getCode() == 'tpg' &&
        		$_order->getStatus() != 'pending')
       		{
            	$order_status = Mage::getStoreConfig('payment/tpg/order_status',  Mage::app()->getStore()->getId());
            	$_order->addStatusToHistory($order_status);
            	$_order->setStatus($order_status);
	        }

	        $_order->save();
	
	        Mage::getSingleton('checkout/session')->setThreedsecurerequired(null)
													->setMd(null)
													->setPareq(null)
													->setAcsurl(null)
													->setTpgOrderId(null);
   		}
   		else if($nVersion == 1400 || $nVersion == 1401 || $nVersion == 1324 || $nVersion == 1330)
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
	
	        $this->getQuote()->reserveOrderId();
	        $convertQuote = Mage::getModel('sales/convert_quote');
	        // @var $convertQuote Mage_Sales_Model_Convert_Quote
	        if ($this->getQuote()->isVirtual())
	        {
	            $order = $convertQuote->addressToOrder($billing);
	        }
	        else
	        {
	            $order = $convertQuote->addressToOrder($shipping);
	        }
	        /* @var $order Mage_Sales_Model_Order */
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
	
	        Mage::getSingleton('checkout/session')->setThreedsecurerequired(true)
	        										->setMd($md)
	        										->setPares($pares);
	
	        $order->place();
	
	        if ( $order->getPayment()->getMethodInstance()->getCode() == 'tpg' &&
	        	$order->getStatus() != 'pending' )
	        {
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
	       // $this->getCheckout()->setRedirectUrl($redirectUrl);
	
	        /**
	         * we only want to send to customer about new order when there is no redirect to third party
	         */
	        /*if(!$redirectUrl){
	            $order->sendNewOrderEmail();
	        }*/
	
	        if ($this->getQuote()->getCheckoutMethod() == 'register')
	        {
	            Mage::getSingleton('customer/session')->loginById($customer->getId());
	        }
   		}
        return $this;
    }
    
    /**
     * Create an order for a Hosted Payment Form/Transparent Redirect payment on the callback
     *
     * @param unknown_type $boIsHostedPaymentAction
     * @param unknown_type $szStatusCode
     * @param unknown_type $szMessage
     * @param unknown_type $szPreviousStatusCode
     * @param unknown_type $szPreviousMessage
     * @param unknown_type $szOrderID
     * @return unknown
     */
	public function saveOrderAfterRedirectedPaymentAction($boIsHostedPaymentAction, $szStatusCode, $szMessage, $szPreviousStatusCode, $szPreviousMessage, $szOrderID, $szCrossReference)
   	{
   		$nVersion = Mage::getModel('tpg/direct')->getVersion();
   		
   		if($nVersion >= 1410)
   		{
   			$_order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getTpgOrderId());

	        if(!$_order->getId())
	        {
	            Mage::throwException('Could not load order.');
	        }
			
	        Mage::getSingleton('checkout/session')->setRedirectedpayment(true)
	        										->setIshostedpayment($boIsHostedPaymentAction)
	        										->setStatuscode($szStatusCode)
	        										->setMessage($szMessage)
	        										->setPreviousstatuscode($szPreviousStatusCode)
	        										->setPreviousmessage($szPreviousMessage)
	        										->setOrderid($szOrderID);
	        
	        $method = Mage::getSingleton('checkout/session')->getRedirectionmethod();
        	$_order->getPayment()->getMethodInstance()->{$method}($_order->getPayment(), $boIsHostedPaymentAction, $szStatusCode, $szMessage, $szPreviousStatusCode, $szPreviousMessage, $szOrderID, $szCrossReference);
        	
        	if ($_order->getFailedThreed() !== true &&
        		$_order->getPayment()->getMethodInstance()->getCode() == 'tpg' &&
        		$_order->getStatus() != 'pending')
       		{
            	$order_status = Mage::getStoreConfig('payment/tpg/order_status',  Mage::app()->getStore()->getId());
            	$_order->addStatusToHistory($order_status);
            	$_order->setStatus($order_status);
	        }
	
	        $_order->save();
	
	        Mage::getSingleton('checkout/session')->setRedirectedpayment(null)
	                                             	->setIshostedpayment(null)
	                                             	->setStatuscode(null)
	                                             	->setMessage(null)
	                                             	->setPreviousstatuscode(null)
	                                             	->setPreviousmessage(null)
	                                             	->setOrderid(null)
	                                             	->setTpgOrderId(null);
   		}
   		else if($nVersion == 1400 || $nVersion == 1401 || $nVersion == 1324 || $nVersion == 1330)
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
	
	        $this->getQuote()->reserveOrderId();
	        $convertQuote = Mage::getModel('sales/convert_quote');
	        // @var $convertQuote Mage_Sales_Model_Convert_Quote
	        if ($this->getQuote()->isVirtual())
	        {
	            $order = $convertQuote->addressToOrder($billing);
	        }
	        else
	        {
	            $order = $convertQuote->addressToOrder($shipping);
	        }
	        /* @var $order Mage_Sales_Model_Order */
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
	
	        Mage::getSingleton('checkout/session')->setRedirectedpayment(true)
	        										->setIshostedpayment($boIsHostedPaymentAction)
	        										->setStatuscode($szStatusCode)
	        										->setMessage($szMessage)
	        										->setPreviousstatuscode($szPreviousStatusCode)
	        										->setPreviousmessage($szPreviousMessage)
	        										->setOrderid($szOrderID);
	
	        $order->place();
	
	        if ( $order->getPayment()->getMethodInstance()->getCode() == 'tpg' &&
	        	$order->getStatus() != 'pending' )
	        {
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
	
	        /**
	         * we only want to send to customer about new order when there is no redirect to third party
	         */
	        //if(!$redirectUrl){
	            $order->sendNewOrderEmail();
	        //}
	
	        if ($this->getQuote()->getCheckoutMethod()=='register')
	        {
	            Mage::getSingleton('customer/session')->loginById($customer->getId());
	        }
   		}

        return $this;
    }
}