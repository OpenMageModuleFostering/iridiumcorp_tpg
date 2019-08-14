<?php

include ("Tpg/ThePaymentGateway/PaymentSystem.php");
include_once ("Tpg/PaymentFormHelper.php");
include ("Tpg/ISOCurrencies.php");
include ("Tpg/ISOCountries.php");

// GLOBAL 3D Secure authorization result variables:
$m_sz3DSecureMessage;
$m_bo3DSecureError;

class Iridiumcorp_Tpg_Model_Direct extends Mage_Payment_Model_Method_Abstract
{
	/**
  	* unique internal payment method identifier
  	*
  	* @var string [a-z0-9_]
  	*/
	protected $_code = 'tpg';
 	protected $_formBlockType = 'tpg/form'; 
 	protected $_infoBlockType = 'tpg/info';

	protected $_isGateway = true;
	protected $_canAuthorize = false;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = false;
	protected $_canVoid = false;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;
	protected $_canSaveCc = false;
	
	/** 
	* Assign data to info model instance 
	*  
	* @param   mixed $data 
	* @return  Mage_Payment_Model_Info 
	*/  
 	public function assignData($data)  
	{
	    if (!($data instanceof Varien_Object))
	    {
	        $data = new Varien_Object($data);
	    }
	    
	    $info = $this->getInfoInstance();
	    
	    $info->setCcOwner($data->getCcOwner())
	        ->setCcLast4(substr($data->getCcNumber(), -4))
	        ->setCcNumber($data->getCcNumber())
	        ->setCcCid($data->getCcCid())
	        ->setCcExpMonth($data->getCcExpMonth())
	        ->setCcExpYear($data->getCcExpYear())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
            ->setCcSsIssue($data->getCcSsIssue());

	    return $this;
	}
	
	/**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
	public function validate()
	{
		// NOTE : cancel out the core Magento validator functionality, the payment gateway will overtake this task
		
		return $this;
	}
	
	/**
     * Authorize - core Mage pre-authorization functionality
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
	public function authorize(Varien_Object $payment, $amount)
	{
		$error = false;
		
		Mage::throwException('This payment module only allow capture payments.');
		
		return $this;
	}
	
	/**
     * Capture payment - immediate settlement payments
     *
     * @param   Varien_Object $payment
     * @return  Mage_Payment_Model_Abstract
     */
	public function capture(Varien_Object $payment, $amount)
	{
		$error = false;
		
		// reset the global 3D Secure variables 
		$GLOBALS['m_bo3DSecureError'] = true;
		$GLOBALS['m_sz3DSecureMessage'] = false;
			
		if($amount <= 0)
		{
			Mage::throwException(Mage::helper('paygate')->__('Invalid amount for authorization.'));
		}
		else
		{
			// TODO : wrap this content with try/catch and log any exception
			// check if the payment is a 3D Secure
			if(Mage::getSingleton('checkout/session')->getSecure3d())
			{
				// this is a 3D Secure payment
				$this->_run3DSecureTransaction($payment, $amount, Mage::getSingleton('checkout/session')->getPares(), Mage::getSingleton('checkout/session')->getMd());
				 
				// reset the property to default non 3DS
				Mage::getSingleton('checkout/session')->setSecure3d(false);
			}
			else
			{
				// reset the 3DS properties for a fresh payment request
				Mage::getSingleton('checkout/session')
					->setMd(null)
        			->setAcsurl(null)
		  		   	->setPareq(null)
		  		   	->setTermurl(null);
				
		  		// run a fresh payment request
		  		switch ($this->getConfigData('mode'))
		  		{
		  			case Iridiumcorp_Tpg_Model_Source_PaymentMode::PAYMENT_MODE_DIRECT_API:
		  				$this->_runTransaction($payment, $amount);
		  				break;
		  			case Iridiumcorp_Tpg_Model_Source_PaymentMode::PAYMENT_MODE_HOSTED_PAYMENT_FORM:
		  				$this->_redirectTransaction($payment, $amount);
		  				break;
		  			case Iridiumcorp_Tpg_Model_Source_PaymentMode::PAYMENT_MODE_TRANSPARENT_REDIRECT:
		  				$this->_transparentRedirectTransaction($payment, $amount);
		  				break;
		  			default:
		  				Mage::throwException('Invalid payment type: '.$this->getConfigData('mode'));
		  				break;
		  		}
			}
		}
		
		return $this;
	}
	
