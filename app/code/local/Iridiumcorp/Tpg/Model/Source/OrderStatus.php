<?php

class Iridiumcorp_Tpg_Model_Source_OrderStatus
{
	public function toOptionArray()
    {
        return array(
        	 // override the order status and ONLY offer "pending" by default 
            array(
                'value' => 'processing',
                'label' => Mage::helper('tpg')->__('Processing')
            ),
        );
    }
}