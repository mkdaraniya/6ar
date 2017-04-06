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
class Bss_MultiStoreViewPricing_Model_Observer {
	public function applyLimitations($observer) {
		if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return;

		$collection = $observer->getCollection();
        $fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);
        if (isset($fromPart['price_index']) && !isset($fromPart['bss_price_table_1'])) {
            $select = $collection->getSelect();

            $tableName = $collection->getTable('catalog_product_entity_decimal');
            $store_id = $collection->getStoreId();
            $minimalExpr = $collection->getConnection()->getCheckSql(
                'bss_price_table_1.value IS NOT NULL',
                'bss_price_table_1.value',
                'bss_price_table_0.value'
            );

            $minimalSpecialExpr = $collection->getConnection()->getCheckSql(
                'bss_price_table_2.value IS NOT NULL',
                'bss_price_table_2.value',
                'bss_price_table_3.value'
            );

            $select->joinLeft(
                array('bss_price_table_0' => $tableName),
                "e.entity_id = bss_price_table_0.entity_id 
                AND bss_price_table_0.attribute_id = (SELECT attribute_id FROM " .
                $collection->getTable('eav_attribute') .
                " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
                AND bss_price_table_0.store_id = 0",
                array()
            );

            $select->joinLeft(
                array('bss_price_table_1' => $tableName),
                "e.entity_id = bss_price_table_1.entity_id 
                AND bss_price_table_1.attribute_id = (SELECT attribute_id FROM " .
                $collection->getTable('eav_attribute') .
                " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
                AND bss_price_table_1.store_id = ".$store_id,
                array(
                    'store_price' => $minimalExpr,
                    'sort_price' => new Zend_Db_Expr('
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
                        ')
                    )
            );

            $select->joinLeft(
                array('bss_price_table_2' => $tableName),
                "e.entity_id = bss_price_table_2.entity_id 
                AND bss_price_table_2.attribute_id = (SELECT attribute_id FROM " .
                $collection->getTable('eav_attribute') .
                " WHERE attribute_code = 'special_price' AND backend_model != '' LIMIT 1)  
                AND bss_price_table_2.store_id = ".$store_id,
                array()
            );

            $select->joinLeft(
                array('bss_price_table_3' => $tableName),
                "e.entity_id = bss_price_table_3.entity_id 
                AND bss_price_table_3.attribute_id = (SELECT attribute_id FROM " .
                $collection->getTable('eav_attribute') .
                " WHERE attribute_code = 'special_price' AND backend_model != '' LIMIT 1)  
                AND bss_price_table_3.store_id = 0",
                array()
            );
            
            if (Mage::app()->getStore()->isAdmin()) {
                $customer_id = (int)Mage::app()->getRequest()->getParam('customer_id');
                if($customer == 0) {
                    $groupId = 0;
                }else {
                    $groupId = (int)Mage::getModel('customer/customer')->load($customer_id)->getCustomerGroupId();
                }
            }else {
                $groupId = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
            }
            
            $storeDate = Mage::app()->getLocale()->storeTimeStamp($store_id);
            $date = $collection->getResource()->formatDate($storeDate, false);

            $select->joinLeft(
                array('bss_rule_price_table' => $collection->getTable('multistoreviewpricing/rule_product_price')),
                "e.entity_id = bss_rule_price_table.product_id 
                AND bss_rule_price_table.rule_date = '". $date ."' 
                AND bss_rule_price_table.customer_group_id = ". $groupId ."  
                AND bss_rule_price_table.store_id = ".$store_id,
                array()
            );

            $select->joinLeft(
                array('bss_tier_price_table' => $collection->getTable('multistoreviewpricing/product_index_tier_price')),
                "e.entity_id = bss_tier_price_table.entity_id 
                AND bss_tier_price_table.customer_group_id = ". $groupId ."  
                AND bss_tier_price_table.store_id = ".$store_id,
                array()
            );

            $tier_default = Mage::helper('multistoreviewpricing')->getTierPriceOption($store_id);
            if($tier_default == 0) {
                $select->joinLeft(
                    array('bss_tier_price_default_table' => $collection->getTable('multistoreviewpricing/tierDefault')),
                    "e.entity_id = bss_tier_price_default_table.product_id 
                    AND bss_tier_price_default_table.store_id = ".$store_id,
                    array()
                );
            }


            $fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

            $price_index = $fromPart['price_index'];
            $columnsPart = $collection->getSelect()->getPart(Zend_Db_Select::COLUMNS);
            foreach ($columnsPart as $key => $part) {
                if ($part[0] == 'price_index') {
                    if ($part[1] == 'price') {
                        $part[2] = $part[1];
                        $part[1] = new Zend_Db_Expr(
                            'IF(bss_price_table_1.value IS NOT NULL, bss_price_table_1.value, bss_price_table_0.value)'
                        );
                        $columnsPart[$key] = $part;
                    } elseif ($part[1] == 'min_price' || $part[1] == 'final_price' || $part[1] == 'max_price') {
                        $part[2] = $part[1];
                        $part[1] = new Zend_Db_Expr('
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
                                    ) , POW(10,13)
                                ),
                                IF(
                                    bss_rule_price_table.rule_price IS NOT NULL,
                                    bss_rule_price_table.rule_price ,
                                    POW(10,13)
                                )
                            )
                            ');
                        $columnsPart[$key] = $part;
                    } elseif ($part[2] == 'minimal_price') {
                        if($tier_default == 1) {
                            $part[1] = new Zend_Db_Expr('
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
                                        price_index.tier_price IS NOT NULL,
                                        price_index.tier_price ,
                                        POW(10,13)
                                    ),
                                    IF(
                                        bss_rule_price_table.rule_price IS NOT NULL,
                                        bss_rule_price_table.rule_price ,
                                        POW(10,13)
                                    ),
                                    IF(
                                        bss_tier_price_table.min_price IS NOT NULL,
                                        bss_tier_price_table.min_price ,
                                        POW(10,13)
                                    )
                                )');
                        }else {
                            $part[1] = new Zend_Db_Expr('
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
                                        price_index.tier_price IS NOT NULL,
                                        price_index.tier_price ,
                                        POW(10,13)
                                    ),
                                    IF(
                                        bss_rule_price_table.rule_price IS NOT NULL,
                                        bss_rule_price_table.rule_price ,
                                        POW(10,13)
                                    ),
                                    IF(
                                        bss_tier_price_default_table.status IS NULL OR bss_tier_price_default_table.status = 1,
                                        price_index.tier_price ,
                                        bss_tier_price_table.min_price
                                    )
                                )');
                        }


                        $columnsPart[$key] = $part;
                    }
                }
            }
            
            $select->setPart(Zend_Db_Select::FROM, $fromPart);
            $select->setPart(Zend_Db_Select::COLUMNS, $columnsPart);

            // echo $select->__toString();die;
        }

        return $this;
	}

