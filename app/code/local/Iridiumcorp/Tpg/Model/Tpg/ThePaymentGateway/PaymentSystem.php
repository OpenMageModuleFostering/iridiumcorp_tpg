<?php
	require_once('TPG_Common.php');
	require_once('SOAP.php');
    	
	/*****************/
	/* Input classes */
	/*****************/
	class IRC_RequestGatewayEntryPoint extends IRC_GatewayEntryPoint 
	{
		private $m_nRetryAttempts;

	  	public function getRetryAttempts()
	  	{
	  		return $this->m_nRetryAttempts;
	  	}
		
		//constructor
	  	public function __construct($szEntryPointURL, $nMetric, $nRetryAttempts)
	   	{
	   		//do NOT forget to call the parent constructor too
	   		//parent::GatewayEntryPoint($szEntryPointURL, $nMetric);
	   		IRC_GatewayEntryPoint::__construct($szEntryPointURL, $nMetric);
	   		
	     	$this->m_nRetryAttempts = $nRetryAttempts;
	   	}
	}

	class IRC_RequestGatewayEntryPointList
	{
		private $m_lrgepRequestGatewayEntryPoint;
		
		public function getAt($nIndex)
		{
			if ($nIndex < 0 ||
				$nIndex >= count($this->m_lrgepRequestGatewayEntryPoint))
			{
				throw new Exception("Array index out of bounds");
			}
				
			return $this->m_lrgepRequestGatewayEntryPoint[$nIndex];
		}
		
		public function getCount()
		{
			return count($this->m_lrgepRequestGatewayEntryPoint);
		}
		
		public function sort($ComparerClassName, $ComparerMethodName)
		{
			usort($this->m_lrgepRequestGatewayEntryPoint, array("$ComparerClassName","$ComparerMethodName"));		
		}
		
		public function add($EntryPointURL, $nMetric, $nRetryAttempts)
		{
			return array_push($this->m_lrgepRequestGatewayEntryPoint, new IRC_RequestGatewayEntryPoint($EntryPointURL, $nMetric, $nRetryAttempts));
		}
		
		//constructor
		public function __construct()
		{
			$this->m_lrgepRequestGatewayEntryPoint = array();
		}
	}

	class IRC_GenericVariable
	{
		private $m_szName;
	   	private $m_szValue;

	   	public function getName()
	   	{
	   		return $this->m_szName;
	   	}
	   	public function getValue()
	   	{
	   		return $this->m_szValue;
	   	}

	   	//constructor
	   	public function __construct($szName, $szValue)
	    {
	    	$this->m_szName = $szName;
	    	$this->m_szValue = $szValue;
	    }
	}

	class IRC_GenericVariableList
	{
		private $m_lgvGenericVariableList;
		
		public function getAt($intOrStringValue)
		{
			$nCount = 0;
			$boFound = false;
			$gvGenericVariable = null;
			
			if (is_int($intOrStringValue))
			{
				if ($intOrStringValue < 0 ||
					$intOrStringValue >= count($this->m_lgvGenericVariableList))
				{
					throw new Exception("Array index out of bounds");
				}
				
				return $this->m_lgvGenericVariableList[$intOrStringValue];
			}
			elseif (is_string($intOrStringValue))
			{
				if ($intOrStringValue == null ||
					$intOrStringValue == '')
				{
					return (null);
				}

				while (!$boFound &&
						$nCount < count($this->m_lgvGenericVariableList))
				{
					if (strtoupper($this->m_lgvGenericVariableList[$nCount]->getName()) ==
						strtoupper($intOrStringValue))
					{
						$gvGenericVariable = $this->m_lgvGenericVariableList[$nCount];
						$boFound = true;
					}
					$nCount++;
				}

				return $gvGenericVariable;
			}
			else 
			{
				throw new Exception('Invalid parameter type:$intOrStringValue');
			}
		}
		
		public function getCount()
		{
			return count($this->m_lgvGenericVariableList);
		}
		
		public function add($Name, $szValue)
		{
			$nReturnValue = -1;
			
			if ($Name != null &&
				$Name != "")
			{
	        	$nReturnValue = array_push($this->m_lgvGenericVariableList, new IRC_GenericVariable($Name, $szValue));
			}

	        return ($nReturnValue);
		}
		
		//constructor
		public function __construct()
		{
			$this->m_lgvGenericVariableList = array();
		}
	}

	class IRC_CustomerDetails
	{
		private $m_adBillingAddress;
	    private $m_szEmailAddress;
	    private $m_szPhoneNumber;
	    private $m_szCustomerIPAddress;
	    
	    public function getBillingAddress()
	    {
	    	return $this->m_adBillingAddress;
	    }
	    public function getEmailAddress()
	    {
	    	return $this->m_szEmailAddress;
	    }
	    public function getPhoneNumber()
	    {
	    	return $this->m_szPhoneNumber;
	    }
	    public function getCustomerIPAddress()
	    {
	    	return $this->m_szCustomerIPAddress;
	    }
	    
	    //constructor
	    public function __construct($adBillingAddress = null, $szEmailAddress, $szPhoneNumber, $szCustomerIPAddress)
	    {
	    	$this->m_adBillingAddress = $adBillingAddress;
	    	$this->m_szEmailAddress = $szEmailAddress;
	    	$this->m_szPhoneNumber = $szPhoneNumber;
	    	$this->m_szCustomerIPAddress = $szCustomerIPAddress;
	    }
	}

	class IRC_AddressDetails
	{
		private $m_szAddress1;
	    private $m_szAddress2;
	    private $m_szAddress3;
	    private $m_szAddress4;
	    private $m_szCity;
	    private $m_szState;
	    private $m_szPostCode;
	    private $m_nCountryCode;
	    
	    public function getAddress1()
	    {
	    	return $this->m_szAddress1;
	    }
	    public function getAddress2()
	    {
	    	return $this->m_szAddress2;
	    }
	    public function getAddress3()
	    {
	    	return $this->m_szAddress3;
	    }
	    public function getAddress4()
	    {
	    	return $this->m_szAddress4;
	    }
	    public function getCity()
	    {
	    	return $this->m_szCity;
	    }
	    public function getState()
	    {
	    	return $this->m_szState;
	    }
	    public function getPostCode()
	    {
	    	return $this->m_szPostCode;
	    }
	    public function getCountryCode()
	    {
	  		return $this->m_nCountryCode;
	    }
	        
	    //constructor
	    public function __construct($szAddress1, $szAddress2, $szAddress3, $szAddress4, $szCity, $szState, $szPostCode, IRC_NullableInt $nCountryCode = null)
	    {
	    	$this->m_szAddress1 = $szAddress1;
	    	$this->m_szAddress2 = $szAddress2;
	    	$this->m_szAddress3 = $szAddress3;
	    	$this->m_szAddress4 = $szAddress4;
	    	$this->m_szCity = $szCity;
	    	$this->m_szState = $szState;
	    	$this->m_szPostCode = $szPostCode;
	    	$this->m_nCountryCode = $nCountryCode;
	    }
	}

	abstract class IRC_CreditCardDate
	{
		private  $m_nMonth;
	    private $m_nYear;
	    
	    public function getMonth()
	    {
	    	return $this->m_nMonth;
	    }
	    public function getYear()
	    {
	    	return $this->m_nYear;
	    }
	    
	    //constructor
	    public function __construct(IRC_NullableInt $nMonth = null, IRC_NullableInt $nYear = null)
	    {
	    	$this->m_nMonth = $nMonth;
	    	$this->m_nYear = $nYear;
	    }
	}

	class IRC_ExpiryDate extends IRC_CreditCardDate
	{
	    public function __construct(IRC_NullableInt $nMonth = null, IRC_NullableInt $nYear = null)
	    {
	      	parent::__construct($nMonth, $nYear);
		}
	}

	class IRC_StartDate extends IRC_CreditCardDate
	{
	    public function __construct(IRC_NullableInt $nMonth = null, IRC_NullableInt $nYear = null)
	    {
	      	parent::__construct($nMonth, $nYear);
		}
	}

	class IRC_CardDetails
	{
		private $m_szCardName;
	    private $m_szCardNumber;
	    private $m_edExpiryDate;
	    private $m_sdStartDate;
	    private $m_szIssueNumber;
	    private $m_szCV2;
	    
	    public function getCardName()
	    {
	    	return $this->m_szCardName;
	    }
	    public function getCardNumber()
	    {
	    	return $this->m_szCardNumber;
	    }
	    
	    public function getExpiryDate()
	    {
	    	return $this->m_edExpiryDate;
	    }
	   
	    public function getStartDate()
	    {
	    	return $this->m_sdStartDate;
	    }
	    
	    public function getIssueNumber()
	    {
	    	return $this->m_szIssueNumber;
	    }
	    
	    public function getCV2()
	    {
	    	return $this->m_szCV2;
	    }
	    
	    //constructor
	    public function __construct($szCardName, $szCardNumber, IRC_ExpiryDate $edExpiryDate = null, IRC_StartDate $sdStartDate = null, $IssueNumber, $CV2)
	    {
	    	$this->m_szCardName = $szCardName;
	    	$this->m_szCardNumber = $szCardNumber;
	    	$this->m_edExpiryDate = $edExpiryDate;
	    	$this->m_sdStartDate = $sdStartDate;
	    	$this->m_szIssueNumber = $IssueNumber;
	    	$this->m_szCV2 = $CV2;
	    }
	}

	class IRC_OverrideCardDetails extends IRC_CardDetails
	{
	    public function __construct($szCardName, $szCardNumber, IRC_ExpiryDate $edExpiryDate = null, IRC_StartDate $sdStartDate = null, $IssueNumber, $CV2)
	    {
			parent::__construct($szCardName, $szCardNumber, $edExpiryDate, $sdStartDate, $IssueNumber, $CV2);
		}
	}

	class IRC_MerchantAuthentication
	{
		private $m_szMerchantID;
	    private $m_szPassword;

	    public function getMerchantID()
	    {
	    	return $this->m_szMerchantID;
	    }
	    public function getPassword()
	    {
	    	return $this->m_szPassword;
	    }
	    
	    //constructor
	    public function __construct($szMerchantID, $szPassword)
	    {
	    	$this->m_szMerchantID = $szMerchantID;
	    	$this->m_szPassword = $szPassword;
	    }
	}

	class IRC_MessageDetails
	{
		private $m_szTransactionType;
	    private $m_boNewTransaction;
	    private $m_szCrossReference;

	    public function getTransactionType()
	    {
	    	return $this->m_szTransactionType;
	    }
	    public function getNewTransaction()
	    {
	    	return $this->m_boNewTransaction;
	    }
	    public function getCrossReference()
	    {
	    	return $this->m_szCrossReference;
	    }
	    
	    //constructor
	    public function __construct($szTransactionType, $szCrossReference = null, IRC_NullableBool $boNewTransaction = null)
	    {
	    	$this->m_szTransactionType = $szTransactionType;
	    	
	    	if ($szCrossReference != null)
	    	{
	    		$this->m_szCrossReference = $szCrossReference;
	    	}
	    	if ($boNewTransaction != null)
	    	{
	    		$this->m_boNewTransaction = $boNewTransaction;
	    	}
	    }
	}

	class IRC_TransactionDetails
	{
		private $m_mdMessageDetails;
	    private $m_nAmount;
	    private $m_nCurrencyCode;
	    private $m_szOrderID;
	    private $m_szOrderDescription;
	    private $m_tcTransactionControl;
	    private $m_tdsbdThreeDSecureBrowserDetails;
	    
	    public function getMessageDetails()
	    {
	    	return $this->m_mdMessageDetails;
	    }
	    public function getAmount()
	    {
	    	return $this->m_nAmount;
	    }
	    public function getCurrencyCode()
	    {
	    	return $this->m_nCurrencyCode;
	    }
	   	public function getOrderID()
	    {
	    	return $this->m_szOrderID;
	    }
	    public function getOrderDescription()
	    {
	    	return $this->m_szOrderDescription;
	    }
	    public function getTransactionControl()
	    {
	    	return $this->m_tcTransactionControl;
	    }
	    public function getThreeDSecureBrowserDetails()
	    {
	    	return $this->m_tdsbdThreeDSecureBrowserDetails;
	    }
	    
	    //constructor
	    public function __construct($TransactionTypeOrMessageDetails, IRC_NullableInt $nAmount = null, IRC_NullableInt $nCurrencyCode = null, $szOrderID, $szOrderDescription, IRC_TransactionControl $tcTransactionControl = null, IRC_ThreeDSecureBrowserDetails $tdsbdThreeDSecureBrowserDetails = null)
	    {
			if ($TransactionTypeOrMessageDetails instanceof IRC_MessageDetails)
			{
				$this->m_mdMessageDetails = $TransactionTypeOrMessageDetails;
	    		$this->m_nAmount = $nAmount;
	    		$this->m_nCurrencyCode = $nCurrencyCode;
	    		$this->m_szOrderID = $szOrderID;
	    		$this->m_szOrderDescription = $szOrderDescription;
	    		$this->m_tcTransactionControl = $tcTransactionControl;
	    		$this->m_tdsbdThreeDSecureBrowserDetails = $tdsbdThreeDSecureBrowserDetails;
			}
			else
			{
				$this->__construct(new IRC_MessageDetails($TransactionTypeOrMessageDetails), $nAmount, $nCurrencyCode, $szOrderID, $szOrderDescription, $tcTransactionControl, $tdsbdThreeDSecureBrowserDetails);
			}
	    }
	}

	class IRC_ThreeDSecureBrowserDetails
	{
		private $m_nDeviceCategory;
	    private $m_szAcceptHeaders;
	    private $m_szUserAgent;

	    public function getDeviceCategory()
	    {
	    	return $this->m_nDeviceCategory;
	    }
	    
	    public function getAcceptHeaders()
	    {
	    	return $this->m_szAcceptHeaders;
	    }
	    
	    public function getUserAgent()
	    {
	    	return $this->m_szUserAgent;
	    }
	    
	    //constructor
	    public function __construct(IRC_NullableInt $nDeviceCategory = null, $szAcceptHeaders, $szUserAgent)
	    {
	    	$this->m_nDeviceCategory = $nDeviceCategory;
	    	$this->m_szAcceptHeaders = $szAcceptHeaders;
	    	$this->m_szUserAgent = $szUserAgent;	
	    }
	}
	    
	class IRC_TransactionControl
	{
		private $m_boEchoCardType;
	    private $m_boEchoAVSCheckResult;
	    private $m_boEchoCV2CheckResult;
	   	private $m_boEchoAmountReceived;
	    private $m_nDuplicateDelay;
	    private $m_szAVSOverridePolicy;
	    private $m_szCV2OverridePolicy;
	    private $m_boThreeDSecureOverridePolicy;
	    private $m_szAuthCode;
	    private $m_tdsptThreeDSecurePassthroughData;
	    private $m_lgvCustomVariables;
	    
	    public function getEchoCardType()
	    {
	    	return $this->m_boEchoCardType;
	    }
	   
	    public function getEchoAVSCheckResult()
	    {
	    	return $this->m_boEchoAVSCheckResult;
	    }
	    
	    public function getEchoCV2CheckResult()
	    {
	    	return $this->m_boEchoCV2CheckResult;
	    }
	    
	    public function getEchoAmountReceived()
	    {
	    	return $this->m_boEchoAmountReceived;
	    }
	   
	    public function getDuplicateDelay()
	    {
	    	return $this->m_nDuplicateDelay;
	    }
	    
	    public function getAVSOverridePolicy()
	    {
	    	return $this->m_szAVSOverridePolicy;
	    }
	    
	    public function getCV2OverridePolicy()
	    {
	    	return $this->m_szCV2OverridePolicy;
	    }
	    
	    public function getThreeDSecureOverridePolicy()
	    {
	    	return $this->m_boThreeDSecureOverridePolicy;
	    }
	    
	    public function getAuthCode()
	    {
	    	return $this->m_szAuthCode;
	    }
	    
	    function getThreeDSecurePassthroughData()
	    {
	    	return $this->m_tdsptThreeDSecurePassthroughData;
	    }
	   
	    public function getCustomVariables()
	    {
	    	return $this->m_lgvCustomVariables;
	    }
	    
	    //constructor
	    public function __construct(IRC_NullableBool $boEchoCardType = null, IRC_NullableBool $boEchoAVSCheckResult = null, IRC_NullableBool $boEchoCV2CheckResult = null, IRC_NullableBool $boEchoAmountReceived = null, IRC_NullableInt $nDuplicateDelay = null, $szAVSOverridePolicy, $szCV2OverridePolicy, IRC_NullableBool $boThreeDSecureOverridePolicy = null, $szAuthCode, IRC_ThreeDSecurePassthroughData $tdsptThreeDSecurePassthroughData = null, IRC_GenericVariableList $lgvCustomVariables = null)
	    {
	    	$this->m_boEchoCardType = $boEchoCardType;
	    	$this->m_boEchoAVSCheckResult = $boEchoAVSCheckResult;
	    	$this->m_boEchoCV2CheckResult = $boEchoCV2CheckResult;
	    	$this->m_boEchoAmountReceived = $boEchoAmountReceived;
	    	$this->m_nDuplicateDelay = $nDuplicateDelay;
	    	$this->m_szAVSOverridePolicy = $szAVSOverridePolicy;
	    	$this->m_szCV2OverridePolicy = $szCV2OverridePolicy;
	    	$this->m_boThreeDSecureOverridePolicy = $boThreeDSecureOverridePolicy;
	    	$this->m_szAuthCode = $szAuthCode;
	    	$this->m_tdsptThreeDSecurePassthroughData = $tdsptThreeDSecurePassthroughData;
	    	$this->m_lgvCustomVariables = $lgvCustomVariables;
	    }
	}

	class IRC_ThreeDSecureInputData
	{
		private $m_szCrossReference;
	    private $m_szPaRES;

	    public function getCrossReference()
	    {
	    	return $this->m_szCrossReference;
	    }
	    
	    public function getPaRES()
	    {
	    	return $this->m_szPaRES;
	    }
	   
	    //constructor
	    public function __construct($szCrossReference, $szPaRES)
	    {
	    	$this->m_szCrossReference = $szCrossReference;
	    	$this->m_szPaRES = $szPaRES;
	    }
	}

	class IRC_ThreeDSecurePassthroughData
	{
	 	private $m_szEnrolmentStatus;
	    private $m_szAuthenticationStatus;
	    private $m_szElectronicCommerceIndicator;
	    private $m_szAuthenticationValue;
	    private $m_szTransactionIdentifier;

	    function getEnrolmentStatus()
	    {
	    	return $this->m_szEnrolmentStatus;
	    }
	    
	    function getAuthenticationStatus()
	    {
	    	return $this->m_szAuthenticationStatus;
	    }
	    
	    function getElectronicCommerceIndicator()
	    {
	    	return $this->m_szElectronicCommerceIndicator;
	    }
	    
	    function getAuthenticationValue()
	    {
	    	return $this->m_szAuthenticationValue;
	    }

	    function getTransactionIdentifier()
	    {
	    	return $this->m_szTransactionIdentifier;
	    }

	    //constructor
	    function __construct($szEnrolmentStatus,
	                    	 $szAuthenticationStatus,
	                         $szElectronicCommerceIndicator,
	                         $szAuthenticationValue,
	                         $szTransactionIdentifier)
	    {
	     	$this->m_szEnrolmentStatus = $szEnrolmentStatus;
	        $this->m_szAuthenticationStatus = $szAuthenticationStatus;
	        $this->m_szElectronicCommerceIndicator = $szElectronicCommerceIndicator;
	        $this->m_szAuthenticationValue = $szAuthenticationValue;
	        $this->m_szTransactionIdentifier = $szTransactionIdentifier;
	    }
	}


	/******************/
	/* Output classes */
	/******************/
	class IRC_Issuer
	{
		private $m_szIssuer;
		private $m_nISOCode;
		
		public function getValue()
		{
			return $this->m_szIssuer;
		}
		
		public function getISOCode()
		{
			return $this->m_nISOCode;
		}
		
		//constructor
	    public function __construct($szIssuer, $nISOCode)
	    {
	        $this->m_szIssuer = $szIssuer;
	        $this->m_nISOCode = $nISOCode;
	    }
	}
	
	class IRC_CardTypeData
	{
	    private $m_szCardType;
	    private $m_iIssuer;
	    private $m_boLuhnCheckRequired;
	    private $m_szIssueNumberStatus;
	    private $m_szStartDateStatus;

	    public function getCardType()
	    {
	        return $this->m_szCardType;
	    }
	   
	    public function getIssuer()
	    {
	    	return $this->m_iIssuer;
	    }
	   
	    public function getLuhnCheckRequired()
	    {
	        return $this->m_boLuhnCheckRequired;
	    }
	    
	    public function getIssueNumberStatus()
	    {
	        return $this->m_szIssueNumberStatus;
	    }
	   
	    public function getStartDateStatus()
	    {
	        return $this->m_szStartDateStatus;
	    }
	    
	    //constructor
	    public function __construct($szCardType, $iIssuer, IRC_NullableBool $boLuhnCheckRequired = null, $szIssueNumberStatus, $szStartDateStatus)
	    {
	        $this->m_szCardType = $szCardType;
	        //$this->m_szIssuer = $szIssuer;
	        $this->m_iIssuer = $iIssuer;
	        $this->m_boLuhnCheckRequired = $boLuhnCheckRequired;
	        $this->m_szIssueNumberStatus = $szIssueNumberStatus;
	        $this->m_szStartDateStatus = $szStartDateStatus;
	    }
	}

	class IRC_GatewayEntryPoint
	{
		private $m_szEntryPointURL;
	    private $m_nMetric;

	 	public function getEntryPointURL()
	 	{
	 		return $this->m_szEntryPointURL;
	 	}
	 	
	    public function getMetric()
	    {
	    	return $this->m_nMetric;
	    }

	    //constructor
	    public function __construct($szEntryPointURL, $nMetric)
	    {
			$this->m_szEntryPointURL = $szEntryPointURL;
			$this->m_nMetric = $nMetric;
	    }
	}

	class IRC_GatewayEntryPointList
	{
	    private $m_lgepGatewayEntryPoint;

	    public function getAt($nIndex)
	    {
	        if ($nIndex < 0 ||
		     	$nIndex >= count($this->m_lgepGatewayEntryPoint))
		     {
		  	 	throw new Exception("Array index out of bounds");
		     }
		
	        return $this->m_lgepGatewayEntryPoint[$nIndex];
	    }

	    public function getCount()
	    {
	        return count($this->m_lgepGatewayEntryPoint);
	    }

	    public function add($GatewayEntrypointOrEntrypointURL, $nMetric)
	    {
	    	return array_push($this->m_lgepGatewayEntryPoint, new IRC_GatewayEntryPoint($GatewayEntrypointOrEntrypointURL, $nMetric));
	    }
	    
	    //constructor
	    public function __construct()
	    {
	       $this->m_lgepGatewayEntryPoint = array();	
	    }
	}

	class IRC_PreviousTransactionResult
	{
		private $m_nStatusCode;
	    private $m_szMessage;
	    
	    function getStatusCode()
	    {
	    	return $this->m_nStatusCode;
	    }
	    
	    function getMessage()
	    {
	    	return $this->m_szMessage;
	    }
	    
	    function __construct(IRC_NullableInt $nStatusCode = null,
	    					 $szMessage)
	    {
	    	$this->m_nStatusCode = $nStatusCode;
	    	$this->m_szMessage = $szMessage;
	    }
	}

	class IRC_GatewayOutput
	{
	    private $m_nStatusCode;
	    private $m_szMessage;
	    private $m_szPassOutData;
	    //private $m_ptdPreviousTransactionResult;
	    //private $m_boAuthorisationAttempted;
	    private $m_lszErrorMessages;

	    public function getStatusCode()
	    {
	        return $this->m_nStatusCode;
	    }
	    
	    public function  getMessage()
	    {
	        return $this->m_szMessage;
	    }
	    
	    public function  getPassOutData()
	    {
	        return $this->m_szPassOutData;
	    }
	   
	    public function  getErrorMessages()
	    {
	        return $this->m_lszErrorMessages;
	    }
	    
	    //constructor
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_StringList $lszErrorMessages = null)
	    {
		    $this->m_nStatusCode = $nStatusCode;
			$this->m_szMessage = $szMessage;
			$this->m_szPassOutData = $szPassOutData;
			$this->m_lszErrorMessages = $lszErrorMessages;
	    }
	}

	class IRC_PaymentMessageGatewayOutput extends IRC_GatewayOutput
	{
	    private $m_ptdPreviousTransactionResult;
	    private $m_boAuthorisationAttempted;

	    public function  getPreviousTransactionResult()
	    {
	        return $this->m_ptdPreviousTransactionResult;
	    }
	    
	    public function  getAuthorisationAttempted()
	    {
	        return $this->m_boAuthorisationAttempted;
	    }
	    //constructor
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_NullableBool $boAuthorisationAttempted = null, IRC_PreviousTransactionResult $ptdPreviousTransactionResult = null, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $lszErrorMessages);
			$this->m_boAuthorisationAttempted = $boAuthorisationAttempted;
			$this->m_ptdPreviousTransactionResult = $ptdPreviousTransactionResult;
		}
	}

	class IRC_CardDetailsTransactionResult extends IRC_PaymentMessageGatewayOutput
	{
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_NullableBool $boAuthorisationAttempted = null, IRC_PreviousTransactionResult $ptdPreviousTransactionResult = null, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $boAuthorisationAttempted, $ptdPreviousTransactionResult, $lszErrorMessages);
		}
	}
	class IRC_CrossReferenceTransactionResult extends IRC_PaymentMessageGatewayOutput
	{
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_NullableBool $boAuthorisationAttempted = null, IRC_PreviousTransactionResult $ptdPreviousTransactionResult = null, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $boAuthorisationAttempted, $ptdPreviousTransactionResult, $lszErrorMessages);
		}
	}
	class IRC_ThreeDSecureTransactionResult extends IRC_PaymentMessageGatewayOutput
	{
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_NullableBool $boAuthorisationAttempted = null, IRC_PreviousTransactionResult $ptdPreviousTransactionResult = null, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $boAuthorisationAttempted, $ptdPreviousTransactionResult, $lszErrorMessages);
		}
	}
	class IRC_GetGatewayEntryPointsResult extends IRC_GatewayOutput
	{
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $lszErrorMessages);
		}
	}
	class IRC_GetCardTypeResult extends IRC_GatewayOutput
	{
	    public function __construct($nStatusCode, $szMessage, $szPassOutData, IRC_StringList $lszErrorMessages = null)
	    {
			parent::__construct($nStatusCode, $szMessage, $szPassOutData, $lszErrorMessages);
		}
	}

	class IRC_ThreeDSecureOutputData
	{
		private $m_szPaREQ;
	   	private $m_szACSURL;

	   	public function getPaREQ()
	   	{
			return $this->m_szPaREQ;
	   	}
	   
	   	public function getACSURL()
	   	{
	       	return ($this->m_szACSURL);
	   	}
	      
	   	//constructor
	   	public function __construct($szPaREQ, $szACSURL)
	   	{
			$this->m_szPaREQ = $szPaREQ;
	       	$this->m_szACSURL = $szACSURL;
	   	}
	}

	class IRC_GetGatewayEntryPointsOutputData extends IRC_BaseOutputData
	{
	   	//constructor
	   	function __construct(IRC_GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	parent::__construct($lgepGatewayEntryPoints);
	   	}
	}

	class IRC_TransactionOutputData extends IRC_BaseOutputData
	{
		private $m_szCrossReference;
		private $m_szAuthCode;
	    private $m_szAddressNumericCheckResult;
	    private $m_szPostCodeCheckResult;
	    private $m_szThreeDSecureAuthenticationCheckResult;
	    private $m_szCV2CheckResult;
	    private $m_ctdCardTypeData;
	    private $m_nAmountReceived;
	    private $m_tdsodThreeDSecureOutputData;
	    private $m_lgvCustomVariables;

	    public function getCrossReference()
	    { 
	        return $this->m_szCrossReference;
	    }
	    
	    public function getAuthCode()
	    { 
	        return $this->m_szAuthCode;
	    }

	    public function getAddressNumericCheckResult()
	    {
	       	return $this->m_szAddressNumericCheckResult;
	    }
	    
	    public function getPostCodeCheckResult()
	    { 
			return $this->m_szPostCodeCheckResult;
	    }
	    
	    public function getThreeDSecureAuthenticationCheckResult()
	    {
	        return $this->m_szThreeDSecureAuthenticationCheckResult;
	    }
	   
	    public function getCV2CheckResult()
	    {
	    	return $this->m_szCV2CheckResult;
	    }
	    
	    public function getCardTypeData()
	    {
	        return $this->m_ctdCardTypeData;
	    }
	   
	    public function getAmountReceived()
	    {
	       	return $this->m_nAmountReceived;
	    }
	    
	    public function getThreeDSecureOutputData()
	    {
	       	return $this->m_tdsodThreeDSecureOutputData;
	    }
	    
	    public function getCustomVariables()
	    {
	       	return $this->m_lgvCustomVariables;
	    }
	    
	 	//constructor
	    public function __construct($szCrossReference,
									$szAuthCode,
	    							$szAddressNumericCheckResult,
	    							$szPostCodeCheckResult,
	    							$szThreeDSecureAuthenticationCheckResult,
	    							$szCV2CheckResult,
	    							IRC_CardTypeData $ctdCardTypeData = null,
	    							IRC_NullableInt $nAmountReceived = null,
	    							IRC_ThreeDSecureOutputData $tdsodThreeDSecureOutputData = null,
	    							IRC_GenericVariableList $lgvCustomVariables = null,
	    							IRC_GatewayEntryPointList $lgepGatewayEntryPoints = null)
	    {
	     	//first calling the parent constructor
	        parent::__construct($lgepGatewayEntryPoints);
	        
		   	$this->m_szCrossReference = $szCrossReference;
			$this->m_szAuthCode = $szAuthCode;
			$this->m_szAddressNumericCheckResult = $szAddressNumericCheckResult;
			$this->m_szPostCodeCheckResult = $szPostCodeCheckResult;
			$this->m_szThreeDSecureAuthenticationCheckResult = $szThreeDSecureAuthenticationCheckResult;
			$this->m_szCV2CheckResult = $szCV2CheckResult;
			$this->m_ctdCardTypeData = $ctdCardTypeData;
			$this->m_nAmountReceived = $nAmountReceived;
			$this->m_tdsodThreeDSecureOutputData = $tdsodThreeDSecureOutputData;
			$this->m_lgvCustomVariables = $lgvCustomVariables;
	    }
	}

	class IRC_GetCardTypeOutputData extends IRC_BaseOutputData
	{
		private $m_ctdCardTypeData;

	   	public function getCardTypeData()
	   	{
	   		return $this->m_ctdCardTypeData;
	   	}

	  	//constructor
	   	public function __construct(IRC_CardTypeData $ctdCardTypeData = null,
	   								IRC_GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	parent::__construct($lgepGatewayEntryPoints);

	      	$this->m_ctdCardTypeData = $ctdCardTypeData;
	   	}
	}

	class IRC_BaseOutputData
	{
	   	private $m_lgepGatewayEntryPoints;

	   	public function getGatewayEntryPoints()
	   	{
	      	return $this->m_lgepGatewayEntryPoints;
	   	}

	   	//constructor
	   	public function __construct(IRC_GatewayEntryPointList $lgepGatewayEntryPoints = null)
	   	{
	      	$this->m_lgepGatewayEntryPoints = $lgepGatewayEntryPoints;
	   	}
	}


	/********************/
	/* Gateway messages */
	/********************/
	class IRC_GetGatewayEntryPoints extends IRC_GatewayTransaction
	{
	  	function processTransaction(IRC_GetGatewayEntryPointsResult &$ggeprGetGatewayEntryPointsResult = null, IRC_GetGatewayEntryPointsOutputData &$ggepGetGatewayEntryPointsOutputData = null)
	   	{
	      	$boTransactionSubmitted = false;
	      	$sSOAPClient;
	      	$lgepGatewayEntryPoints;

	      	$ggepGetGatewayEntryPointsOutputData = null;
	      	$goGatewayOutput = null;

	      	$sSOAPClient = new IRC_SOAP('GetGatewayEntryPoints', IRC_GatewayTransaction::getSOAPNamespace());
	      
	      	$boTransactionSubmitted = IRC_GatewayTransaction::processTransactionBase($sSOAPClient, 'GetGatewayEntryPointsMessage', 'GetGatewayEntryPointsResult', 'GetGatewayEntryPointsOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
	      
	      	if ($boTransactionSubmitted)
	      	{
				$ggeprGetGatewayEntryPointsResult = $goGatewayOutput;

	      		$ggepGetGatewayEntryPointsOutputData = new IRC_GetGatewayEntryPointsOutputData($lgepGatewayEntryPoints);
	      	}
	      
	      	return $boTransactionSubmitted;
	   	}
	   
	   	//constructor
	   	public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	   								$nRetryAttempts,
	   								IRC_NullableInt $nTimeout = null,
	   								IRC_MerchantAuthentication $maMerchantAuthentication = null,
	   								$szPassOutData)
	 	{
	   		if ($nRetryAttempts == null &&
	   			$nTimeout == null)
	   		{
	   			IRC_GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, 1, null, $maMerchantAuthentication, $szPassOutData);								
	   		}
	   		else 
	   		{
	   			IRC_GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $maMerchantAuthentication, $szPassOutData);
	   		}
	   	}
	}


	class IRC_CardDetailsTransaction extends IRC_GatewayTransaction 
	{
		private $m_tdTransactionDetails;
	    private $m_cdCardDetails;
	    private $m_cdCustomerDetails;
	     
	    public function getTransactionDetails()
	    {
	    	return $this->m_tdTransactionDetails;
	   	}
	     
	    public function getCardDetails()
	    {
	     	return $this->m_cdCardDetails;	
	    }
	     
	   	public function getCustomerDetails()
	    {
	    	return $this->m_cdCardDetails;
	    }
	     
	   	public function processTransaction(IRC_CardDetailsTransactionResult &$cdtrCardDetailsTransactionResult = null, IRC_TransactionOutputData &$todTransactionOutputData = null)
	   	{
	     	$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $sxXmlDocument;
			$goGatewayOutput = null;

	      	$todTransactionOutputData = null;
	        $cdtrCardDetailsTransactionResult = null;

	        $sSOAPClient = new IRC_SOAP('CardDetailsTransaction', parent::getSOAPNamespace());

	    	// transaction details
	       	if ($this->m_tdTransactionDetails != null)
	        {
	        	$test = $this->m_tdTransactionDetails->getAmount();
	       		if ($this->m_tdTransactionDetails->getAmount() != null)
	          	{
	            	if ($this->m_tdTransactionDetails->getAmount()->getHasValue())
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'Amount', (string)$this->m_tdTransactionDetails->getAmount()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getCurrencyCode() != null)
	          	{
	            	if ($this->m_tdTransactionDetails->getCurrencyCode()->getHasValue())
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'CurrencyCode', (string)$this->m_tdTransactionDetails->getCurrencyCode()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getMessageDetails() != null)
	            {
	            	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()))
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'TransactionType', $this->m_tdTransactionDetails->getMessageDetails()->getTransactionType());
	               	}
	            }
	            if ($this->m_tdTransactionDetails->getTransactionControl() != null)
	           	{
	             	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAuthCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AuthCode', $this->m_tdTransactionDetails->getTransactionControl()->getAuthCode());
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecureOverridePolicy() != null)
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecureOverridePolicy', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecureOverridePolicy()->getValue()));
	               	}
	               	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AVSOverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.CV2OverridePolicy', ($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()));
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.DuplicateDelay', (string)$this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getValue());
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCardType', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                  	}
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCV2CheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getValue()));
	                    }
	               	}
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAmountReceived', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData() != null)
	                {
	                	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getEnrolmentStatus()))
	                	{
	                		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData', 'EnrolmentStatus', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getEnrolmentStatus());
	                	}
	                	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationStatus()))
	                	{
	                		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData', 'AuthenticationStatus', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationStatus());
	                	}
	                	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getElectronicCommerceIndicator()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.ElectronicCommerceIndicator', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getElectronicCommerceIndicator());
	                	}
	                	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationValue()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.AuthenticationValue', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getAuthenticationValue());
	                	}
	                	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getTransactionIdentifier()))
	                	{
	                		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.ThreeDSecurePassthroughData.TransactionIdentifier', $this->m_tdTransactionDetails->getTransactionControl()->getThreeDSecurePassthroughData()->getTransactionIdentifier());
	                	}
	                }
	          	}
	          	if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails() != null)
	            {
	            	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getAcceptHeaders()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails.AcceptHeaders', $this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getAcceptHeaders());
	                }
	                if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails', 'DeviceCategory', (string)$this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getDeviceCategory()->getValue());
	                    }
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getUserAgent()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.ThreeDSecureBrowserDetails.UserAgent', $this->m_tdTransactionDetails->getThreeDSecureBrowserDetails()->getUserAgent());
	                }
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderID()))
	           	{
	             	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderID', $this->m_tdTransactionDetails->getOrderID());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderDescription()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderDescription', $this->m_tdTransactionDetails->getOrderDescription());
	            }
	        }

	        // card details
	        if ($this->m_cdCardDetails != null)
	        {
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCardName()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CardDetails.CardName', $this->m_cdCardDetails->getCardName());
	            }
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCV2()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CV2', $this->m_cdCardDetails->getCV2());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getCardNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CardNumber', $this->m_cdCardDetails->getCardNumber());
	            }
	            if ($this->m_cdCardDetails->getExpiryDate() != null)
	            {
	                if ($this->m_cdCardDetails->getExpiryDate()->getMonth() != null)
	                {
	                	if ($this->m_cdCardDetails->getExpiryDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.ExpiryDate', 'Month', (string)$this->m_cdCardDetails->getExpiryDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdCardDetails->getExpiryDate()->getYear() != null)
	                {
	                    if ($this->m_cdCardDetails->getExpiryDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.ExpiryDate', 'Year', (string)$this->m_cdCardDetails->getExpiryDate()->getYear()->getValue());
	                    }
	               	}
	            }
	            if ($this->m_cdCardDetails->getStartDate() != null)
	            {
	                if ($this->m_cdCardDetails->getStartDate()->getMonth() != null)
	                {
	                	if ($this->m_cdCardDetails->getStartDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.StartDate', 'Month', (string)$this->m_cdCardDetails->getStartDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_cdCardDetails->getStartDate()->getYear() != null)
	                {
	                    if ($this->m_cdCardDetails->getStartDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.CardDetails.StartDate', 'Year', (string)$this->m_cdCardDetails->getStartDate()->getYear()->getValue());
	                    }
	                }
	            }
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCardDetails->getIssueNumber()))
	            {
	               	$sSOAPClient->addParam('PaymentMessage.CardDetails.IssueNumber', $this->m_cdCardDetails->getIssueNumber());
	            }
	        }

	        // customer details
	        if ($this->m_cdCustomerDetails != null)
	        {
	        	if ($this->m_cdCustomerDetails->getBillingAddress() != null)
	            {
	             	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress1()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address1', $this->m_cdCustomerDetails->getBillingAddress()->getAddress1());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress2()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address2', $this->m_cdCustomerDetails->getBillingAddress()->getAddress2());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress3()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address3', $this->m_cdCustomerDetails->getBillingAddress()->getAddress3());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress4()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address4', $this->m_cdCustomerDetails->getBillingAddress()->getAddress4());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getCity()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.City', $this->m_cdCustomerDetails->getBillingAddress()->getCity());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getState()))
	                {
	                  	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.State', $this->m_cdCustomerDetails->getBillingAddress()->getState());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getPostCode()))
	                {
	                   	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.PostCode', $this->m_cdCustomerDetails->getBillingAddress()->getPostCode());
	                }
	                if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode() != null)
	                {
	                  	if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getHasValue())
	                    {
	                   		$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.CountryCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getValue());
	                    }
	                }
	      		}
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getEmailAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.EmailAddress', $this->m_cdCustomerDetails->getEmailAddress());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getPhoneNumber()))
	            {
	              	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.PhoneNumber', $this->m_cdCustomerDetails->getPhoneNumber());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getCustomerIPAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.CustomerIPAddress', $this->m_cdCustomerDetails->getCustomerIPAddress());
	            }
	       	}

	       	$boTransactionSubmitted = IRC_GatewayTransaction::processTransactionBase($sSOAPClient, 'PaymentMessage', 'CardDetailsTransactionResult', 'TransactionOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
	       	
			if ($boTransactionSubmitted)
			{
				$cdtrCardDetailsTransactionResult = IRC_SharedFunctionsPaymentSystemShared::getPaymentMessageGatewayOutput($sxXmlDocument->CardDetailsTransactionResult, $goGatewayOutput);
					
				$todTransactionOutputData = IRC_SharedFunctionsPaymentSystemShared::getTransactionOutputData($sxXmlDocument, $lgepGatewayEntryPoints);
			}

			return ($boTransactionSubmitted);
		}
	     
		public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	     							$nRetryAttempts,
	     							IRC_NullableInt $nTimeout = null,
	     							IRC_MerchantAuthentication $maMerchantAuthentication = null,
	                                IRC_TransactionDetails $tdTransactionDetails = null,
	                                IRC_CardDetails $cdCardDetails = null,
	                                IRC_CustomerDetails $cdCustomerDetails = null,
	                                $szPassOutData)
	  	{
	    	parent::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $maMerchantAuthentication, $szPassOutData);
	        	
	        $this->m_tdTransactionDetails = $tdTransactionDetails;
	        $this->m_cdCardDetails = $cdCardDetails;
	        $this->m_cdCustomerDetails = $cdCustomerDetails;
	    }
	     
	}
	class IRC_CrossReferenceTransaction extends IRC_GatewayTransaction 
	{
		private $m_tdTransactionDetails;
	    private $m_ocdOverrideCardDetails;
	    private $m_cdCustomerDetails;

	    public function getTransactionDetails()
	    {
			return $this->m_tdTransactionDetails;
	    }
	    public function getOverrideCardDetails()
	    {
	    	return $this->m_ocdOverrideCardDetails;
	    }
	    public function getCustomerDetails()
	    {
	    	return $this->m_cdCustomerDetails;
	    }
	        
	    public function processTransaction(IRC_CrossReferenceTransactionResult &$crtrCrossReferenceTransactionResult = null, IRC_TransactionOutputData &$todTransactionOutputData = null)
	    {
	    	$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $sxXmlDocument = null;

	        $todTransactionOutputData = null;
	        $goGatewayOutput = null;

	        $sSOAPClient = new IRC_SOAP('CrossReferenceTransaction', IRC_GatewayTransaction::getSOAPNamespace());
	      	// transaction details
	        if ($this->m_tdTransactionDetails != null)
	        {
	        	if ($this->m_tdTransactionDetails->getAmount() != null)
	          	{
	             	if ($this->m_tdTransactionDetails->getAmount()->getHasValue())
	                {
	               		$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'Amount', (string)$this->m_tdTransactionDetails->getAmount()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getCurrencyCode() != null)
	            {
	                if ($this->m_tdTransactionDetails->getCurrencyCode()->getHasValue())
	                {
	                    $sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails', 'CurrencyCode', (string)$this->m_tdTransactionDetails->getCurrencyCode()->getValue());
	                }
	            }
	            if ($this->m_tdTransactionDetails->getMessageDetails() != null)
	            {
	            	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getMessageDetails()->getTransactionType()))
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'TransactionType', $this->m_tdTransactionDetails->getMessageDetails()->getTransactionType());
	               	}
	                if ($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction()->getHasValue())
	                    {
	                        $sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'NewTransaction', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getMessageDetails()->getNewTransaction()->getValue()));
	                    }
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getMessageDetails()->getCrossReference()))
	                {
	                	$sSOAPClient->addParamAttribute('PaymentMessage.TransactionDetails.MessageDetails', 'CrossReference', $this->m_tdTransactionDetails->getMessageDetails()->getCrossReference());
	                }
	           	}
	           	if ($this->m_tdTransactionDetails->getTransactionControl() != null)
	           	{
	             	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAuthCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AuthCode', $this->m_tdTransactionDetails->getTransactionControl()->getAuthCode());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.AVSOverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getAVSOverridePolicy());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.CV2OverridePolicy', $this->m_tdTransactionDetails->getTransactionControl()->getCV2OverridePolicy());
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.DuplicateDelay', (string)($this->m_tdTransactionDetails->getTransactionControl()->getDuplicateDelay()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getHasValue())
	                    {
	                   		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCardType', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCardType()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                  	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult() != null)
	                {
	                  	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAVSCheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAVSCheckResult()->getValue()));
	                    }
	                }
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult() != null)
	                {
	                    if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoCV2CheckResult', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoCV2CheckResult()->getValue()));
	                    }
	              	}
	                if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived() != null)
	                {
	                	if ($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.TransactionDetails.TransactionControl.EchoAmountReceived', IRC_SharedFunctions::boolToString($this->m_tdTransactionDetails->getTransactionControl()->getEchoAmountReceived()->getValue()));
		               	}
	                }
	         	}
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderID()))
	            {
	           		$sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderID', $this->m_tdTransactionDetails->getOrderID());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdTransactionDetails->getOrderDescription()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.TransactionDetails.OrderDescription', $this->m_tdTransactionDetails->getOrderDescription());
	            }
	        }
	        // card details
	       	if ($this->m_ocdOverrideCardDetails != null)
	        {
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_ocdOverrideCardDetails->getCardName()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.OverrideCardDetails.CardName', $this->m_ocdOverrideCardDetails->getCardName());
	            }
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_ocdOverrideCardDetails->getCV2()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CardDetails.CV2', $this->m_ocdOverrideCardDetails->getCV2());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_ocdOverrideCardDetails->getCardNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.OverrideCardDetails.CardNumber', $this->m_ocdOverrideCardDetails->getCardNumber());
	            }
	            if ($this->m_ocdOverrideCardDetails->getExpiryDate() != null)
	            {
	                if ($this->m_ocdOverrideCardDetails->getExpiryDate()->getMonth() != null)
	                {
	                	if ($this->m_ocdOverrideCardDetails->getExpiryDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.ExpiryDate', 'Month', (string)$this->m_ocdOverrideCardDetails->getExpiryDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_ocdOverrideCardDetails->getExpiryDate()->getYear() != null)
	                {
	                    if ($this->m_ocdOverrideCardDetails->getExpiryDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.ExpiryDate', 'Year', (string)$this->m_ocdOverrideCardDetails->getExpiryDate()->getYear()->getValue());
	                    }
	                }
	            }
	            if ($this->m_ocdOverrideCardDetails->getStartDate() != null)
	            {
	              	if ($this->m_ocdOverrideCardDetails->getStartDate()->getMonth() != null)
	                {
	                	if ($this->m_ocdOverrideCardDetails->getStartDate()->getMonth()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.StartDate', 'Month', (string)$this->m_ocdOverrideCardDetails->getStartDate()->getMonth()->getValue());
	                    }
	                }
	                if ($this->m_ocdOverrideCardDetails->getStartDate()->getYear() != null)
	                {
	                   	if ($this->m_ocdOverrideCardDetails->getStartDate()->getYear()->getHasValue())
	                    {
	                    	$sSOAPClient->addParamAttribute('PaymentMessage.OverrideCardDetails.StartDate', 'Year', (string)$this->m_ocdOverrideCardDetails->getStartDate()->getYear()->getValue());
	                    }
	                }
	            }
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_ocdOverrideCardDetails->getIssueNumber()))
	            {
	               	$sSOAPClient->addParam('PaymentMessage.CardDetails.IssueNumber', $this->m_ocdOverrideCardDetails->getIssueNumber());
	            }
	        }
	        // customer details
			if ($this->m_cdCustomerDetails != null)
	        {
	        	if ($this->m_cdCustomerDetails->getBillingAddress() != null)
	            {
	             	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress1()))
	                {
	                	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address1', $this->m_cdCustomerDetails->getBillingAddress()->getAddress1());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress2()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address2', $this->m_cdCustomerDetails->getBillingAddress()->getAddress2());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress3()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address3', $this->m_cdCustomerDetails->getBillingAddress()->getAddress3());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getAddress4()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.Address4', $this->m_cdCustomerDetails->getBillingAddress()->getAddress4());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getCity()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.City', $this->m_cdCustomerDetails->getBillingAddress()->getCity());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getState()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.State', $this->m_cdCustomerDetails->getBillingAddress()->getState());
	                }
	                if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getBillingAddress()->getPostCode()))
	                {
	                    $sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.PostCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getPostCode());
	                }
	                if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode() != null)
	                {
	                    if ($this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getHasValue())
	                    {
	                    	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.BillingAddress.CountryCode', (string)$this->m_cdCustomerDetails->getBillingAddress()->getCountryCode()->getValue());
	                    }
	                }
	         	}
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getEmailAddress()))
	            {
	            	$sSOAPClient->addParam('PaymentMessage.CustomerDetails.EmailAddress', $this->m_cdCustomerDetails->getEmailAddress());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getPhoneNumber()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CustomerDetails.PhoneNumber', $this->m_cdCustomerDetails->getPhoneNumber());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_cdCustomerDetails->getCustomerIPAddress()))
	            {
	                $sSOAPClient->addParam('PaymentMessage.CustomerDetails.CustomerIPAddress', $this->m_cdCustomerDetails->getCustomerIPAddress());
	            }
	        }
	        
	        $boTransactionSubmitted = IRC_GatewayTransaction::processTransactionBase($sSOAPClient, 'PaymentMessage', 'CrossReferenceTransactionResult', 'TransactionOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);

	       	if ($boTransactionSubmitted)
	        {
				$crtrCrossReferenceTransactionResult = IRC_SharedFunctionsPaymentSystemShared::getPaymentMessageGatewayOutput($sxXmlDocument->CrossReferenceTransactionResult, $goGatewayOutput);
	
	        	$todTransactionOutputData = IRC_SharedFunctionsPaymentSystemShared::getTransactionOutputData($sxXmlDocument, $lgepGatewayEntryPoints);
	        }

	        return $boTransactionSubmitted;
	    }
	    
	    //constructor
	    public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
	    							$nRetryAttempts,
	    							IRC_NullableInt $nTimeout = null,
	    							IRC_MerchantAuthentication $maMerchantAuthentication = null,
	    							IRC_TransactionDetails $tdTransactionDetails = null,
	    							IRC_CardDetails $cdOverrideCardDetails = null,
	    							IRC_CustomerDetails $cdCustomerDetails = null,
	    							$szPassOutData)
	    {
	    	IRC_GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $maMerchantAuthentication, $szPassOutData);
		    	
		    $this->m_tdTransactionDetails = $tdTransactionDetails;
	      	$this->m_ocdOverrideCardDetails = $cdOverrideCardDetails;
	       	$this->m_cdCustomerDetails = $cdCustomerDetails;
	    }
	}

	class IRC_ThreeDSecureAuthentication extends IRC_GatewayTransaction
	{
		private $m_tdsidThreeDSecureInputData;
		
		public function getThreeDSecureInputData()
		{
			return $this->m_tdsidThreeDSecureInputData;
		}
		
		public function processTransaction(IRC_ThreeDSecureAuthenticationResult &$tdsarThreeDSecureAuthenticationResult = null, IRC_TransactionOutputData &$todTransactionOutputData = null)
		{
			$boTransactionSubmitted = false;
	        $sSOAPClient;
	        $lgepGatewayEntryPoints = null;
	        $sxXmlDocument = null;

	        $todTransactionOutputData = null;
	        $goGatewayOutput = null;

	       	$sSOAPClient = new IRC_SOAP('ThreeDSecureAuthentication', IRC_GatewayTransaction::getSOAPNamespace());
	       	if ($this->m_tdsidThreeDSecureInputData != null)
	        {
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdsidThreeDSecureInputData->getCrossReference()))
	            {
	                $sSOAPClient->addParamAttribute('ThreeDSecureMessage.ThreeDSecureInputData', 'CrossReference', $this->m_tdsidThreeDSecureInputData->getCrossReference());
	            }
	            if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_tdsidThreeDSecureInputData->getPaRES()))
	            {
	            	$sSOAPClient->addParam('ThreeDSecureMessage.ThreeDSecureInputData.PaRES', $this->m_tdsidThreeDSecureInputData->getPaRES());
	            }
	        }
	        
	        $boTransactionSubmitted = IRC_GatewayTransaction::processTransactionBase($sSOAPClient, 'ThreeDSecureMessage', 'ThreeDSecureAuthenticationResult', 'TransactionOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
	       	
	        if ($boTransactionSubmitted)
	      	{
				$tdsarThreeDSecureAuthenticationResult = IRC_SharedFunctionsPaymentSystemShared::getPaymentMessageGatewayOutput($sxXmlDocument->ThreeDSecureAuthenticationResult, $goGatewayOutput);

	        	$todTransactionOutputData = IRC_SharedFunctionsPaymentSystemShared::getTransactionOutputData($sxXmlDocument, $lgepGatewayEntryPoints);
	        }

	        return $boTransactionSubmitted;
		}
		
		//constructor
		public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
									IRC_NullableInt $nTimeout = null,
									IRC_MerchantAuthentication $maMerchantAuthentication = null,
	                              	IRC_ThreeDSecureInputData $tdsidThreeDSecureInputData = null,
	                                $szPassOutData)
	 	{
	    	IRC_GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $maMerchantAuthentication, $szPassOutData);
	    	
	    	$this->m_tdsidThreeDSecureInputData = $tdsidThreeDSecureInputData;
	    }
	}

	class IRC_GetCardType extends IRC_GatewayTransaction
	{
		private $m_szCardNumber;
		
		public function getCardNumber()
		{
			return $this->m_szCardNumber;
		}
		
		public function processTransaction(IRC_GetCardTypeResult &$gctrGetCardTypeResult = null, IRC_GetCardTypeOutputData &$gctodGetCardTypeOutputData = null)
		{
			$boTransactionSubmitted = false;
	        $sSOAPClient;
	       	$lgepGatewayEntryPoints = null;
	        $ctdCardTypeData = null;
	        $sxXmlDocument = null;

	       	$gctodGetCardTypeOutputData = null;
	        $goGatewayOutput = null;

	      	$sSOAPClient = new IRC_SOAP('GetCardType', IRC_GatewayTransaction::getSOAPNamespace());
	      	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_szCardNumber))
	       	{
	        	$sSOAPClient->addParam('GetCardTypeMessage.CardNumber', $this->m_szCardNumber);
	        }
	        
	        $boTransactionSubmitted = IRC_GatewayTransaction::processTransactionBase($sSOAPClient, 'GetCardTypeMessage', 'GetCardTypeResult', 'GetCardTypeOutputData', $sxXmlDocument, $goGatewayOutput, $lgepGatewayEntryPoints);
			
	        if ($boTransactionSubmitted)
	        {
				$gctrGetCardTypeResult = $goGatewayOutput;

	        	if(!$sxXmlDocument->GetCardTypeOutputData->CardTypeData)
	        	{
	        		$ctdCardTypeData = null;
	        	}
	        	else
	        	{
	            	$ctdCardTypeData = IRC_SharedFunctionsPaymentSystemShared::getCardTypeData($sxXmlDocument->GetCardTypeOutputData->CardTypeData);
	        	}
	        	
	            if (!is_null($ctdCardTypeData)) 
	            {
	                $gctodGetCardTypeOutputData = new IRC_GetCardTypeOutputData($ctdCardTypeData, $lgepGatewayEntryPoints);
	            } 
			}
	        return $boTransactionSubmitted;
		}
		
		//constructor
		public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
	                           		IRC_NullableInt $nTimeout = null,
	                           		IRC_MerchantAuthentication $maMerchantAuthentication = null,
	                           		$szCardNumber,
	                          		$szPassOutData)
	  	{
	    	IRC_GatewayTransaction::__construct($lrgepRequestGatewayEntryPoints, $nRetryAttempts, $nTimeout, $maMerchantAuthentication, $szPassOutData);

	    	$this->m_szCardNumber = $szCardNumber;	
	    }
	}

	abstract class IRC_GatewayTransaction
	{
	    private $m_maMerchantAuthentication;
	 	private $m_szPassOutData;
	    private $m_lrgepRequestGatewayEntryPoints;
	    private $m_nRetryAttempts;
	    private $m_nTimeout;
	    private $m_szSOAPNamespace = 'https://www.thepaymentgateway.net/';
	    private $m_szLastRequest;
		private $m_szLastResponse;
		private $m_eLastException;
		private $m_szEntryPointUsed;

	   	public function getMerchantAuthentication()
	   	{
	      	return $this->m_maMerchantAuthentication;
	   	}
	  
	  	public function getPassOutData()
	   	{
	    	return $this->m_szPassOutData;
	  	}
	   
	   	public function getRequestGatewayEntryPoints()
	   	{
	    	return $this->m_lrgepRequestGatewayEntryPoints;
	   	}
	   
	   	public function getRetryAttempts()
	   	{
	      	return $this->m_nRetryAttempts;
	   	}
	   
	   	public function getTimeout()
	   	{
	      	return $this->m_nTimeout;
	   	}
	   
	   	public function getSOAPNamespace()
	   	{
	      	return $this->m_szSOAPNamespace;
	   	}
	   	public function setSOAPNamespace($value)
	   	{
	      	$this->m_szSOAPNamespace = $value;
	   	}
	   	
	   	public function getLastRequest()
	   	{
	   		return $this->m_szLastRequest;
	   	}
	   	
	   	public function getLastResponse()
	   	{
	   		return $this->m_szLastResponse;
	   	}
	   	
	   	public function getLastException()
	   	{
	   		return $this->m_eLastException;
	   	}
	   	public function getEntryPointUsed()
	   	{
	   		return $this->m_szEntryPointUsed;
	   	}

	   	public static function compare($x, $y)
	   	{
	      	$rgepFirst = null;
	      	$rgepSecond = null;
	     
	      	$rgepFirst = $x;
	      	$rgepSecond = $y;

	      	return (IRC_GatewayTransaction::compareGatewayEntryPoints($rgepFirst, $rgepSecond));
	   	}

	   	private static function compareGatewayEntryPoints(IRC_RequestGatewayEntryPoint $rgepFirst, IRC_RequestGatewayEntryPoint $rgepSecond)
	   	{
			$nReturnValue = 0;
	      	// returns >0 if rgepFirst greater than rgepSecond
	      	// returns 0 if they are equal
	      	// returns <0 if rgepFirst less than rgepSecond
	      
	      	// both null, then they are the same
	      	if ($rgepFirst == null &&
	          	$rgepSecond == null)
	   		{
	        	$nReturnValue = 0;
	        }
	      	// just first null? then second is greater
	      	elseif ($rgepFirst == null &&
		    		$rgepSecond != null)
	      	{
	        	$nReturnValue = 1;
	        }
	      	// just second null? then first is greater
	      	elseif ($rgepFirst != null  && $rgepSecond == null)
	      	{
	        	$nReturnValue = -1;
	        }
	      	// can now assume that first & second both have a value
	      	elseif ($rgepFirst->getMetric() == $rgepSecond->getMetric())
	        {
	        	$nReturnValue = 0;
	        }
	      	elseif ($rgepFirst->getMetric() < $rgepSecond->getMetric())
	        {
	        	$nReturnValue = -1;
	        }
	      	elseif ($rgepFirst->getMetric() > $rgepSecond->getMetric())
		    {
				$nReturnValue = 1;
	  	    }

	      	return $nReturnValue;
	   	}

	   	protected function processTransactionBase(IRC_SOAP $sSOAPClient, $szMessageXMLPath, $szGatewayOutputXMLPath, $szTransactionMessageXMLPath, SimpleXMLElement &$sxXmlDocument = null, IRC_GatewayOutput &$goGatewayOutput = null, IRC_GatewayEntryPointList &$lgepGatewayEntryPoints = null)
	   	{
			$boTransactionSubmitted = false;
		    $nOverallRetryCount = 0;
		    $nOverallGatewayEntryPointCount = 0;
		    $nGatewayEntryPointCount = 0;
		    $nErrorMessageCount = 0;
		    $rgepCurrentGatewayEntryPoint;
		    $nStatusCode;
		    $szMessage = null;
		    $lszErrorMessages;
		    $szString;
		    $sbXMLString;
		    $szXMLFormatString;
		    $nCount = 0;
		    $szEntryPointURL;
		    $nMetric;
		    $gepGatewayEntryPoint = null;
		    $szPassOutData = null;
		    $ResponseDocument = null;
		    $ResponseMethod = null;

	      	$lgepGatewayEntryPoints = null;
	      	$goGatewayOutput = null;

			$this->m_szEntryPointUsed = null;

	      	if ($sSOAPClient == null)
	      	{
	        	return false;
	      	}

	       	// populate the merchant details
	       	if ($this->m_maMerchantAuthentication != null)
	       	{
	        	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_maMerchantAuthentication->getMerchantID()))
	          	{
	            	$sSOAPClient->addParamAttribute($szMessageXMLPath. '.MerchantAuthentication', 'MerchantID', $this->m_maMerchantAuthentication->getMerchantID());
	          	}
	          	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_maMerchantAuthentication->getPassword()))
	          	{
	             	$sSOAPClient->addParamAttribute($szMessageXMLPath. '.MerchantAuthentication', 'Password', $this->m_maMerchantAuthentication->getPassword());
	          	}
	       	}
	       	
	       	// populate the passout data
	       	if (!IRC_SharedFunctions::isStringNullOrEmpty($this->m_szPassOutData))
	       	{
	        	$sSOAPClient->addParam($szMessageXMLPath. '.PassOutData', $this->m_szPassOutData, null);
	       	}

	      	// first need to sort the gateway entry points into the correct usage order
	       	$number = $this->m_lrgepRequestGatewayEntryPoints->sort('IRC_GatewayTransaction','Compare');
	       	
	       	// loop over the overall number of transaction attempts
	       	while (!$boTransactionSubmitted &&
	       			$nOverallRetryCount < $this->m_nRetryAttempts) 
	       	{
	       		$nOverallGatewayEntryPointCount = 0;
	       			
	       		// loop over the number of gateway entry points in the list
	            while (!$boTransactionSubmitted &&
	                 	$nOverallGatewayEntryPointCount < $this->m_lrgepRequestGatewayEntryPoints->getCount())
	          	{

					$rgepCurrentGatewayEntryPoint = $this->m_lrgepRequestGatewayEntryPoints->getAt($nOverallGatewayEntryPointCount);
					
					// ignore if the metric is "-1" this indicates that the entry point is offline
	              	if ($rgepCurrentGatewayEntryPoint->getMetric() >= 0)
	                {
	              		$nGatewayEntryPointCount = 0;
	                 	$sSOAPClient->setURL($rgepCurrentGatewayEntryPoint->getEntryPointURL());
						
	                    // loop over the number of times to try this specific entry point
	                    while (!$boTransactionSubmitted &&
	                          	$nGatewayEntryPointCount < $rgepCurrentGatewayEntryPoint->getRetryAttempts())
	                  	{
	                    	if ($sSOAPClient->sendRequest($ResponseDocument, $ResponseMethod))
	                        {
	                        	//getting the valid transaction type document format
	                        	$sxXmlDocument = $ResponseDocument->$ResponseMethod;
	                        	
	                        	$lszErrorMessages = new IRC_StringList();
	                        	
								$nStatusCode = (int)current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->StatusCode[0]);

								// a status code of 50 means that this entry point is not to be used
								if ($nStatusCode != 50)
								{
									$this->m_szEntryPointUsed = $rgepCurrentGatewayEntryPoint->getEntryPointURL();

		                        	// the transaction was submitted
		                        	$boTransactionSubmitted = true;
									
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->Message)
									{
										$szMessage = current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->Message[0]);
									}
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->ErrorMessages)
									{
										foreach ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->ErrorMessages->MessageDetail as $key => $value)
										{
											$lszErrorMessages->add(current($value->Detail));
 										}
									}
																		
									if ($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PassOutData)
									{
										$szPassOutData = current($ResponseDocument->$ResponseMethod->$szGatewayOutputXMLPath->PassOutData[0]);
									}
									else 
									{
										$szPassOutData = null;
									}
									
									$goGatewayOutput = new IRC_GatewayOutput($nStatusCode, $szMessage, $szPassOutData/*, $boAuthorisationAttempted, $ptdPreviousTransactionResult*/, $lszErrorMessages);
		                                
		                            // look to see if there are any gateway entry points
		                            $nCount = 0;
		                            		                            
		                            $nMetric = -1;
		                            
		                            if ($ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints)
		                            {
		                            	if($ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints->GatewayEntryPoint)
		                            	{
			                            	$szXMLFormatString = $ResponseDocument->$ResponseMethod->$szTransactionMessageXMLPath->GatewayEntryPoints->GatewayEntryPoint;
			                            	
					                      	foreach($szXMLFormatString->attributes() as $key => $value)
					                        {
					                          	if (is_numeric(current($value)))
					                           	{
					                           		$nMetric = current($value);
					                           	}
					                           	else 
					                           	{
					                           		$szEntryPointURL = current($value);
					                           	}
					                       	}
				                            
				                            //$gepGatewayEntryPoint = new GatewayEntryPoint($szEntryPointURL, $nMetric);
				                            if ($lgepGatewayEntryPoints == null)
				                            {
				                            	$lgepGatewayEntryPoints = new IRC_GatewayEntryPointList();
				                            }
				                            $lgepGatewayEntryPoints->add($szEntryPointURL, $nMetric); //$lgepGatewayEntryPoints->add($gepGatewayEntryPoint);
		                            	}
		                            }
		                            $nCount++;
								}
	                    	}
	                            
	                        $nGatewayEntryPointCount++;
	                  	}
	              	}
	                $nOverallGatewayEntryPointCount++;
	       		}
	       		$nOverallRetryCount++;
	   		}
	   		$this->m_szLastRequest = $sSOAPClient->getSOAPPacket();
	   		$this->m_szLastResponse = $sSOAPClient->getLastResponse();
	   		$this->m_eLastException = $sSOAPClient->getLastException();

	   		return $boTransactionSubmitted;
		}
		
		public function __construct(IRC_RequestGatewayEntryPointList $lrgepRequestGatewayEntryPoints = null,
									$nRetryAttempts,
									IRC_NullableInt $nTimeout = null,
									IRC_MerchantAuthentication $maMerchantAuthentication = null,
									$szPassOutData)
		{
			$this->m_maMerchantAuthentication = $maMerchantAuthentication;
			$this->m_szPassOutData = $szPassOutData;
			$this->m_lrgepRequestGatewayEntryPoints = $lrgepRequestGatewayEntryPoints;
			$this->m_nRetryAttempts = $nRetryAttempts;
			$this->m_nTimeout = $nTimeout;
		}
	}

	class IRC_SharedFunctionsPaymentSystemShared
	{
		public static function getTransactionOutputData(SimpleXMLElement $sxXmlDocument, IRC_GatewayEntryPointList $lgepGatewayEntryPoints = null)
		{
			$szCrossReference = null;
	        $szAddressNumericCheckResult = null;
	        $szPostCodeCheckResult = null;
	        $szThreeDSecureAuthenticationCheckResult = null;
	        $szCV2CheckResult = null;
	        $nAmountReceived = null;
	        $szPaREQ = null;
	        $szACSURL = null;
	        $ctdCardTypeData = null;
	        $tdsodThreeDSecureOutputData = null;
	        $lgvCustomVariables = null;
	        $nCount = 0;
	        $sbString;
	        $szXMLFormatString;
	        $szName;
	        $szValue;
	        $gvGenericVariable;
	        $nCount = 0;
	        $szCardTypeData;
	        
	        $todTransactionOutputData = null;

			if (!$sxXmlDocument->TransactionOutputData)
			{
				return (null);
			}

		    if ($sxXmlDocument->TransactionOutputData->attributes())
		    {
		    	foreach($sxXmlDocument->TransactionOutputData->attributes() as $key => $value)
		    	{
		    		$szCrossReference = current($value);
		    	}
		    }
		    else 
		    {
		    	$szCrossReference = null;
		    }

			if ($sxXmlDocument->TransactionOutputData->AuthCode)
			{
				$szAuthCode = current($sxXmlDocument->TransactionOutputData->AuthCode[0]);
			}
			else
			{
				$szAuthCode = null;
			}

			if ($sxXmlDocument->TransactionOutputData->AddressNumericCheckResult)
			{
				$szAddressNumericCheckResult = current($sxXmlDocument->TransactionOutputData->AddressNumericCheckResult[0]);
			}
			
			if ($sxXmlDocument->TransactionOutputData->PostCodeCheckResult)
			{
		    	$szPostCodeCheckResult = current($sxXmlDocument->TransactionOutputData->PostCodeCheckResult[0]);
			}
		    
		    if ($sxXmlDocument->TransactionOutputData->ThreeDSecureAuthenticationCheckResult)
		    {
				$szThreeDSecureAuthenticationCheckResult = current($sxXmlDocument->TransactionOutputData->ThreeDSecureAuthenticationCheckResult[0]);
		    }

			if ($sxXmlDocument->TransactionOutputData->CV2CheckResult)
			{
		    	$szCV2CheckResult = current($sxXmlDocument->TransactionOutputData->CV2CheckResult[0]);
			}
		    
		    if ($sxXmlDocument->TransactionOutputData->CardTypeData)
		    {
		    	$ctdCardTypeData = self::getCardTypeData($sxXmlDocument->TransactionOutputData->CardTypeData);
		    }
		    else 
		    {
		    	$ctdCardTypeData = null;
		    }

			if ($sxXmlDocument->TransactionOutputData->AmountReceived)
			{
		    	$nAmountReceived = new IRC_NullableInt(current($sxXmlDocument->TransactionOutputData->AmountReceived[0]));
			}
			else 
			{
				$nAmountReceived = new IRC_NullableInt(null);
			}
			
			if ($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData)
			{
				$szPaREQ = current($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData->PaREQ[0]);
				$szACSURL = current($sxXmlDocument->TransactionOutputData->ThreeDSecureOutputData->ACSURL[0]);
			}
			else 
			{
				$szPaREQ = null;
				$szACSURL = null;
			}
			

		    if (!IRC_SharedFunctions::isStringNullOrEmpty($szACSURL) &&
		    	!IRC_SharedFunctions::isStringNullOrEmpty($szPaREQ))
		    {
		    	$tdsodThreeDSecureOutputData = new IRC_ThreeDSecureOutputData($szPaREQ, $szACSURL);
		    }
		        
			if ($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable)
			{
				if ($lgvCustomVariables == null)
				{
					$lgvCustomVariables = new IRC_GenericVariableList();
				}
				for ($nCount=0; $nCount < count($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable); $nCount++)
				{
					$szName = current($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable[$nCount]->Name[0]);
					$szValue = current($sxXmlDocument->TransactionOutputData->CustomVariables->GenericVariable[$nCount]->Value[0]);
					$gvGenericVariable = new IRC_GenericVariable($szName, $szValue);
					$lgvCustomVariables->add($gvGenericVariable);
				}
			}
			else 
			{
				$lgvCustomVariables = null;
			}


		    $todTransactionOutputData = new IRC_TransactionOutputData($szCrossReference,
																	  $szAuthCode,
																      $szAddressNumericCheckResult,
																      $szPostCodeCheckResult,
																      $szThreeDSecureAuthenticationCheckResult,
																      $szCV2CheckResult,
																      $ctdCardTypeData,
																      $nAmountReceived,
																      $tdsodThreeDSecureOutputData,
																      $lgvCustomVariables,
																      $lgepGatewayEntryPoints);
			
	     	return $todTransactionOutputData;
		}

		public static function getCardTypeData($CardTypeDataTag)
		{
			$ctdCardTypeData = null;
	        $szCardType = null;
	        $boLuhnCheckRequired = null;
	        $szStartDateStatus = null;
	        $szIssueNumberStatus = null;
	        $szIssuer = null;
	        $nISOCode = null;
	        $iIssuer;

			if ($CardTypeDataTag->CardType)
			{
				$szCardType = current($CardTypeDataTag->CardType[0]);
			}
			
			if ($CardTypeDataTag->Issuer)
			{
				try 
				{
					$szIssuer = (string)$CardTypeDataTag->Issuer[0];
				} 
				catch (Exception $e) 
				{
					$szIssuer = null;
				}
				
				try
				{
					$nISOCode = current($CardTypeDataTag->Issuer->attributes()->ISOCode);
				}
				catch (Exception $e)
				{
					$nISOCode = null;
				}
				
				$iIssuer = new IRC_Issuer($szIssuer, $nISOCode);
			}
			else 
			{
				$iIssuer = null;
			}
			
			if ($CardTypeDataTag->LuhnCheckRequired)
			{
				$boLuhnCheckRequired = new IRC_NullableBool(current($CardTypeDataTag->LuhnCheckRequired[0]));
			}
			else 
			{
				$boLuhnCheckRequired = null;
			}
			
			if ($CardTypeDataTag->IssueNumberStatus)
			{
				try 
				{
					$szIssueNumberStatus = current($CardTypeDataTag->IssueNumberStatus[0]);
				} 
				catch (Exception $e) 
				{
					$szIssueNumberStatus = null;
				}
			}
			else 
			{
				$szIssueNumberStatus = null;
			}
			
			if ($CardTypeDataTag->StartDateStatus)
			{
				try 
				{
					$szStartDateStatus = current($CardTypeDataTag->StartDateStatus[0]);
				} 
				catch (Exception $e) 
				{
					$szStartDateStatus = null;
				}
			}
			else 
			{
				$szStartDateStatus = null;
			}
			
			$ctdCardTypeData = new IRC_CardTypeData($szCardType, $iIssuer, $boLuhnCheckRequired, $szIssueNumberStatus, $szStartDateStatus);

	        return ($ctdCardTypeData);
		}
		
		
		
	   	public static function getPaymentMessageGatewayOutput($GatewayOutput, IRC_GatewayOutput $goGatewayOutput = null)
	   	{
		    $nPreviousStatusCode = null;
		    $szPreviousMessage = null;
		    $ptdPreviousTransactionResult = null;
			$pmgoPaymentMessageGatewayOutput = null;
			$boAuthorisationAttempted = null;

			if ($GatewayOutput->attributes())
			{
				try
				{
					$szAuthorisationAttempted = current($GatewayOutput->attributes()->AuthorisationAttempted);
					if (strtolower($boAuthorisationAttempted) == 'false')
					{
						$boAuthorisationAttempted = new IRC_NullableBool(false);
					}
					elseif (strtolower($boAuthorisationAttempted) == 'true')
					{
						$boAuthorisationAttempted = new IRC_NullableBool(true);
					}
					else 
					{
						throw new Exception('Return value must be true or false');
					}
				}
				catch (Exception $e)
				{
					$boAuthorisationAttempted = null;
				}
			}
											
			//check to see if there is any previous transaction data
			if ($GatewayOutput->PreviousTransactionResult->StatusCode)
			{
				$nPreviousStatusCode = new IRC_NullableInt(current($GatewayOutput->PreviousTransactionResult->StatusCode[0]));
			}

			if ($GatewayOutput->PreviousTransactionResult->Message)
			{
				$szPreviousMessage = current($GatewayOutput->PreviousTransactionResult->Message[0]);
			}
			
			if ($nPreviousStatusCode != null &&
				!IRC_SharedFunctions::isStringNullOrEmpty($szPreviousMessage))
			{
				$ptdPreviousTransactionResult = new IRC_PreviousTransactionResult($nPreviousStatusCode, $szPreviousMessage);		
			}

			$pmgoPaymentMessageGatewayOutput = new IRC_PaymentMessageGatewayOutput($goGatewayOutput->getStatusCode(),
																				   $goGatewayOutput->getMessage(),
																				   $goGatewayOutput->getPassOutData(),
																				   $boAuthorisationAttempted,
																				   $ptdPreviousTransactionResult,
																				   $goGatewayOutput->getErrorMessages());

	   		return $pmgoPaymentMessageGatewayOutput;
		}
	}
