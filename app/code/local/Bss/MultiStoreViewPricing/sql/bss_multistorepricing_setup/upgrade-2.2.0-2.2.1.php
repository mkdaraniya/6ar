<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_MultiStoreViewPricing
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'catalog/product_index_tier_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('multistoreviewpricing/product_index_tier_price'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity ID')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Customer Group ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store ID')
    ->addColumn('min_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Min Price')
    ->addIndex($installer->getIdxName('multistoreviewpricing/product_index_tier_price', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('multistoreviewpricing/product_index_tier_price', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(
            'multistoreviewpricing/product_index_tier_price',
            'customer_group_id',
            'customer/customer_group',
            'customer_group_id'
        ),
        'customer_group_id', $installer->getTable('customer/customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'multistoreviewpricing/product_index_tier_price',
            'entity_id',
            'catalog/product',
            'entity_id'
        ),
        'entity_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'multistoreviewpricing/product_index_tier_price',
            'store_id',
            'core/store',
            'store_id'
         ),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Catalog Product Tier Price Index Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
