<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

Mage::log('iridiumcorp installer script started');

try
{
	// try to run the installation script
	$installer->run("
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_failed_hosted_payment');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_failed_threed_secure');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_paid');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_pending');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_pending_hosted_payment');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_pending_threed_secure');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_refunded');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_voided');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_preauth');
	DELETE FROM `{$installer->getTable('sales_order_status')}` WHERE (`status`='irc_collected');
	
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_failed_hosted_payment', 'Iridiumcorp - Failed Payment');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_failed_threed_secure', 'Iridiumcorp - Failed 3D Secure');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_paid', 'Iridiumcorp - Successful Payment');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_pending', 'Iridiumcorp - Pending Hosted Payment');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_pending_hosted_payment', 'Iridiumcorp - Pending Hosted Payment');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_pending_threed_secure', 'Iridiumcorp - Pending 3D Secure');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_refunded', 'Iridiumcorp - Payment Refunded');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_voided', 'Iridiumcorp - Payment Voided');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_preauth', 'Iridiumcorp - PreAuthorized');
	INSERT INTO `{$installer->getTable('sales_order_status')}` (`status`, `label`) VALUES ('irc_collected', 'Iridiumcorp - Payment Collected');
	");
}
catch(Exception $exc)
{
	Mage::log("Error during script installation: ". $exc->__toString());
}

Mage::log('iridiumcorp installer script ended');

$installer->endSetup();