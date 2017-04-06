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
class Bss_MultiStoreViewPricing_Model_Catalogrule extends Mage_CatalogRule_Model_Rule {
	public function calcProductPriceRule(Mage_Catalog_Model_Product $product, $price)
	{
		$priceRules = null;
		$productId  = $product->getId();
		$storeId    = $product->getStoreId();
		$websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
		if ($product->hasCustomerGroupId()) {
			$customerGroupId = $product->getCustomerGroupId();
		} else {
			$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		}
		$dateTs     = Mage::app()->getLocale()->date()->getTimestamp();
		$cacheKey   = date('Y-m-d', $dateTs) . "|$websiteId|$customerGroupId|$productId|$price";

		if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
			$rulesData = $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
			if ($rulesData) {
				foreach ($rulesData as $ruleData) {
					if ($product->getParentId()) {
						if (!empty($ruleData['sub_simple_action'])) {
							$priceRules = Mage::helper('catalogrule')->calcPriceRule(
								$ruleData['sub_simple_action'],
								$ruleData['sub_discount_amount'],
								$priceRules ? $priceRules : $price
								);
						} else {
							$priceRules = ($priceRules ? $priceRules : $price);
						}
						if ($ruleData['action_stop']) {
							break;
						}
					} else {
						$priceRules = Mage::helper('catalogrule')->calcPriceRule(
							$ruleData['action_operator'],
							$ruleData['action_amount'],
							$priceRules ? $priceRules : $price
							);
						if ($ruleData['action_stop']) {
							break;
						}
					}
				}
				return self::$_priceRulesData[$cacheKey] = $priceRules;
			} else {
				self::$_priceRulesData[$cacheKey] = $price;
			}
		} else {
			return self::$_priceRulesData[$cacheKey];
		}
		return $price;
	}


	public function calcProductPriceRule15(Mage_Catalog_Model_Product $product, $price)
	{
		$priceRules      = null;
		$productId       = $product->getId();
		$storeId         = $product->getStoreId();
		$websiteId       = Mage::app()->getStore($storeId)->getWebsiteId();
		$customerGroupId = null;
		if ($product->hasCustomerGroupId()) {
			$customerGroupId = $product->getCustomerGroupId();
		} else {
			$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		}
		$dateTs          = Mage::app()->getLocale()->storeTimeStamp($storeId);
		$cacheKey        = date('Y-m-d', $dateTs)."|$websiteId|$customerGroupId|$productId|$price";

		if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
			$rulesData = $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
			if ($rulesData) {
				foreach ($rulesData as $ruleData) {
					$priceRules = Mage::helper('catalogrule')->calcPriceRule(
						$ruleData['simple_action'],
						$ruleData['discount_amount'],
						$priceRules ? $priceRules :$price);
					if ($ruleData['stop_rules_processing']) {
						break;
					}
				}
				return self::$_priceRulesData[$cacheKey] = $priceRules;
			} else {
				self::$_priceRulesData[$cacheKey] = null;
			}
		} else {
			return self::$_priceRulesData[$cacheKey];
		}
		return $price;
	}
}