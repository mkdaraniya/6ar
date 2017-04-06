<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Loyalty
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Loyalty Controller
 * 
 * @category    
 * @package     Loyalty
 * @author      Developer
 */
class Magestore_Loyalty_IndexController extends Mage_Core_Controller_Front_Action
{
    public function checkInstallAction(){
       echo "1";
        exit();
    }

    public function installDbAction()
    {
        $setup = new Mage_Core_Model_Resource_Setup('core_setup');
        $installer = $setup;
        $installer->startSetup();
        $installer->run("

            DROP TABLE IF EXISTS {$installer->getTable('rewardpoints/transaction')};
            DROP TABLE IF EXISTS {$installer->getTable('rewardpoints/rate')};
            DROP TABLE IF EXISTS {$installer->getTable('rewardpoints/customer')};

            CREATE TABLE {$installer->getTable('rewardpoints/customer')} (
              `reward_id` int(10) unsigned NOT NULL auto_increment,
              `customer_id` int(10) unsigned NOT NULL,
              `point_balance` int(11) NOT NULL default '0',
              `holding_balance` int(11) NOT NULL default '0',
              `spent_balance` int(11) NOT NULL default '0',
              `is_notification` smallint(5) NOT NULL,
              `expire_notification` smallint(5) NOT NULL,
              PRIMARY KEY (`reward_id`),
              KEY `FK_REWARDPOINTS_CUSTOMER_ID` (`customer_id`),
              CONSTRAINT `FK_REWARDPOINTS_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('customer/entity')}(`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE {$installer->getTable('rewardpoints/rate')} (
              `rate_id` int(10) unsigned NOT NULL auto_increment,
              `website_ids` text NULL,
              `customer_group_ids` text NULL,
              `direction` smallint(5) NOT NULL,
              `points` int(11) NOT NULL default '0',
              `money` decimal(12,4) NOT NULL default '0',
              `sort_order` int(11) NOT NULL,
              PRIMARY KEY (`rate_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE {$installer->getTable('rewardpoints/transaction')} (
              `transaction_id` int(10) unsigned NOT NULL auto_increment,
              `reward_id` int(10) unsigned NULL,
              `customer_id` int(10) unsigned NULL,
              `customer_email` varchar(255) NOT NULL,
              `title` varchar(255) NOT NULL,
              `action` varchar(63) NOT NULL,
              `action_type` smallint(5) NOT NULL default '0',
              `store_id` smallint(5) NOT NULL,
              `point_amount` int(11) NOT NULL default '0',
              `point_used` int(11) NOT NULL default '0',
              `real_point` int(11) NOT NULL default '0',
              `status` smallint(5) NOT NULL,
              `created_time` datetime NULL,
              `updated_time` datetime NULL,
              `expiration_date` datetime NULL,
              `expire_email` smallint(5) NOT NULL default '0',
              `order_id` int(10) unsigned NULL,
              `order_increment_id` varchar(63) NULL,
              `order_base_amount` decimal(12,4) NULL,
              `order_amount` decimal(12,4) NULL,
              `base_discount` decimal(12,4) NULL,
              `discount` decimal(12,4) NULL,
              `extra_content` text NULL,
              PRIMARY KEY (`transaction_id`),
              KEY `FK_REWARDPOINTS_TRANS_REWARD_ID` (`reward_id`),
              KEY `FK_REWARDPOINTS_TRANS_CUSTOMER_ID` (`customer_id`),
              CONSTRAINT `FK_REWARDPOINTS_TRANS_REWARD_ID` FOREIGN KEY (`reward_id`) REFERENCES {$installer->getTable('rewardpoints/customer')} (`reward_id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `FK_REWARDPOINTS_TRANS_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('rewardpoints/customer')} (`customer_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

			");

        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_spent', 'int(11) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_discount', 'decimal(12,4) NOT NULL default 0');

        $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'rewardpoints_spent', 'int(11) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'rewardpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'rewardpoints_discount', 'decimal(12,4) NOT NULL default 0');

        $installer->getConnection()->addColumn($installer->getTable('sales/invoice'), 'rewardpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/invoice'), 'rewardpoints_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/creditmemo'), 'rewardpoints_base_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/creditmemo'), 'rewardpoints_discount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('rewardpoints/rate'), 'max_price_spended_type', 'VARCHAR(15) NULL');
        $installer->getConnection()->addColumn($installer->getTable('rewardpoints/rate'), 'max_price_spended_value', 'DECIMAL(12,4) NULL');

        if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')) {
            $installer->getConnection()->addColumn($installer->getTable('sales/invoice'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
            $installer->getConnection()->addColumn($installer->getTable('sales/creditmemo'), 'rewardpoints_earn', 'int(11) NOT NULL default 0');
        } else {
            $setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
            $setup->addAttribute('invoice', 'rewardpoints_earn', array('type' => 'ịnt'));
            $setup->addAttribute('creditmemo', 'rewardpoints_earn', array('type' => 'int'));
        }

        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_base_amount', 'decimal(12,4) NOT NULL default 0');
        $installer->getConnection()->addColumn($installer->getTable('sales/order'), 'rewardpoints_amount', 'decimal(12,4) NOT NULL default 0');

        $installer=new  Magestore_RewardPoints_Model_Mysql4_Setup();
        $installer->updateRewardPointsConfig()
            ->updateRewardPointsRate()
            ->updateRewardPointsCustomer()
            ->updateRewardPointsTransaction()
            ->updateRewardPointsOrders()
            ->updateRewardPointsOrderTable();
        $installer->endSetup();
        echo "success";

    }
}
