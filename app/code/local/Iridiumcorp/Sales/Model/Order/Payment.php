<?php

class Iridiumcorp_Sales_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
	/**
     * Capture payment
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function capture($invoice)
    {
    	$nVersion = Mage::getModel('tpg/direct')->getVersion();
    	
    	if($nVersion >= 1411 || $nVersion == 1410)
    	{
    		if (is_null($invoice))
    		{
	            $invoice = $this->_invoice();
	            $this->setCreatedInvoice($invoice);
	            return $this; // @see Mage_Sales_Model_Order_Invoice::capture()
	        }
	        $amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
	        $order = $this->getOrder();
	
	        // prepare parent transaction and its amount
	        $paidWorkaround = 0;
	        if (!$invoice->wasPayCalled())
	        {
	            $paidWorkaround = (float)$amountToCapture;
	        }
	        $this->_isCaptureFinal($paidWorkaround);
	
	        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $this->getAuthorizationTransaction());
	
	        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));
	
	        /**
	         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
	         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
	         */
	        if ($invoice->getTransactionId())
	        {
	            $this->getMethodInstance()->setStore($order->getStoreId())->fetchTransactionInfo($this, $invoice->getTransactionId());
	        }
	        $status = true;
	        if (!$invoice->getIsPaid() && !$this->getIsTransactionPending())
	        {
	            // attempt to capture: this can trigger "is_transaction_pending"
	            $this->getMethodInstance()->setStore($order->getStoreId())->capture($this, $amountToCapture);
	
	            $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
	
	            if ($this->getIsTransactionPending())
	            {
	                $message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amountToCapture));
	                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
	                if ($this->getIsFraudDetected())
	                {
	                    $status = 'fraud';
	                }
	                $invoice->setIsPaid(false);
	            }
	            else
	            { // normal online capture: invoice is marked as "paid"
	                $message = Mage::helper('sales')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
	                $state = Mage_Sales_Model_Order::STATE_PROCESSING;
	                $invoice->setIsPaid(true);
	                $this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
	            }
	            if ($order->isNominal())
	            {
	                $message = $this->_prependMessage(Mage::helper('sales')->__('Nominal order registered.'));
	            }
	            else
	            {
	                $message = $this->_prependMessage($message);
	                $message = $this->_appendTransactionToMessage($transaction, $message);
	            }
	            $order->setState($state, $status, $message);
	            $this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated
	            return $this;
	        }
	        Mage::throwException(Mage::helper('sales')->__('The transaction "%s" cannot be captured yet.', $invoice->getTransactionId()));
    	}
    	if($nVersion == 1400 || $nVersion == 1401)
    	{
    		if (is_null($invoice))
    		{
	            $invoice = $this->_invoice();
	            $this->setCreatedInvoice($invoice);
	            return $this; // @see Mage_Sales_Model_Order_Invoice::capture()
	        }
	        $amountToCapture = $this->_formatAmount($invoice->getBaseGrandTotal());
	
	        $paidWorkaround = 0;
	        if (!$invoice->wasPayCalled())
	        {
	            $paidWorkaround = (float)$amountToCapture;
	        }
	        $this->_isCaptureFinal($paidWorkaround);
	        $baseTransaction = false;
	        if ($invoice->getTransactionId())
	        {
	            $baseTransaction = $this->_lookupTransaction($invoice->getTransactionId());
	        }
	        else
	        {
	            $baseTransaction = $this->getAuthorizationTransaction();
	        }
	        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $baseTransaction);
	
	        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));

	        $this->getMethodInstance()
	            ->setStore($this->getOrder()->getStoreId())
	            ->capture($this, $amountToCapture);

	        // update transactions, set order state (order will close itself if required)
	        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
	        /**
	         * Check if payment transaction is under pending state.
	         * Gateway can accept peyment transaction but mark it as pending.
	         * We need hold such kind of orders
	         */
	        if ($this->getIsTransactionPending())
	        {
	            $message = Mage::helper('sales')->__('Amount of %s pending approval on gateway.', $this->_formatPrice($amountToCapture));
	            $message = $this->_prependMessage($message);
	            $message = $this->_appendTransactionToMessage($transaction, $message);
	            $status  = $this->getTransactionPendingStatus() ? $this->getTransactionPendingStatus() : true;
	            $this->getOrder()->setState(Mage_Sales_Model_Order::STATE_HOLDED, $status, $message);
	            $invoice->setIsPaid(false);
	        }
	        else
	        {
	            $this->_updateTotals(array('base_amount_paid_online' => $amountToCapture));
	            $message = Mage::helper('sales')->__('Captured amount of %s online.', $this->_formatPrice($amountToCapture));
	            $message = $this->_prependMessage($message);
	            $message = $this->_appendTransactionToMessage($transaction, $message);
	            $this->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);
	            $invoice->setIsPaid(true);
	        }

	        $this->getMethodInstance()->processInvoice($invoice, $this); // should be deprecated
	        return $this;
    	}
    	if($nVersion == 1324 || $nVersion == 1330)
    	{
	        if (is_null($invoice))
	        {
	            $invoice = $this->_invoice();
	        }
	
	        Mage::dispatchEvent('sales_order_payment_capture', array('payment' => $this, 'invoice' => $invoice));
	
	        $this->getMethodInstance()
	            ->setStore($this->getOrder()->getStoreId())
	            ->capture($this, sprintf('%.2f', $invoice->getBaseGrandTotal()));
	      	if($this->getIsInvoicePaid())
	      	{
	      		$invoice->setIsPaid(true);
	      	}
	        $this->getMethodInstance()->processInvoice($invoice, $this);
	        
	        return $this;
    	}
    }
	
    /**
     * Authorize or authorize and capture payment on gateway, if applicable
     * This method is supposed to be called only when order is placed
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function place()
    {
    	$nVersion = Mage::getModel('tpg/direct')->getVersion();
    	$paymentAction = Mage::getModel('tpg/direct')->getConfigData('payment_action');

    	if($paymentAction == Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE)
		{
			$ircOrderStatus = "irc_paid";
		}
		else if($paymentAction == Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE)
		{
			$ircOrderStatus = "irc_preauth";
		}
		else 
		{
			$ircOrderStatus = null;
		}
		    	
    	if($nVersion >= 1411 || $nVersion == 1410 || $nVersion == 1401 || $nVersion == 1400)
    	{
	        Mage::dispatchEvent('sales_order_payment_place_start', array('payment' => $this));
	        $order = $this->getOrder();
	
	        $this->setAmountOrdered($order->getTotalDue());
	        $this->setBaseAmountOrdered($order->getBaseTotalDue());
	        $this->setShippingAmount($order->getShippingAmount());
	        $this->setBaseShippingAmount($order->getBaseShippingAmount());
	
	        $methodInstance = $this->getMethodInstance();
	        $methodInstance->setStore($order->getStoreId());
	
	        $orderState = Mage_Sales_Model_Order::STATE_NEW;
	        $orderStatus= false;
	
	        $stateObject = new Varien_Object();
	
	        /**
	         * Do order payment validation on payment method level
	         */
	        $methodInstance->validate();
	        $action = $methodInstance->getConfigPaymentAction();
	        if ($action)
	        {
	            if ($methodInstance->isInitializeNeeded())
	            {
	                /**
	                 * For method initialization we have to use original config value for payment action
	                 */
	                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
	            }
	            else
	            {
	                $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
	                switch ($action)
	                {
	                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
	                        $this->_authorize(true, $order->getBaseTotalDue()); // base amount will be set inside
	                        $this->setAmountAuthorized($order->getTotalDue());
	                        break;
	                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
	                        $this->setAmountAuthorized($order->getTotalDue());
	                        $this->setBaseAmountAuthorized($order->getBaseTotalDue());
	                        $this->capture(null);
	                        break;
	                    default:
	                        break;
	                }
	            }
	        }

	        if($nVersion >= 1411 || $nVersion == 1410)
	        {
	        	$this->_createBillingAgreement();
	        	$orderStateHelper = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
	        }
	        else 
	        {
	        	$orderStateHelper = Mage_Sales_Model_Order::STATE_HOLDED;
	        }

	        $orderIsNotified = null;
	        if ($stateObject->getState() && $stateObject->getStatus())
			{
	            $orderState      = $stateObject->getState();
	            if($ircOrderStatus == null)
	            {
	            	$orderStatus     = $stateObject->getStatus();
	            }
	            else 
	            {
	            	$orderStatus = $ircOrderStatus;
	            }
	            $orderIsNotified = $stateObject->getIsNotified();
	        }
			else if($order->getIsThreeDSecurePending())
			{
				$orderState = 'pending_payment';
	            $orderStatus = 'irc_pending_threed_secure';
	            $message = '3D Secure authentication need to be completed';
	            $orderIsNotified = false;
			}
			else if($order->getIsHostedPaymentPending())
			{
				$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
				$orderStateHelper = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
				$orderState = 'pending_payment';
	            $orderStatus = 'irc_pending_hosted_payment';
	            $message = 'Hosted Payment need to be completed';
	            $orderIsNotified = false;
			}
			else
			{
				if($ircOrderStatus == null)
				{
		            $orderStatus = $methodInstance->getConfigData('order_status');
		            if (!$orderStatus || $order->getIsVirtual())
					{
		                $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
		            }
				}
				else 
				{
					$orderStatus = $ircOrderStatus;
				}
	        }
		
	        $isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
	        //$message = $order->getCustomerNote();
	        if(!$order->getIsThreeDSecurePending() &&
	        	!$order->getIsHostedPaymentPending())
	        {
	        	$message = $order->getCustomerNote();
	        }

	        // add message if order was put into review during authorization or capture
	        //if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)$orderState
	        if ($order->getState() == $orderStateHelper)
			{
	            if ($message)
				{
	                $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
	            }
	            
	            if($nVersion >= 1401 || $nVersion == 1400)
	            {
		            $order->setHoldBeforeState($orderState);
	            	$order->setHoldBeforeStatus($orderStatus);
	            }
	        }
	        
	        // add message to history if order state already declared
	        elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message))
			{
	            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
	        }
	        // set order state
	        elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message)
			{
	            $order->setState($orderState, $orderStatus, $message, $isCustomerNotified);
	        }

	        Mage::dispatchEvent('sales_order_payment_place_end', array('payment' => $this));
	
	        return $this;
    	}
    	if($nVersion == 1324 || $nVersion == 1330)
    	{
    		Mage::dispatchEvent('sales_order_payment_place_start', array('payment' => $this));

	        $this->setAmountOrdered($this->getOrder()->getTotalDue());
	        $this->setBaseAmountOrdered($this->getOrder()->getBaseTotalDue());
	
	        $this->setShippingAmount($this->getOrder()->getShippingAmount());
	        $this->setBaseShippingAmount($this->getOrder()->getBaseShippingAmount());
	
	        $methodInstance = $this->getMethodInstance()->setStore($this->getOrder()->getStoreId());
	
	        $orderState = Mage_Sales_Model_Order::STATE_NEW;
	        $orderStatus= false;
	
	        $stateObject = new Varien_Object();
	
	        /**
	         * validating payment method again
	         */
	        $methodInstance->validate();
	        if ($action = $methodInstance->getConfigData('payment_action')) {
	            /**
	             * Run action declared for payment method in configuration
	             */
	
	            if ($methodInstance->isInitializeNeeded()) {
	                $methodInstance->initialize($action, $stateObject);
	            } else {
	                switch ($action) {
	                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
	                    case Mage_Paypal_Model_Api_Abstract::PAYMENT_TYPE_AUTH:
	                        $methodInstance->authorize($this, $this->getOrder()->getBaseTotalDue());
	
	                        $this->setAmountAuthorized($this->getOrder()->getTotalDue());
	                        $this->setBaseAmountAuthorized($this->getOrder()->getBaseTotalDue());
	
	                        $orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
	                        break;
	                    case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
	                    case Mage_Paypal_Model_Api_Abstract::PAYMENT_TYPE_SALE:
	                        $invoice = $this->_invoice();
	
	                        $this->setAmountAuthorized($this->getOrder()->getTotalDue());
	                        $this->setBaseAmountAuthorized($this->getOrder()->getBaseTotalDue());
	
	                        $orderState = $this->getOrder()->getIsVirtual()
	                            ? Mage_Sales_Model_Order::STATE_COMPLETE
	                            : Mage_Sales_Model_Order::STATE_PROCESSING;
	                        break;
	                    default:
	                        break;
	                }
	            }
	        }
	
	        $orderIsNotified = null;
	        if ($stateObject->getState() && $stateObject->getStatus())
	        {
	            $orderState      = $stateObject->getState();
	            if($ircOrderStatus == null)
	            {
	            	$orderStatus     = $stateObject->getStatus();
	            }
	            else 
	            {
	            	$orderStatus = $ircOrderStatus;
	            }
	            $orderIsNotified = $stateObject->getIsNotified();
	        }
	        else
	        {
	            /*
	             * this flag will set if the order went to as authorization under fraud service for payflowpro
	             */
	            if ($this->getFraudFlag())
	            {
	                $orderStatus = $methodInstance->getConfigData('fraud_order_status');
	                $orderState = Mage_Sales_Model_Order::STATE_HOLDED;
	            }
	            else
	            {
	            	$orderStatus = $methodInstance->getConfigData('order_status');
	            }
	
	            if($ircOrderStatus == null)
	           	{
		            if (!$orderStatus || $this->getOrder()->getIsVirtual())
		            {
		                $orderStatus = $this->getOrder()->getConfig()->getStateDefaultStatus($orderState);
		            }
	            }
	            else 
	            {
	            	$orderStatus = $ircOrderStatus;
	            }
	        }
	
	        $this->getOrder()->setState($orderState);
	        $this->getOrder()->addStatusToHistory(
	            $orderStatus,
	            $this->getOrder()->getCustomerNote(),
	            (null !== $orderIsNotified ? $orderIsNotified : $this->getOrder()->getCustomerNoteNotify())
	        );
	
	        Mage::dispatchEvent('sales_order_payment_place_end', array('payment' => $this));
	
	        return $this;
    	}
    }
    
    /**
     * Create transaction,
     * prepare its insertion into hierarchy and add its information to payment and comments
     *
     * To add transactions and related information,
     * the following information should be set to payment before processing:
     * - transaction_id
     * - is_transaction_closed (optional) - whether transaction should be closed or open (closed by default)
     * - parent_transaction_id (optional)
     * - should_close_parent_transaction (optional) - whether to close parent transaction (closed by default)
     *
     * If the sales document is specified, it will be linked to the transaction as related for future usage.
     * Currently transaction ID is set into the sales object
     * This method writes the added transaction ID into last_trans_id field of the payment object
     *
     * To make sure transaction object won't cause trouble before saving, use $failsafe = true
     *
     * @param string $type
     * @param Mage_Sales_Model_Abstract $salesDocument
     * @param bool $failsafe
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addTransaction($type, $salesDocument = null, $failsafe = false)
    {
    	$model = Mage::getModel('tpg/direct');
    	$nVersion = $model->getVersion();
    	
    	if($nVersion >= 1501)
    	{
	        if ($this->getSkipTransactionCreation())
	        {
	            $this->unsTransactionId();
	            return null;
	        }
    	}

        // look for set transaction ids
        $transactionId = $this->getTransactionId();
        if (null !== $transactionId)
        {
            // set transaction parameters
            $transaction = false;
            if ($this->getOrder()->getId())
            {
                $transaction = $this->_lookupTransaction($transactionId);
            }
            
            if (!$transaction)
            {
                $transaction = Mage::getModel('sales/order_payment_transaction')->setTxnId($transactionId);
            }
            $transaction
                ->setOrderPaymentObject($this)
                ->setTxnType($type)
                ->isFailsafe($failsafe);

            if ($this->hasIsTransactionClosed())
            {
                $transaction->setIsClosed((int)$this->getIsTransactionClosed());
            }

            //set transaction addition information
            if ($this->_transactionAdditionalInfo)
            {
                foreach ($this->_transactionAdditionalInfo as $key => $value)
                {
                    $transaction->setAdditionalInformation($key, $value);
                }
            }

            // link with sales entities
            $this->setLastTransId($transactionId);
            $this->setCreatedTransaction($transaction);
            $this->getOrder()->addRelatedObject($transaction);
            if ($salesDocument && $salesDocument instanceof Mage_Sales_Model_Abstract)
            {
                $salesDocument->setTransactionId($transactionId);
                // TODO: linking transaction with the sales document
            }

            // link with parent transaction
            $parentTransactionId = $this->getParentTransactionId();

            if ($parentTransactionId)
            {
                $transaction->setParentTxnId($parentTransactionId);
                if ($this->getShouldCloseParentTransaction())
                {
                    $parentTransaction = $this->_lookupTransaction($parentTransactionId);
                    if ($parentTransaction)
                    {
                        if (!$parentTransaction->getIsClosed())
                        {
                            $parentTransaction->isFailsafe($failsafe)->close(false);
                        }
                        $this->getOrder()->addRelatedObject($parentTransaction);
                    }
                }
            }
            return $transaction;
        }
    }
}