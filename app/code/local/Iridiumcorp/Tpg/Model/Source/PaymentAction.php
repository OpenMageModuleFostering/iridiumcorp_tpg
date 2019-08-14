<?php

class Iridiumcorp_Tpg_Model_Source_PaymentAction extends Mage_Paygate_Model_Authorizenet_Source_PaymentAction
{
	public function toOptionArray()
    {
        return array
        (
        	 // override the core class to ONLY allow capture transactions (immediate settlement)
            array
            (
            	'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE,
            	'label' => Mage::helper('paygate')->__('PREAUTH')
            ),
        	 array
            (
                'value' => Mage_Paygate_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paygate')->__('SALE')
            )
        );
    }
}