	/**
	 * Processing the transaction using the direct integration
	 * 
	 * @param   Varien_Object $orderPayment
	 * @param   $amount
	 * @return  void
	 */
	public function _runTransaction(Varien_Object $payment, $amount)
	{
		$MerchantID = $this->getConfigData('merchantid');
		$Password = $this->getConfigData('password');
		$SecretKey = $this->getConfigData('secretkey');
		// assign payment form field values to variables
		$order = $payment->getOrder();
		$szOrderID = $payment->getOrder()->increment_id;
		$szOrderDescription = '';
		$szCardName = $payment->getCcOwner();
		$szCardNumber = $payment->getCcNumber();
		$szIssueNumber = $payment->getCcSsIssue();
		$szCV2 = $payment->getCcCid();
		$nCurrencyCode;
		$szCurrencyShort = $order->getOrderCurrency()->getCurrencyCode();
		// address details
		$billingAddress = $order->getBillingAddress();
		$szAddress1 = $billingAddress->getStreet1();
		$szAddress2 = $billingAddress->getStreet2();
		$szAddress3 = $billingAddress->getStreet3();
		$szAddress4 = $billingAddress->getStreet4();
		$szCity = $billingAddress->getCity();
		$szState = $billingAddress->getRegion();
		$szPostCode = $billingAddress->getPostcode();
		$szISO2CountryCode = $billingAddress->getCountry();
		$nCountryCode;
		$szEmailAddress = $billingAddress->getCustomerEmail();
		$szPhoneNumber = $billingAddress->getTelephone();
		$nDecimalAmount;

		$PaymentProcessorFullDomain = $this->_getPaymentProcessorFullDomain();
		
		$rgeplRequestGatewayEntryPointList = new IRC_RequestGatewayEntryPointList();
		
		$rgeplRequestGatewayEntryPointList->add("https://gw1.".$PaymentProcessorFullDomain, 100, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw2.".$PaymentProcessorFullDomain, 200, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw3.".$PaymentProcessorFullDomain, 300, 2);
		
		$maMerchantAuthentication = new IRC_MerchantAuthentication($MerchantID, $Password);
		
		$mdMessageDetails = new IRC_MessageDetails("SALE");

		$boEchoCardType = new IRC_NullableBool(true);
		$boEchoAmountReceived = new IRC_NullableBool(true);
		$boEchoAVSCheckResult = new IRC_NullableBool(true);
		$boEchoCV2CheckResult = new IRC_NullableBool(true);
		$boThreeDSecureOverridePolicy = new IRC_NullableBool(true);
		$nDuplicateDelay = new IRC_NullableInt(60);
		$tcTransactionControl = new IRC_TransactionControl($boEchoCardType, $boEchoAVSCheckResult, $boEchoCV2CheckResult, $boEchoAmountReceived, $nDuplicateDelay, "",  "", $boThreeDSecureOverridePolicy,  "",  null, null);
		
		$iclISOCurrencyList = IRC_ISOCurrencies::getISOCurrencyList();
		
		if ($szCurrencyShort != '' &&
			$iclISOCurrencyList->getISOCurrency($szCurrencyShort, $icISOCurrency))
		{
			$nCurrencyCode = new IRC_NullableInt($icISOCurrency->getISOCode());
		}
		
		$power = pow(10, $icISOCurrency->getExponent());
		$nDecimalAmount = $amount * $power;
		$nAmount = new IRC_NullableInt($nDecimalAmount);
		
		$nDeviceCategory = new IRC_NullableInt(0);
		$tdsbdThreeDSecureBrowserDetails = new IRC_ThreeDSecureBrowserDetails($nDeviceCategory, "*/*",  $_SERVER["HTTP_USER_AGENT"]);;
		$tdTransactionDetails = new IRC_TransactionDetails($mdMessageDetails, $nAmount, $nCurrencyCode, $szOrderID, $szOrderDescription, $tcTransactionControl, $tdsbdThreeDSecureBrowserDetails);

		$nExpiryDateMonth = null;
		if($payment->getCcExpMonth() != '')
		{
			$nExpiryDateMonth = new IRC_NullableInt($payment->getCcExpMonth());
		}
		
		$nExpiryDateYear = null;
		if($payment->getCcExpYear() != '')
		{
			$nExpiryDateYear = new IRC_NullableInt($payment->getCcExpYear());
		}
		
		$nStartDateMonth = null;
		if($payment->getCcSsStartMonth() != '')
		{
			$nStartDateMonth = new IRC_NullableInt($payment->getCcSsStartMonth());
		}
		
		$nStartDateYear = null;
		if($payment->getCcSsStartYear() != '')
		{
			$nStartDateYear = new IRC_NullableInt($payment->getCcSsStartYear());
		}
		
		$edExpiryDate = new IRC_ExpiryDate($nExpiryDateMonth, $nExpiryDateYear);
		$sdStartDate = new IRC_StartDate($nStartDateMonth, $nStartDateYear);
		$cdCardDetails = new IRC_CardDetails($szCardName, $szCardNumber, $edExpiryDate, $sdStartDate, $szIssueNumber, $szCV2);

		$nCountryCode = null;
		$iclISOCountryList = IRC_ISOCountries::getISOCountryList();
		$szCountryShort = $this->_getISO3Code($szISO2CountryCode);
		if($iclISOCountryList->getISOCountry($szCountryShort, $icISOCountry))
		{
			$nCountryCode = new IRC_NullableInt($icISOCountry->getISOCode());
		}
		
		if($szAddress1 == null)
		{
			$szAddress1 = '';
		}
		if($szAddress2 == null)
		{
			$szAddress2 = '';
		}
		if($szAddress2 == null)
		{
			$szAddress2 = '';
		}
		if($szAddress2 == null)
		{
			$szAddress2 = '';
		}

		$adBillingAddress = new IRC_AddressDetails($szAddress1, $szAddress2, $szAddress3, $szAddress4, $szCity, $szState, $szPostCode, $nCountryCode);
		$cdCustomerDetails = new IRC_CustomerDetails($adBillingAddress, $szEmailAddress, $szPhoneNumber, $_SERVER["REMOTE_ADDR"]);
		$cdtCardDetailsTransaction = new IRC_CardDetailsTransaction($rgeplRequestGatewayEntryPointList, 1, null, $maMerchantAuthentication, $tdTransactionDetails, $cdCardDetails, $cdCustomerDetails, "Some data to be passed out");
		$boTransactionProcessed = $cdtCardDetailsTransaction->processTransaction($cdtrCardDetailsTransactionResult, $todTransactionOutputData);
		
		if ($boTransactionProcessed == false)
		{
			// could not communicate with the payment gateway
			$szLogMessage = "Couldn't complete transaction. Details: ".print_r($cdtrCardDetailsTransactionResult, 1)." ".print_r($todTransactionOutputData, 1);  //"Couldn't communicate with payment gateway.";
			Mage::log($szLogMessage);
    		Mage::throwException(Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_261);
		}
		else
		{
			$boError = true;
			$szLogMessage = "Transaction could not be completed for OrderID: ".$szOrderID.". Result details: ";
			$szNotificationMessage = 'Payment Processor Response: '.$cdtrCardDetailsTransactionResult->getMessage();
			
			switch ($cdtrCardDetailsTransactionResult->getStatusCode())
			{
				case 0:
					// status code of 0 - means transaction successful
					$boError = false;
					$szLogMessage = "Transaction successfully completed for OrderID: ".$szOrderID.". Result object details: ";
					Mage::getSingleton('core/session')->addSuccess($szNotificationMessage);
					break;
				case 3:
					// status code of 3 - means 3D Secure authentication required
					$boError = false;
					$szLogMessage = "3D Secure Authentication required for OrderID: ".$szOrderID.". Result object details: ";
					
					$szPaReq = $todTransactionOutputData->getThreeDSecureOutputData()->getPaREQ();
					$szCrossReference = $todTransactionOutputData->getCrossReference();
					$szACSURL = $todTransactionOutputData->getThreeDSecureOutputData()->getACSURL();
					
					Mage::getSingleton('checkout/session')->setMd($szCrossReference)
	        												->setAcsurl($szACSURL)
			  		   										->setPareq($szPaReq)
			  		   										->setTermurl('tpg/payment/callback3d');
					break;
				case 5:
					// status code of 5 - means transaction declined
					break;
				case 20:
					// status code of 20 - means duplicate transaction
					$szPreviousTransactionMessage = $cdtrCardDetailsTransactionResult->getPreviousTransactionResult()->getMessage();
					$szLogMessage = "Duplicate transaction for OrderID: ".$szOrderID.". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction: ".$szPreviousTransactionMessage.". Result object details: ";
					$szNotificationMessage = $szNotificationMessage.". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction - Previous Transaction Response: ".$szPreviousTransactionMessage;
					
					if ($cdtrCardDetailsTransactionResult->getPreviousTransactionResult()->getStatusCode()->getValue() == 0)
					{
						$boError = false;
						Mage::getSingleton('core/session')->addSuccess($szNotificationMessage);
					}
					break;
				case 30:
					// status code of 30 - means an error occurred 
					$szLogMessage = "Transaction could not be completed for OrderID: ".$szOrderID.". Error message: ".$cdtrCardDetailsTransactionResult->getMessage();
					if ($cdtrCardDetailsTransactionResult->getErrorMessages()->getCount() > 0)
					{
						$szLogMessage = $szLogMessage.".";
	
						for ($LoopIndex = 0; $LoopIndex < $cdtrCardDetailsTransactionResult->getErrorMessages()->getCount(); $LoopIndex++)
						{
							$szLogMessage = $szLogMessage.$cdtrCardDetailsTransactionResult->getErrorMessages()->getAt($LoopIndex).";";
						}
						$szLogMessage = $szLogMessage." ";
					}
					$szLogMessage = $szLogMessage.' Result object details: ';
					break;
				default:
					// unhandled status code
					break;
			}
			
			$szLogMessage = $szLogMessage.print_r($cdtrCardDetailsTransactionResult, 1);
			Mage::log($szLogMessage);
			
			// if the payment was not sucessful notify the customer with a message
			if($boError == true)
			{
				Mage::throwException($szNotificationMessage);
			}
		}
	}
	
