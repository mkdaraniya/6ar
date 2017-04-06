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
class Bss_MultiStoreViewPricing_Model_Catalog_Resource_Product_Option_Value extends Mage_Catalog_Model_Resource_Product_Option_Value
{
    protected function _saveValuePrices(Mage_Core_Model_Abstract $object)
    {
        $scope = (int)Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
        if($scope != 2) {
            return parent::_saveValuePrices($object);
        }

        $priceTable = $this->getTable('catalog/product_option_type_price');

        $price      = (float)sprintf('%F', $object->getPrice());
        $priceType  = $object->getPriceType();

        $store = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        if(Mage::app()->getRequest()->getParam('store') && Mage::app()->getRequest()->getParam('store') != '') {
            $store = Mage::app()->getRequest()->getParam('store');
        }
        
        if (!$object->getData('scope', 'price')) {
            //save for store_id = 0
            $select = $this->_getReadAdapter()->select()
                ->from($priceTable, 'option_type_id')
                ->where('option_type_id = ?', (int)$object->getId())
                ->where('store_id = ?', $store);
            $optionTypeId = $this->_getReadAdapter()->fetchOne($select);

            if ($optionTypeId) {
                if ($object->getStoreId() == '0') {
                    $bind  = array(
                        'price'         => $price,
                        'price_type'    => $priceType
                    );
                    $where = array(
                        'option_type_id = ?'    => $optionTypeId,
                        'store_id = ?'          => $store
                    );

                    $this->_getWriteAdapter()->update($priceTable, $bind, $where);
                }
            } else {
                $bind  = array(
                    'option_type_id'    => (int)$object->getId(),
                    'store_id'          => $store,
                    'price'             => $price,
                    'price_type'        => $priceType
                );
                $this->_getWriteAdapter()->insert($priceTable, $bind);
            }
        }

        //$scope = (int)Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);

        if ($object->getStoreId() != '0' && $scope == 2 && !$object->getData('scope', 'price')) {
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $storeId = $object->getStoreId();
            if ($priceType == 'fixed') {
                $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($storeCurrency);
                if (!$rate) {
                    $rate = 1;
                }
                $newPrice = $price * $rate;
            } else {
                $newPrice = $price;
            }

            $select = $this->_getReadAdapter()->select()
                ->from($priceTable, 'option_type_id')
                ->where('option_type_id = ?', (int)$object->getId())
                ->where('store_id = ?', (int)$storeId);
            $optionTypeId = $this->_getReadAdapter()->fetchOne($select);

            if ($optionTypeId) {
                $bind  = array(
                    'price'         => $newPrice,
                    'price_type'    => $priceType
                );
                $where = array(
                    'option_type_id = ?'    => (int)$optionTypeId,
                    'store_id = ?'          => (int)$storeId
                );

                $this->_getWriteAdapter()->update($priceTable, $bind, $where);
            } else {
                $bind  = array(
                    'option_type_id'    => (int)$object->getId(),
                    'store_id'          => (int)$storeId,
                    'price'             => $newPrice,
                    'price_type'        => $priceType
                );

                $this->_getWriteAdapter()->insert($priceTable, $bind);
            }
        }else if ($scope == 2 && $object->getData('scope', 'price')) {
            $where = array(
                'option_type_id = ?'    => (int)$object->getId(),
                'store_id = ?'          => (int)$object->getStoreId(),
            );
            $this->_getWriteAdapter()->delete($priceTable, $where);
        }

    }

}
        