<?php

class Iridiumcorp_Tpg_Model_Source_ResultDeliveryMethod
{
	// public enum for the payment types
	const RESULT_DELIVERY_METHOD_POST = 'POST';
	const RESULT_DELIVERY_METHOD_SERVER = 'SERVER';

	public function toOptionArray()
    {
        return array
        (
            array(
                'value' => self::RESULT_DELIVERY_METHOD_POST,
                'label' => Mage::helper('tpg')->__('Post')
            ),
            array(
                'value' => self::RESULT_DELIVERY_METHOD_SERVER,
                'label' => Mage::helper('tpg')->__('Server')
            ),
        );
    }
}