	/**
	 * Processing the transaction using the hosted payment form integration 
	 *
	 * @param Varien_Object $payment
	 * @param unknown_type $amount
	 */
	public function _redirectTransaction(Varien_Object $payment, $amount)
	{
		$szMerchantID = $this->getConfigData('merchantid');
		$szPassword = $this->getConfigData('password');
		$szCallbackURL = Mage::getUrl('tpg/payment/callbackhostedpayment');
		$szPreSharedKey = $this->getConfigData('presharedkey');
		$hmHashMethod = $this->getConfigData('hashmethod');
		$boCV2Mandatory = 'false';
		$boAddress1Mandatory = 'false';
		$boCityMandatory = 'false';
		$boPostCodeMandatory = 'false';
		$boStateMandatory = 'false';
		$boCountryMandatory = 'false';
		$rdmResultdeliveryMethod = $this->getConfigData('resultdeliverymethod');
		$szServerResultURL = $this->getConfigData('serverresulturl');
		$boPaymentFormDisplaysResult = 'false';
		
		$order = $payment->getOrder();
		$billingAddress = $order->getBillingAddress();
		$iclISOCurrencyList = IRC_ISOCurrencies::getISOCurrencyList();
		$iclISOCountryList = IRC_ISOCountries::getISOCountryList();
		
		$szCurrencyShort = $order->getOrderCurrency()->getCurrencyCode();
		if ($szCurrencyShort != '' &&
			$iclISOCurrencyList->getISOCurrency($szCurrencyShort, $icISOCurrency))
		{
			$nCurrencyCode = $icISOCurrency->getISOCode();
		}
		
		$power = pow(10, $icISOCurrency->getExponent());
		$nAmount = $amount * $power;
		
		$szISO2CountryCode = $billingAddress->getCountry();
		$szCountryShort = $this->_getISO3Code($szISO2CountryCode);
		if($iclISOCountryList->getISOCountry($szCountryShort, $icISOCountry))
		{
			$nCountryCode = $icISOCountry->getISOCode();
		}
		
		$szOrderID = $payment->getOrder()->increment_id;
		$szTransactionType = 'SALE';
		//date time with 2008-12-01 14:12:00 +01:00 format
		$szTransactionDateTime = date('Y-m-d H:i:s P');
		$szOrderDescription = '';
		
		$szCustomerName = $billingAddress->getfirstname();
		if($billingAddress->getfirstname())
		{
			$szCustomerName = $szCustomerName.' '.$billingAddress->getlastname();
		}
		$szAddress1 = $billingAddress->getStreet1();
		$szAddress2 = $billingAddress->getStreet2();
		$szAddress3 = $billingAddress->getStreet3();
		$szAddress4 = $billingAddress->getStreet4();
		$szCity = $billingAddress->getCity();
		$szState = $billingAddress->getRegion();
		$szPostCode = $billingAddress->getPostcode();
		
		if($this->getConfigData('cv2mandatory'))
		{
			$boCV2Mandatory = 'true';
		}
		if($this->getConfigData('address1mandatory'))
		{
			$boAddress1Mandatory = 'true';
		}
		if($this->getConfigData('citymandatory'))
		{
			$boCityMandatory = 'true';
		}
		if($this->getConfigData('postcodemandatory'))
		{
			$boPostCodeMandatory = 'true';
		}
		if($this->getConfigData('statemandatory'))
		{
			$boStateMandatory = 'true';
		}
		if($this->getConfigData('countrymandatory'))
		{
			$boCountryMandatory = 'true';
		}
		if($this->getConfigData('paymentformdisplaysresult'))
		{
			$boPaymentFormDisplaysResult = 'true';
		}

		$szHashDigest = IRC_PaymentFormHelper::calculateHashDigest($szMerchantID, $szPassword, $hmHashMethod, $szPreSharedKey, $nAmount, $nCurrencyCode, $szOrderID, $szTransactionType, $szTransactionDateTime, $szCallbackURL, $szOrderDescription, $szCustomerName, $szAddress1, $szAddress2, $szAddress3, $szAddress4, $szCity, $szState, $szPostCode, $nCountryCode, $boCV2Mandatory, $boAddress1Mandatory, $boCityMandatory, $boPostCodeMandatory, $boStateMandatory, $boCountryMandatory, $rdmResultdeliveryMethod, $szServerResultURL, $boPaymentFormDisplaysResult);

		Mage::getSingleton('checkout/session')->setHashdigest($szHashDigest)
	        									->setMerchantid($szMerchantID)
			  		   							->setAmount($nAmount)
			  		   							->setCurrencycode($nCurrencyCode)
			  		   							->setOrderid($szOrderID)
			  		   							->setTransactiontype($szTransactionType)
			  		   							->setTransactiondatetime($szTransactionDateTime)
			  		   							->setCallbackurl($szCallbackURL)
			  		   							->setOrderdescription($szOrderDescription)
			  		   							->setCustomername($szCustomerName)
			  		   							->setAddress1($szAddress1)
			  		   							->setAddress2($szAddress2)
			  		   							->setAddress3($szAddress3)
			  		   							->setAddress4($szAddress4)
			  		   							->setCity($szCity)
			  		   							->setState($szState)
			  		   							->setPostcode($szPostCode)
			  		   							->setCountrycode($nCountryCode)
			  		   							->setCv2mandatory($boCV2Mandatory)
			  		   							->setAddress1mandatory($boAddress1Mandatory)
			  		   							->setCitymandatory($boCityMandatory)
			  		   							->setPostcodemandatory($boPostCodeMandatory)
			  		   							->setStatemandatory($boStateMandatory)
			  		   							->setCountrymandatory($boCountryMandatory)
			  		   							->setResultdeliverymethod($rdmResultdeliveryMethod)
			  		   							->setServerresulturl($szServerResultURL)
			  		   							->setPaymentformdisplaysresult($boPaymentFormDisplaysResult);
	}
	
