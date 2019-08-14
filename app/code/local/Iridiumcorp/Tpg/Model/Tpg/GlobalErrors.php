<?php

class Iridiumcorp_Tpg_Model_Tpg_GlobalErrors
{	
	// error occurred in the processing of the callback from the hosted payment form
	const ERROR_183 = "ERROR 183: The payment result couldn't be verified.";
	
	// error occurred during the processing of the callback from the transparent redirect page
	const ERROR_260 = "ERROR 260: The payment result couldn't be verified.";
	
	// direct integration transaction cannot be completed - problem in the communication with the payment gateway
	const ERROR_261 = "ERROR 261: Couldn't communicate with payment gateway.";
	
	// direct integration 3D Secure transaction couldn't be processed - problem in the communication with the paymwent gateway
	const ERROR_431 = "ERROR 431: Couldn't communicate with payment gateway for 3D Secure transaction.";
	
	// error occurred during the processing of the data in the callback from the 3D Secure Authentication page
	const ERROR_7655 = "ERROR 7655: 3D Secure Validation was not successfull and checkout was cancelled.<br/>Please check your credit card details and try again.";
}
?>