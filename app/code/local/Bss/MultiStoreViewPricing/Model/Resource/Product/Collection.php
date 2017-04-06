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
class Bss_MultiStoreViewPricing_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Add tier price data to loaded items
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addTierPriceData()
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) {
            return parent::addTierPriceData();
        }

        foreach ($this->getItems() as $item) {
            $item->setData('tier_price', null);
        }

        return $this;
    }

	/**
     * Prepare additional price expression sql part
     *
     * @param Varien_Db_Select $select
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _preparePriceExpressionParameters($select)
    {
    	if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) {
    		return parent::_preparePriceExpressionParameters($select);
    	}
    	
        // prepare response object for event
        $response = new Varien_Object();
        $response->setAdditionalCalculations(array());
        $tableAliases = array_keys($select->getPart(Zend_Db_Select::FROM));
        if (in_array(self::INDEX_TABLE_ALIAS, $tableAliases)) {
            $table = self::INDEX_TABLE_ALIAS;
        } else {
            $table = reset($tableAliases);
        }

        // prepare event arguments
        $eventArgs = array(
            'select'          => $select,
            'table'           => $table,
            'store_id'        => $this->getStoreId(),
            'response_object' => $response
        );

        Mage::dispatchEvent('catalog_prepare_price_select', $eventArgs);

        $additional   = join('', $response->getAdditionalCalculations());
        $this->_priceExpression = new Zend_Db_Expr('
                        LEAST(
                            IF(
                                bss_price_table_1.value IS NOT NULL,
                                bss_price_table_1.value,
                                bss_price_table_0.value
                            ),
                            IF(
                                IF(
                                    bss_price_table_2.value IS NOT NULL,
                                    bss_price_table_2.value,
                                    bss_price_table_3.value
                                ) IS NOT NULL,
                                IF(
                                    bss_price_table_2.value IS NOT NULL,
                                    bss_price_table_2.value,
                                    bss_price_table_3.value
                                ),
                                POW(10,13)
                            ),
                            IF(
                                bss_rule_price_table.rule_price IS NOT NULL,
                                bss_rule_price_table.rule_price ,
                                POW(10,13)
                            )
                        )
                        ');
        $this->_additionalPriceExpression = $additional;
        $this->_catalogPreparePriceSelect = clone $select;

        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) {
            return parent::addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC);
        }

        if ($attribute == 'position') {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
                return $this;
            }
            if ($this->isEnabledFlat()) {
                $this->getSelect()->order("cat_index_position {$dir}");
            }
            // optimize if using cat index
            $filters = $this->_productLimitationFilters;
            if (isset($filters['category_id']) || isset($filters['visibility'])) {
                $this->getSelect()->order('cat_index.position ' . $dir);
            } else {
                $this->getSelect()->order('e.entity_id ' . $dir);
            }

            return $this;
        } elseif($attribute == 'is_saleable'){
            $this->getSelect()->order("is_saleable " . $dir);
            return $this;
        }

        $storeId = $this->getStoreId();
        if ($attribute == 'price' && $storeId != 0) {
            $this->addPriceData();
            $this->getSelect()->order("sort_price {$dir}");

            return $this;
        }

        if ($this->isEnabledFlat()) {
            $column = $this->getEntity()->getAttributeSortColumn($attribute);

            if ($column) {
                $this->getSelect()->order("e.{$column} {$dir}");
            }
            else if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
            }

            return $this;
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            if ($attrInstance && $attrInstance->usesSource()) {
                $attrInstance->getSource()
                    ->addValueSortToCollection($this, $dir);
                return $this;
            }
        }

        return parent::addAttributeToSort($attribute, $dir);
    }
}