	/**
	 * Processing the transaction using the transparent redirect integration
	 *
	 * @param Varien_Object $payment
	 * @param unknown_type $amount
	 */
	public function _transparentRedirectTransaction(Varien_Object $payment, $amount)
	{
		$szMerchantID = $this->getConfigData('merchantid');
		$szPassword = $this->getConfigData('password');
		$szPreSharedKey = $this->getConfigData('presharedkey');
		$hmHashMethod = $this->getConfigData('hashmethod');
		$szCallbackURL = Mage::getUrl('tpg/payment/callbacktransparentredirect');
		$order = $payment->getOrder();
		$billingAddress = $order->getBillingAddress();
		$iclISOCurrencyList = IRC_ISOCurrencies::getISOCurrencyList();
		$iclISOCountryList = IRC_ISOCountries::getISOCountryList();
		$szStartDateMonth = '';
		$szStartDateYear = '';
		
		$szCurrencyShort = $order->getOrderCurrency()->getCurrencyCode();
		if ($szCurrencyShort != '' &&
			$iclISOCurrencyList->getISOCurrency($szCurrencyShort, $icISOCurrency))
		{
			$nCurrencyCode = $icISOCurrency->getISOCode();
		}
		
		$power = pow(10, $icISOCurrency->getExponent());
		$nAmount = $amount * $power;
		
		$szOrderID = $payment->getOrder()->increment_id;
		$szTransactionType = 'SALE';
		//date time with 2008-12-01 14:12:00 +01:00 format
		$szTransactionDateTime = date('Y-m-d H:i:s P');
		$szOrderDescription = '';
		
		$szAddress1 = $billingAddress->getStreet1();
		$szAddress2 = $billingAddress->getStreet2();
		$szAddress3 = $billingAddress->getStreet3();
		$szAddress4 = $billingAddress->getStreet4();
		$szCity = $billingAddress->getCity();
		$szState = $billingAddress->getRegion();
		$szPostCode = $billingAddress->getPostcode();
		$szISO2CountryCode = $billingAddress->getCountry();
		$szCountryShort = $this->_getISO3Code($szISO2CountryCode);
		if($iclISOCountryList->getISOCountry($szCountryShort, $icISOCountry))
		{
			$nCountryCode = $icISOCountry->getISOCode();
		}
		
		$szCardName = $payment->getCcOwner();
		$szCardNumber = $payment->getCcNumber();
		$szExpiryDateMonth = $payment->getCcExpMonth();
		$szExpiryDateYear = $payment->getCcExpYear();
		if($payment->getCcSsStartMonth() != '')
		{
			$szStartDateMonth = $payment->getCcSsStartMonth();
		}
		if($payment->getCcSsStartYear() != '')
		{
			$szStartDateYear = $payment->getCcSsStartYear();
		}
		$szIssueNumber = $payment->getCcSsIssue();
		$szCV2 = $payment->getCcCid();
		
		$szHashDigest = IRC_PaymentFormHelper::calculateTransparentRedirectHashDigest($szMerchantID, $szPassword, $hmHashMethod, $szPreSharedKey, $nAmount, $nCurrencyCode, $szOrderID, $szTransactionType, $szTransactionDateTime, $szCallbackURL, $szOrderDescription);
		
		Mage::getSingleton('checkout/session')->setHashdigest($szHashDigest)
	        									->setMerchantid($szMerchantID)
			  		   							->setAmount($nAmount)
			  		   							->setCurrencycode($nCurrencyCode)
			  		   							->setOrderid($szOrderID)
			  		   							->setTransactiontype($szTransactionType)
			  		   							->setTransactiondatetime($szTransactionDateTime)
			  		   							->setCallbackurl($szCallbackURL)
			  		   							->setOrderdescription($szOrderDescription)
			  		   							->setAddress1($szAddress1)
			  		   							->setAddress2($szAddress2)
			  		   							->setAddress3($szAddress3)
			  		   							->setAddress4($szAddress4)
			  		   							->setCity($szCity)
			  		   							->setState($szState)
			  		   							->setPostcode($szPostCode)
			  		   							->setCountrycode($nCountryCode)
			  		   							->setCardname($szCardName)
			  		   							->setCardnumber($szCardNumber)
			  		   							->setExpirydatemonth($szExpiryDateMonth)
			  		   							->setExpirydateyear($szExpiryDateYear)
			  		   							->setStartdatemonth($szStartDateMonth)
			  		   							->setStartdateyear($szStartDateYear)
			  		   							->setIssuenumber($szIssueNumber)
			  		   							->setCv2($szCV2);
	}
	