	public function rendererAttributes($observer) {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return;
        
		$form = $observer->getEvent()->getForm();
		$tierPrice = $form->getElement('tier_price_for_store');
		$groupPrice = $form->getElement('group_price_for_store');
		if ($tierPrice) {
			if(Mage::registry('current_product') && Mage::registry('current_product')->getTypeId() == 'bundle') {
				$tierPrice->setRenderer(
					Mage::app()->getLayout()->createBlock('multistoreviewpricing/catalog_product_edit_tab_price_tier')
					->setPriceColumnHeader(Mage::helper('bundle')->__('Percent Discount'))
					);
			}else {
				$tierPrice->setRenderer(
					Mage::app()->getLayout()->createBlock('multistoreviewpricing/catalog_product_edit_tab_price_tier')
					);
			}
		}

		if ($groupPrice) {
			if(Mage::registry('current_product') && Mage::registry('current_product')->getTypeId() == 'bundle') {
				$groupPrice->setRenderer(
					Mage::app()->getLayout()->createBlock('multistoreviewpricing/catalog_product_edit_tab_price_group')
					->setPriceColumnHeader(Mage::helper('bundle')->__('Percent Discount'))
					);
			}else {
				$groupPrice->setRenderer(
					Mage::app()->getLayout()->createBlock('multistoreviewpricing/catalog_product_edit_tab_price_group')
					);
			}
		}
	}

	public function saveProductAfter($observer) {
        if(Mage::helper('multistoreviewpricing')->isScopePrice() == 0) return;

		$product = $observer->getEvent()->getProduct();
		$store = Mage::app()->getRequest()->getParam('store',0);
		if ($store > 0 && $product && $product->getId() > 0) {

			$default_value_tier = Mage::app()->getRequest()->getParam('tier_price_store_view_default',0);
			$model = Mage::getModel('multistoreviewpricing/tierDefault')->getCollection()
			->addFieldToSelect('*')
			->addFieldToFilter('product_id', $product->getId())
			->addFieldToFilter('store_id', $store)
			->getFirstItem();

			$model->setProductId($product->getId())
			->setStoreId($store)
			->setStatus($default_value_tier)
			->save();
		}
	}
}
