<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Themeone
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Themeone Model
 * 
 * @category    
 * @package     Themeone
 * @author      Developer
 */
class Simi_Themeone_Model_Banner extends Simi_Connector_Model_Banner {

    protected $_website_id = null;

    public function _construct() {
        parent::_construct();
        $this->_init('themeone/banner');
    }

    public function getBannerList() {

        $status=Mage::getStoreConfig('themeone/general/enable');            
        if (!$status){
            return parent::getBannerList();            
        }

        $website_id = Mage::app()->getStore()->getWebsiteId();
        $list = array();
        $collection = $this->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('website_id',array('in' => array($website_id, 0)));
        
        foreach ($collection as $item) {
            $path = Mage::getBaseUrl('media') . 'simi/themeone/banner' . '/' . $item->getWebsiteId() .'/'. $item->getBannerName();
            $categoryName = '';
            $categoryChildrenCount = '';
            if($item->getCategoryId()){
                $category = Mage::getModel('catalog/category')->load($item->getCategoryId());
                $categoryName = $category->getName();
                $categoryChildrenCount = $category->getChildrenCount();
                if($categoryChildrenCount > 0)
                    $categoryChildrenCount = 1;
                else
                    $categoryChildrenCount = 0;
            }            
            $list[] = array(
                'image_path' => $path,
                'url' => $item->getBannerUrl(),
                'type' => $item->getType(),
                'categoryID' => $item->getCategoryId(),
                'categoryName' => $categoryName,
                'productID' => $item->getProductId(),
                'has_child' => $categoryChildrenCount,
            );
        }
    
        return $list;
    }

    public function toOptionArray(){
        $platform = array(
                        '1' => Mage::helper('connector')->__('Product In-app'), 
                        '2' => Mage::helper('connector')->__('Category In-app'), 
                        '3' => Mage::helper('connector')->__('Website Page'), 
                    );
        return $platform;
    }

}