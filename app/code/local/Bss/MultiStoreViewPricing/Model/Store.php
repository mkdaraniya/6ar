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
class Bss_MultiStoreViewPricing_Model_Store extends Mage_Core_Model_Store
{
	public function getBaseCurrencyCode()
    {
        $configValue = $this->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        if ($configValue == Mage_Core_Model_Store::PRICE_SCOPE_GLOBAL) {
            return Mage::app()->getBaseCurrencyCode();
        } elseif ($configValue == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
            return $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        }else {
            return $this->getConfig('currency/options/default');
        }
    }
}