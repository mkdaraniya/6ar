<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Rewardpoints
 * @copyright   Copyright (c) 2012 
 * @license     
 */
/** @var $installer Magestore_RewardPointsRule_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
    $installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
} else {
    $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $setup->addAttribute('invoice', 'rewardpoints_earn', array('type' => 'ịnt'));    
    $setup->addAttribute('creditmemo', 'rewardpoints_earn', array('type' => 'int'));
}

$installer->endSetup();
