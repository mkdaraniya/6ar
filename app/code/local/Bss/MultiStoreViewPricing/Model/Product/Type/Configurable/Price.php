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
class Bss_MultiStoreViewPricing_Model_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
	public function getTierPrice($qty = null, $product)
	{
		$allGroups = Mage_Customer_Model_Group::CUST_GROUP_ALL;
        // $prices = $product->getData('tier_price');

        // if (is_null($prices)) {
		$attribute = $product->getResource()->getAttribute('tier_price');
		if ($attribute) {
			$attribute->getBackend()->afterLoad($product);
			$prices = $product->getData('tier_price');
		}
        // }

		if (is_null($prices) || !is_array($prices)) {
			if (!is_null($qty)) {
				return $product->getPrice();
			}
			return array(array(
				'price'         => $product->getPrice(),
				'website_price' => $product->getPrice(),
				'price_qty'     => 1,
				'cust_group'    => $allGroups,
				));
		}

		$custGroup = $this->_getCustomerGroupId($product);
		if ($qty) {
			$prevQty = 1;
			$prevPrice = $product->getPrice();
			$prevGroup = $allGroups;

			foreach ($prices as $price) {
				if ($price['cust_group']!=$custGroup && $price['cust_group']!=$allGroups) {
                    // tier not for current customer group nor is for all groups
					continue;
				}
				if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
					continue;
				}
				if ($price['price_qty'] < $prevQty) {
                    // higher tier qty already found
					continue;
				}
				if ($price['price_qty'] == $prevQty && $prevGroup != $allGroups && $price['cust_group'] == $allGroups) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
					continue;
				}
				if ($price['website_price'] < $prevPrice) {
					$prevPrice  = $price['website_price'];
					$prevQty    = $price['price_qty'];
					$prevGroup  = $price['cust_group'];
				}
			}
			return $prevPrice;
		} else {
			$qtyCache = array();
			foreach ($prices as $i => $price) {
				if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
					unset($prices[$i]);
				} else if (isset($qtyCache[$price['price_qty']])) {
					$j = $qtyCache[$price['price_qty']];
					if ($prices[$j]['website_price'] > $price['website_price']) {
						unset($prices[$j]);
						$qtyCache[$price['price_qty']] = $i;
					} else {
						unset($prices[$i]);
					}
				} else {
					$qtyCache[$price['price_qty']] = $i;
				}
			}
		}

		return ($prices) ? $prices : array();
	}
}