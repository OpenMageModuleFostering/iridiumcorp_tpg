<?php

require_once "app/code/local/Iridiumcorp/Tpg/Model/Tpg/PaymentFormHelper.php";

/**
 * Standard Checkout Controller
 *
 */
class Iridiumcorp_Tpg_PaymentController extends Mage_Core_Controller_Front_Action
{
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems())
        {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function errorAction()
    {
    	Mage::log('error navigation.');
		//$this->_redirect('checkout/cart');
		$this->_redirect('checkout/onepage/failure');
        #$this->loadLayout();
        #$this->renderLayout();
    }

    /**
     * When a customer cancel payment from paypal.
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        $this->_redirect('checkout/cart');
     }

	/**
     * Action logic for Hosted Payment mode
     *
     */
    public function redirectAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('tpg/redirect')->toHtml());
    }
    
    /**
     * Action logic for 3D Secure redirection
     *
     */
    public function threedsecureAction()
    {
    	$this->getResponse()->setBody($this->getLayout()->createBlock('tpg/threedsecure')->toHtml());
    }
    
    /**
     * Action for handling the reception of the 3D Secure authentication result (PaRes)
     *
     * @return unknown
     */
    public function callback3dAction()
    {
    	$boError = false;
    	$szMessage = '';
    	
    	try
    	{
    		// get the PaRes and MD from the post 
    		$szPaRes = $this->getRequest()->getPost('PaRes');
    		$szMD = $this->getRequest()->getPost('MD');
    		
    		// complete the 3D Secure transaction with the 3D Authorization result
    		Mage::getSingleton('checkout/type_onepage')->saveOrderAfter3DSecure($szPaRes, $szMD);
    	}
    	catch (Exception $exc)
    	{
    		$boError = true;
    		Mage::log('Callback 3DSecure action failed, exception details: '.$exc);
    		
    		if( isset($_SESSION['tpg_message']) )
    		{
    			$szMessage = $_SESSION['tpg_message'];
    			unset($_SESSION['tpg_message']);
    		}
    		else
    		{
				$szMessage = Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_7655;
    		}

    		// report out an fatal error
    		Mage::getSingleton('core/session')->addError($szMessage);
    		$this->_redirect('checkout/onepage/failure');
    	}

    	if (!$boError)
    	{
    		// report out an payment result
    		if($GLOBALS['m_bo3DSecureError'] == 1)
    		{
    			// if the global message is empty report out a general error message
    			if(!$GLOBALS['m_sz3DSecureMessage'])
    			{
    				Mage::getSingleton('core/session')->addError("3DSecure Validation was not successfull, please try again.");
    			}
    			else
    			{
    				Mage::getSingleton('core/session')->addError($GLOBALS['m_sz3DSecureMessage']);
    			}
    			$this->_redirect('checkout/onepage/failure');
    		}
    		else
    		{
		        // set the quote as inactive after back from 3DS Authorization page
		        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
	
		        // send confirmation email to customer
		        $order = Mage::getModel('sales/order');
	
		        $order->load(Mage::getSingleton('checkout/session')->getLastOrderId());
		        if($order->getId())
		        {
		            $order->sendNewOrderEmail();
		        }
	
		        //Mage::getSingleton('checkout/session')->unsQuoteId();
		        if($GLOBALS['m_sz3DSecureMessage'])
		        {
		        	Mage::getSingleton('core/session')->addSuccess($GLOBALS['m_sz3DSecureMessage']);
		        }
		        $this->_redirect('checkout/onepage/success');
    		}
    	}
    }
    
    /**
     * Action for handling the result from the Hosted Payment page
     *
     */
    public function callbackhostedpaymentAction()
    {
    	$error = false;
    	$formVariables = array();
    	$model = Mage::getModel('tpg/direct');
    	
    	try
    	{
    		$hmHashMethod = $model->getConfigData('hashmethod');
			$szPassword = $model->getConfigData('password');
			$szPreSharedKey = $model->getConfigData('presharedkey');
    		
    		$formVariables['HashDigest'] = $this->getRequest()->getPost('HashDigest');
    		$formVariables['MerchantID'] = $this->getRequest()->getPost('MerchantID');
    		$formVariables['StatusCode'] = $this->getRequest()->getPost('StatusCode');
    		$formVariables['Message'] = $this->getRequest()->getPost('Message');
    		$formVariables['PreviousStatusCode'] = $this->getRequest()->getPost('PreviousStatusCode');
    		$formVariables['PreviousMessage'] = $this->getRequest()->getPost('PreviousMessage');
    		$formVariables['CrossReference'] = $this->getRequest()->getPost('CrossReference');
    		$formVariables['Amount'] = $this->getRequest()->getPost('Amount');
    		$formVariables['CurrencyCode'] = $this->getRequest()->getPost('CurrencyCode');
    		$formVariables['OrderID'] = $this->getRequest()->getPost('OrderID');
    		$formVariables['TransactionType'] = $this->getRequest()->getPost('TransactionType');
    		$formVariables['TransactionDateTime'] = $this->getRequest()->getPost('TransactionDateTime');
    		$formVariables['OrderDescription'] = $this->getRequest()->getPost('OrderDescription');
    		$formVariables['CustomerName'] = $this->getRequest()->getPost('CustomerName');
    		$formVariables['Address1'] = $this->getRequest()->getPost('Address1');
    		$formVariables['Address2'] = $this->getRequest()->getPost('Address2');
    		$formVariables['Address3'] = $this->getRequest()->getPost('Address3');
    		$formVariables['Address4'] = $this->getRequest()->getPost('Address4');
    		$formVariables['City'] = $this->getRequest()->getPost('City');
    		$formVariables['State'] = $this->getRequest()->getPost('State');
    		$formVariables['PostCode'] = $this->getRequest()->getPost('PostCode');
    		$formVariables['CountryCode'] = $this->getRequest()->getPost('CountryCode');
    		Mage::log(print_r($formVariables, 1));
    		if(!IRC_PaymentFormHelper::compareHostedPaymentFormHashDigest($formVariables, $szPassword, $hmHashMethod, $szPreSharedKey))
    		{
    			$error = "The payment was rejected for a SECURITY reason: the incoming payment data was tampered with.";
    			Mage::log("The Hosted Payment Form transaction couldn't be completed for the following reason: ".$error. " Form variables: ".print_r($formVariables, 1));
    		}
    	}
    	catch (Exception $exc)
    	{
    		$error = Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_183;
    		Mage::logException($exc);
    		Mage::log($error." Order ID: ".$formVariables['OrderID'].". Exception details: ".$exc);
    	}
    	
    	// check the incoming hash digest
    	if($error)
    	{
    		Mage::getSingleton('core/session')->addError($error);
    		$this->_redirect('checkout/onepage/failure');
    	}
    	else
    	{
    		switch ($formVariables['StatusCode'])
    		{
    			case "0":
    				Mage::getSingleton('checkout/type_onepage')->saveOrderAfterHostedPayment();
    				
    				Mage::log("Hosted Payment Form transaction successfully completed. Transaction details: ".print_r($formVariables, 1));
    				Mage::getSingleton('core/session')->addSuccess("Payment Processor Response: ".$formVariables['Message']);
    				$this->_redirect('checkout/onepage/success');
    				break;
    			case "20":
    				Mage::log("Duplicate Hosted Payment Form transaction. Transaction details: ".print_r($formVariables, 1));
    				$szNotificationMessage = "Payment Processor Response: ".$szMessage.". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction - Previous Transaction Response: ".$formVariables['PreviousMessage'];
    				if($formVariables['PreviousStatusCode'] == "0")
    				{
	    				Mage::getSingleton('core/session')->addSuccess($szNotificationMessage);
	    				$this->_redirect('checkout/onepage/success');
    				}
    				else
    				{
	    				Mage::getSingleton('core/session')->addError($szNotificationMessage);
	    				$this->_redirect('checkout/onepage/failure');
    				}
    				break;
    			case "5":
    			case "30":
    			default:
    				Mage::log("Hosted Payment Form transaction couldn't be completed. Transaction details: ".print_r($formVariables, 1));
    				Mage::getSingleton('core/session')->addError("Payment Processor Response: ".$formVariables['Message']);
    				$this->_redirect('checkout/onepage/failure');
    				break;
    		}
    	}
    }
    
    public function callbacktransparentredirectAction()
    {
    	$model = Mage::getModel('tpg/direct');
    	
    	try
    	{
    		$hmHashMethod = $model->getConfigData('hashmethod');
			$szPassword = $model->getConfigData('password');
			$szPreSharedKey = $model->getConfigData('presharedkey');
			
    		$szPaREQ = $this->getRequest()->getPost('PaREQ');
    		$szPaRES = $this->getRequest()->getPost('PaRes');
    		$nStatusCode = $this->getRequest()->getPost('StatusCode');
    		
    		if(isset($szPaREQ))
    		{
    			// 3D Secure authentication required
    			self::_threeDSecureAuthenticationRequired($szPassword, $hmHashMethod, $szPreSharedKey);
    		}
    		else if(isset($szPaRES))
    		{
    			// 3D Secure post authentication
    			self::_postThreeDSecureAuthentication($szPassword, $hmHashMethod, $szPreSharedKey);
    		}
    		else
    		{
    			// payment complete
    			self::_paymentComplete($szPassword, $hmHashMethod, $szPreSharedKey);
    		}
    		
    	}
    	catch (Exception $exc)
    	{
    		$error = Iridiumcorp_Tpg_Model_Tpg_GlobalErrors::ERROR_260;
    		Mage::logException($exc);
    		Mage::log($error." Exception details: ".$exc);
    		
    		Mage::getSingleton('core/session')->addError($error);
    		$this->_redirect('checkout/onepage/failure');
    	}
    }
    
    private function _threeDSecureAuthenticationRequired($szPassword, $hmHashMethod, $szPreSharedKey)
    {
    	$error = false;
    	$formVariables = array();
    	
    	$formVariables['HashDigest'] = $this->getRequest()->getPost('HashDigest');
    	$formVariables['MerchantID'] = $this->getRequest()->getPost('MerchantID');
    	$formVariables['StatusCode'] = $this->getRequest()->getPost('StatusCode');
    	$formVariables['Message'] = $this->getRequest()->getPost('Message');
    	$formVariables['CrossReference'] = $this->getRequest()->getPost('CrossReference');
    	$formVariables['OrderID'] = $this->getRequest()->getPost('OrderID');
    	$formVariables['TransactionDateTime'] = $this->getRequest()->getPost('TransactionDateTime');
    	$formVariables['ACSURL'] = $this->getRequest()->getPost('ACSURL');
    	$formVariables['PaREQ'] = $this->getRequest()->getPost('PaREQ');
    	
    	if(!IRC_PaymentFormHelper::compareThreeDSecureAuthenticationRequiredHashDigest($formVariables, $szPassword, $hmHashMethod, $szPreSharedKey))
    	{
    		$error = "The payment was rejected for a SECURITY reason: the incoming payment data was tampered with.";
    		Mage::log("The Transparent Redirect transaction couldn't be completed for the following reason: ".$error. " Form variables: ".print_r($formVariables, 1));
    	}
    	
    	if($error)
    	{
    		Mage::getSingleton('core/session')->addError($error);
    		$this->_redirect('checkout/onepage/failure');
    	}
    	else
    	{
    		// redirect to a secure 3DS authentication page
    		Mage::getSingleton('checkout/session')->setMd($formVariables['CrossReference'])
	        										->setAcsurl($formVariables['ACSURL'])
			  		   								->setPareq($formVariables['PaREQ'])
			  		   								->setTermurl('tpg/payment/callbacktransparentredirect');
			
			// redirect to a 3D Secure page
			$this->_redirect('tpg/payment/threedsecure');
    	}
    }
    
    private function _postThreeDSecureAuthentication($szPassword, $hmHashMethod, $szPreSharedKey)
    {
    	$error = false;
    	$formVariables = array();
    	$model = Mage::getModel('tpg/direct');

    	$szPaRES =  $this->getRequest()->getPost('PaRes');
    	$szCrossReference =  $this->getRequest()->getPost('MD');
    	$szMerchantID = $model->getConfigData('merchantid');
    	$szTransactionDateTime = date('Y-m-d H:i:s P');
    	$szCallbackURL = Mage::getUrl('tpg/payment/callbacktransparentredirect');
    	$szHashDigest = IRC_PaymentFormHelper::calculatePostThreeDSecureAuthenticationHashDigest($szMerchantID, $szPassword, $hmHashMethod, $szPreSharedKey, $szPaRES, $szCrossReference, $szTransactionDateTime, $szCallbackURL);
    	
    	
    	Mage::getSingleton('checkout/session')->setHashdigest($szHashDigest)
    											->setMerchantid($szMerchantID)
    											->setCrossreference($szCrossReference)
    											->setTransactiondatetime($szTransactionDateTime)
    											->setCallbackurl($szCallbackURL)
    											->setPares($szPaRES);
    	
    	// redirect to the redirection bridge page
    	$this->_redirect('tpg/payment/redirect');
    }
    
    private function _paymentComplete($szPassword, $hmHashMethod, $szPreSharedKey)
    {
    	$error = false;
    	$formVariables = array();

	    $formVariables['HashDigest'] = $this->getRequest()->getPost('HashDigest');
	    $formVariables['MerchantID'] = $this->getRequest()->getPost('MerchantID');
	    $formVariables['StatusCode'] = $this->getRequest()->getPost('StatusCode');
	    $formVariables['Message'] = $this->getRequest()->getPost('Message');
	    $formVariables['PreviousStatusCode'] = $this->getRequest()->getPost('PreviousStatusCode');
	    $formVariables['PreviousMessage'] = $this->getRequest()->getPost('PreviousMessage');
	    $formVariables['CrossReference'] = $this->getRequest()->getPost('CrossReference');
	    $formVariables['Amount'] = $this->getRequest()->getPost('Amount');
	    $formVariables['CurrencyCode'] = $this->getRequest()->getPost('CurrencyCode');
	    $formVariables['OrderID'] = $this->getRequest()->getPost('OrderID');
	    $formVariables['TransactionType'] = $this->getRequest()->getPost('TransactionType');
	    $formVariables['TransactionDateTime'] = $this->getRequest()->getPost('TransactionDateTime');
	    $formVariables['OrderDescription'] = $this->getRequest()->getPost('OrderDescription');
	    $formVariables['Address1'] = $this->getRequest()->getPost('Address1');
	    $formVariables['Address2'] = $this->getRequest()->getPost('Address2');
	    $formVariables['Address3'] = $this->getRequest()->getPost('Address3');
	    $formVariables['Address4'] = $this->getRequest()->getPost('Address4');
	    $formVariables['City'] = $this->getRequest()->getPost('City');
	    $formVariables['State'] = $this->getRequest()->getPost('State');
	    $formVariables['PostCode'] = $this->getRequest()->getPost('PostCode');
	    $formVariables['CountryCode'] = $this->getRequest()->getPost('CountryCode');
	    
	    if(!IRC_PaymentFormHelper::comparePaymentCompleteHashDigest($formVariables, $szPassword, $hmHashMethod, $szPreSharedKey))
    	{
    		$error = "The payment was rejected for a SECURITY reason: the incoming payment data was tampered with.";
    		Mage::log("The Transparent Redirect transaction couldn't be completed for the following reason: ".$error." Form variables: ".print_r($formVariables, 1));
    	}
    	
    	if($error)
    	{
    		Mage::getSingleton('core/session')->addError($error);
    		$this->_redirect('checkout/onepage/failure');
    	}
    	else
    	{
    		switch ($formVariables['StatusCode'])
    		{
    			case "0":
    				// TODO : replace with PCI compliant version of data saving
    				Mage::getSingleton('checkout/type_onepage')->saveOrderAfterHostedPayment();
    				
    				Mage::log("Transparent Redirect transaction successfully completed. Transaction details: ".print_r($formVariables, 1));
    				Mage::getSingleton('core/session')->addSuccess("Payment Processor Response: ".$formVariables['Message']);
    				$this->_redirect('checkout/onepage/success');
    				break;
    			case "20":
    				Mage::log("Duplicate Transparent Redirect transaction. Transaction details: ".print_r($formVariables, 1));
    				$szNotificationMessage = "Payment Processor Response: ".$formVariables['Message'].". A duplicate transaction means that a transaction with these details has already been processed by the payment provider. The details of the original transaction - Previous Transaction Response: ".$formVariables['PreviousMessage'];
    				if($formVariables['PreviousStatusCode'] == "0")
    				{
	    				Mage::getSingleton('core/session')->addSuccess($szNotificationMessage);
	    				$this->_redirect('checkout/onepage/success');
    				}
    				else
    				{
	    				Mage::getSingleton('core/session')->addError($szNotificationMessage);
	    				$this->_redirect('checkout/onepage/failure');
    				}
    				break;
    			case "5":
    			case "30":
    			default:
    				Mage::log("Transparent Redirect transaction couldn't be completed. Transaction details: ".print_r($formVariables, 1));
    				Mage::getSingleton('core/session')->addError("Payment Processor Response: ".$formVariables['Message']);
    				$this->_redirect('checkout/onepage/failure');
    				break;
    		}
    	}
    }
}
