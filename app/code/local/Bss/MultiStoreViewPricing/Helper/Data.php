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
class Bss_MultiStoreViewPricing_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_PRICE_SCOPE  = 'catalog/price/scope';

	public function isScopePrice() {
		if(Mage::getStoreConfig(self::XML_PATH_PRICE_SCOPE) == 2) {
			return 1;
		}
		return 0;
	}

	public function getTierPriceOption($store = false) {
		if($store) {
			return Mage::getStoreConfig('multistoreviewpricing/general/tier_price', $store);
		}
		return Mage::getStoreConfig('multistoreviewpricing/general/tier_price');
	}

	public function getPriceBundleCategory($bundled_product) {
		$selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
			$bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product
			);
		$min_price_array = array();
		$max_price_array = array();
		foreach ($selectionCollection as $option) {
			if($option->getRequiredOptions() == 0) continue;

			if(!$min_price_array[$option->getOptionId()]) {
				$min_price_array[$option->getOptionId()] = $option->getFinalPrice();
				$max_price_array[$option->getOptionId()] = $option->getFinalPrice();
			}else {
				$min_price_array[$option->getOptionId()] = min($option->getFinalPrice(),$min_price_array[$option->getOptionId()]);
				$max_price_array[$option->getOptionId()] = max($option->getFinalPrice(),$max_price_array[$option->getOptionId()]);
			}
		}
		return array('min' => array_sum($min_price_array) , 'max' => array_sum($max_price_array));
	}

	public function getPriceGroupedCategory($product) {
		$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
		$price = '';
		foreach ($associatedProducts as $key => $product) {
			if($product->isSaleable()) {
				if($price == '') {
					$price = $product->getFinalPrice();
				}else {
					$price = min($price, $product->getFinalPrice());
				}
			}
		}
		return $price;
	}

	public function checkProductAdmin() {
		if(Mage::app()->getRequest()->getModuleName(). ' ' . Mage::app()->getRequest()->getControllerName() == 'admin catalog_product') return true;

		return false;
	}

}
