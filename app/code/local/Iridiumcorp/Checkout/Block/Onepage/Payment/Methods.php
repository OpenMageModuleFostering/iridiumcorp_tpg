<?php

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Iridiumcorp_Checkout_Block_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    /**
     * Override the base function - by default the Iridium payment option will be selected
     *
     * @return mixed
     */
    public function getSelectedMethodCode()
    {
        if ($this->getQuote()->getPayment()->getMethod())
        {
            $method = $this->getQuote()->getPayment()->getMethod();
        }
        else 
        {
        	// force the current payment to be selected
        	$method = 'tpg';
        }
        
        return $method;
    }
}