	/**
	 * Processing the 3D Secure transaction
	 *
	 * @param Varien_Object $payment
	 * @param int $amount
	 * @param string $szPaRes
	 * @param string $szMD
	 */
	public function _run3DSecureTransaction(Varien_Object $payment, $amount, $szPaRes, $szMD)
	{
		$szOrderID = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		
		$MerchantID = $this->getConfigData('merchantid');
		$Password = $this->getConfigData('password');
		$SecretKey = $this->getConfigData('secretkey');
		
		$PaymentProcessorFullDomain = $this->_getPaymentProcessorFullDomain();
		$rgeplRequestGatewayEntryPointList = new IRC_RequestGatewayEntryPointList();
		$rgeplRequestGatewayEntryPointList->add("https://gw1.".$PaymentProcessorFullDomain, 100, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw2.".$PaymentProcessorFullDomain, 200, 2);
		$rgeplRequestGatewayEntryPointList->add("https://gw3.".$PaymentProcessorFullDomain, 300, 2);

		$maMerchantAuthentication = new IRC_MerchantAuthentication($MerchantID, $Password);
		$tdsidThreeDSecureInputData = new IRC_ThreeDSecureInputData($szMD, $szPaRes);
		
		$tdsaThreeDSecureAuthentication = new IRC_ThreeDSecureAuthentication($rgeplRequestGatewayEntryPointList, 1, null, $maMerchantAuthentication, $tdsidThreeDSecureInputData, "Some data to be passed out");
		$boTransactionProcessed = $tdsaThreeDSecureAuthentication->processTransaction($tdsarThreeDSecureAuthenticationResult, $todTransactionOutputData);
		
		if ($boTransactionProcessed == false)
		{
			// could not communicate with the payment gateway
			//PaymentFormHelper::reportTransactionResults($CrossReference, 30, $Message, null);
			$szLogMessage = Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_431;
			Mage::log($szLogMessage);
			
    		$GLOBALS['m_bo3DSecureError'] = true;
    		$GLOBALS['m_sz3DSecureMessage'] = Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_431;
		}
		else
		{
			$szLogMessage = "3D Secure transaction could not be completed for OrderID: ".$szOrderID.". Result object details: ";
			$GLOBALS['m_bo3DSecureError'] = true;
			$GLOBALS['m_sz3DSecureMessage'] = "Payment Processor Response: ".$tdsarThreeDSecureAuthenticationResult->getMessage();
			
			switch ($tdsarThreeDSecureAuthenticationResult->getStatusCode())
			{
				case 0:
					// status code of 0 - means transaction successful
					//PaymentFormHelper::reportTransactionResults($CrossReference, $tdsarThreeDSecureAuthenticationResult->getStatusCode(), $tdsarThreeDSecureAuthenticationResult->getMessage(), $todTransactionOutputData->getCrossReference());
					$GLOBALS['m_bo3DSecureError'] = false;
					$szLogMessage = "3D Secure transaction successfully completed for OrderID: ".$szOrderID.". Result object details: ";
					break;
				case 5:
					// status code of 5 - means transaction declined
					break;
				case 20:
					// status code of 20 - means duplicate transaction
					$szPreviousTransactionMessage = $tdsarThreeDSecureAuthenticationResult->getPreviousTransactionResult()->getMessage();
					$szLogMessage = "Duplicate transaction for OrderID: ".$szOrderID.". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction: ".$szPreviousTransactionMessage.". Result object details: ";
					
					if ($tdsarThreeDSecureAuthenticationResult->getPreviousTransactionResult()->getStatusCode()->getValue() == 0)
					{
						$GLOBALS['m_bo3DSecureError'] = false;
						$GLOBALS['m_sz3DSecureMessage'] = $GLOBALS['m_sz3DSecureMessage'].". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction are - ".$szPreviousTransactionMessage;
					}
					break;
				case 30:
					// status code of 30 - means an error occurred 
					$szLogMessage = "3D Secure transaction could not be completed for OrderID: ".$szOrderID.". Error message: ".$tdsarThreeDSecureAuthenticationResult->getMessage();
					if ($tdsarThreeDSecureAuthenticationResult->getErrorMessages()->getCount() > 0)
					{
						$szLogMessage = $szLogMessage.".";
	
						for ($LoopIndex = 0; $LoopIndex < $tdsarThreeDSecureAuthenticationResult->getErrorMessages()->getCount(); $LoopIndex++)
						{
							$szLogMessage = $szLogMessage.$tdsarThreeDSecureAuthenticationResult->getErrorMessages()->getAt($LoopIndex).";";
						}
						$szLogMessage = $szLogMessage." ";
					}
					break;
				default:
					// unhandled status code  
					break;
			}
			
			// log 3DS payment result
			$szLogMessage = $szLogMessage.print_r($tdsarThreeDSecureAuthenticationResult, 1);
			Mage::log($szLogMessage);
		}
	}
	
	/**
	 * Building the request object for 3D Secure payment
	 *
	 * @param string $PaRes
	 * @param string $MD
	 * @return Iridiumcorp_Tpg_Model_Request
	 */
	public function _build3DSecureRequest($PaRes, $MD)
	{
		$request = Mage::getModel('tpg/request')
					->setPares($PaRes)
					->setMd($MD);
		
		return $request;
	}
	
	/**
	 * Override the core Mage function to get the URL to be redirected from the Onepage
	 *
	 * @return string
	 */
	public function getOrderPlaceRedirectUrl()
    {
    	$result = false;
       	$session = Mage::getSingleton('checkout/session');
       	
       	// get the correct url for redirection
        if ($session->getAcsurl() &&
         	$session->getMd() &&
          	$session->getPareq())
        {
        	$result = Mage::getUrl('tpg/payment/threedsecure');
        }
        else if ($session->getHashdigest())
        {
        	$result = Mage::getUrl('tpg/payment/redirect');
        }
        
        return $result;
    }
	
    /**
     * Get the correct payment processor domain
     *
     * @return string
     */
    private function _getPaymentProcessorFullDomain()
    {
    	$szPaymentProcessorFullDomain;
    	
    	// get the stored config setting
    	$szPaymentProcessorDomain = $this->getConfigData('paymentprocessordomain');
		$szPaymentProcessorPort = $this->getConfigData('paymentprocessorport');
    	
    	if ($szPaymentProcessorPort == '443')
		{
			$szPaymentProcessorFullDomain = $szPaymentProcessorDomain."/";
		}
		else
		{
			$szPaymentProcessorFullDomain = $szPaymentProcessorDomain.":".$szPaymentProcessorPort."/";
		}
		
		return $szPaymentProcessorFullDomain;
    }
    
    /**
     * Get the country ISO3 code from the ISO2 code
     *
     * @param ISO2Code
     * @return string
     */
	private function _getISO3Code($szISO2Code)
	{
		$szISO3Code;
		$collection;
		$boFound = false;
		$nCount = 1;
		$item;
		
		$collection = Mage::getModel('directory/country_api')->items();
		
		while ($boFound == false &&
				$nCount < count($collection))
		{
			$item = $collection[$nCount];
			if($item['iso2_code'] == $szISO2Code)
			{
				$boFound = true;
				$szISO3Code = $item['iso3_code'];
			}
			$nCount++;
		}
		
		return $szISO3Code;
	}
}
