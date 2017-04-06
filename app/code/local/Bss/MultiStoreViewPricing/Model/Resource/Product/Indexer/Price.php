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
class Bss_MultiStoreViewPricing_Model_Resource_Product_Indexer_Price extends Mage_Catalog_Model_Resource_Product_Indexer_Price
{
	/**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Price
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return parent::_prepareTierPriceIndex($entityIds);
        
        $write = $this->_getWriteAdapter();
        $table = $this->getTable('multistoreviewpricing/product_index_tier_price');
        $write->delete($table);
        $select = $write->select()
            ->from(
                array('tp' => $this->getTable('multistoreviewpricing/tier_price')),
                array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
                array('customer_group_id'))
            ->join(
                array('cw' => $this->getTable('core/store')),
                'tp.store_id = cw.store_id',
                array('store_id'))
            ->where('tp.store_id != 0')
            ->columns(new Zend_Db_Expr("MIN(tp.value)"))
            ->group(array('tp.entity_id', 'tp.customer_group_id', 'tp.store_id'));

        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return parent::_prepareTierPriceIndex($entityIds);
    }
}
