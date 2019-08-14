<?php

class Iridiumcorp_Sales_Model_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    /**
     * Capture invoice
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function capture()
    {
        $this->getOrder()->getPayment()->capture($this);

        // decide whether to pay the invoice
        if($GLOBALS['m_boPayInvoice'])
        {
	        if ($this->getIsPaid())
	        {
	            $this->pay();
	        }
        }
        return $this;
    }
}