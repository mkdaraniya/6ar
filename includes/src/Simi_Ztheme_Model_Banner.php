<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Ztheme
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Ztheme Model
 * 
 * @category    
 * @package     Ztheme
 * @author      Developer
 */
class Simi_Ztheme_Model_Banner extends Simi_Ztheme_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('ztheme/banner');
    }

	
    public function getBanners($data, $phone_type) {
   
        $list = $this->getListBanner($data, $phone_type);
        
        if (count($list)) {
            $information = $this->statusSuccess();
            $information['data'] = $list;
            return $information;
        } else {
            $information = $this->statusError();
            return $information;
        }
    }
    
    public function getBannersAndSpot($data,$phone_type) {
        $bannerList = $this->getListBanner($data, $phone_type);
        $spotList = Mage::getModel('ztheme/spotproduct')->getSpotList($data,$phone_type);
        $bannerAndSpot = array_merge($bannerList, $spotList);
        
        if (count($bannerAndSpot)) {
            $information = $this->statusSuccess();
            $information['data'] = $bannerAndSpot;
            return $information;
        } else {
            $information = $this->statusError();
            return $information;
        }
    }

    public function getListBanner($data, $phone_type) {
        $storeview_id = Mage::app()->getStore()->getId();
        $list = array();
        $collection = $this->getCollection()
                ->addFieldToFilter('status', 1)
				->addFieldToFilter('banner_content','1')
                ->setOrder('banner_position', 'ASC')
                ->addFieldToFilter('website_id', $storeview_id);
        $storeId = Mage::app()->getStore()->getId();    
        $block =  Mage::app()->getLayout()->getBlockSingleton('page/html_topmenu');        
        foreach ($collection as $item) {

            $categoryId = $item->getCategoryId();
            $cat = Mage::getModel('catalog/category')->load($categoryId);            
            
            //child cats layer 1
            $childCatsLayer1 = array();
            foreach (explode(',', $cat->getChildren()) as $subCatid) {
                $_category = Mage::getModel('catalog/category')->load($subCatid);  				
                if ($_category->getIsActive()) {
                    $subCategoryLayer1 = array();
                    $subCategoryLayer1['category_id'] = $subCatid;
                    $subCategoryLayer1['category_name'] = $_category->getName();
					/*
					if Exist Sub cat banner
					*/
					$subCatBanner = Mage::getModel('ztheme/banner')->getCollection()
					->addFieldToFilter('banner_content','2')
					->addFieldToFilter('status', 1)
					->addFieldToFilter('category_id',$subCatid)
					->addFieldToFilter('website_id', $storeview_id)
					->getFirstItem();
					$subCatBannerTitle = '';
					$subCategoryLayer1['banner_position'] = 99999;
					if ($subCatBanner->getId()){
						$path = '';
						if (($subCatBanner->getBannerName()) && ($subCatBanner->getBannerName() != ''))
							$path = Mage::getBaseUrl('media') . 'simi/ztheme/banner' . '/' . $subCatBanner->getBannerName();
						if (($phone_type == 'tablet') && ($subCatBanner->getBannerNameTablet()) && ($subCatBanner->getBannerNameTablet() != ''))
							$path = Mage::getBaseUrl('media') . 'simi/ztheme/banner_tab' . '/' . $subCatBanner->getBannerNameTablet();
						$subCategoryLayer1['category_image'] = $path;
						if (($this->getConfig("show_title") != 0) &&($subCatBanner->getBannerTitle()))
							$subCatBannerTitle = $subCatBanner->getBannerTitle();
						$subCategoryLayer1['banner_position'] = $subCatBanner->getData('banner_position');
						$subCategoryLayer1['banner_id'] = $subCatBanner->getId();
					}
					$subCategoryLayer1['title'] = $subCatBannerTitle;
					$subCategoryLayer1['type'] = 'cat';
						
                    if ($_category->hasChildren()) {
                        $subCategoryLayer1['has_child'] = 'YES';
						$childCatsLayer2 = array();
						
						/*
						Child cats layer 2 
						*/
						foreach ($_category->getChildrenCategories() as $childCatLayer2) {
							$subCategoryLayer2 = array();
							$subCategoryLayer2['category_id'] = $childCatLayer2->getId();
							$subCategoryLayer2['category_name'] = $childCatLayer2->getName();
							if ($childCatLayer2->hasChildren()) 
								$subCategoryLayer2['has_child'] = 'YES';
							else 
								$subCategoryLayer2['has_child'] = 'NO';
							$subCategoryLayer2['banner_id'] = $childCatLayer2->getId();
							$childCatsLayer2[] = $subCategoryLayer2;
							
						}
						$subCategoryLayer1['child_cat'] = $childCatsLayer2;
					}
                    else
                        $subCategoryLayer1['has_child'] = 'NO';
					
					
                    $childCatsLayer1[] = $subCategoryLayer1;
                }
            }

			//sort
			$sortArray = array();
			foreach ($childCatsLayer1 as $subItem) {
				$sortArray[$subItem['category_id']] = $subItem['banner_position'];
			}
			
			asort($sortArray);
			$newArray = array();
			foreach ($sortArray as $index=>$sorItem) {
				foreach ($childCatsLayer1 as $subItem) {
					if ($subItem['category_id'] == $index)
						$newArray[] = $subItem;
				}
			}
			$childCatsLayer1 = $newArray;
			

			
			
            //has child
            if ($cat->hasChildren())
                $hasChild = 'YES';
            else
                $hasChild = 'NO';
            
            //image
            $path = '';
            if (($item->getBannerName()) && ($item->getBannerName() != ''))
                $path = Mage::getBaseUrl('media') . 'simi/ztheme/banner' . '/' . $item->getBannerName();
            if (($phone_type == 'tablet') && ($item->getBannerNameTablet()) && ($item->getBannerNameTablet() != ''))
                $path = Mage::getBaseUrl('media') . 'simi/ztheme/banner_tab' . '/' . $item->getBannerNameTablet();
            
            //title
            $title = '';
            if (($this->getConfig("show_title") != 0) &&($item->getBannerTitle()))
                $title = $item->getBannerTitle();
                
            $list[] = array(
                'type' => 'cat',
                'category_image' => $path,
                'category_id' => $categoryId,
                'category_name' => $cat->getName(),
                'has_child' => $hasChild,
				'banner_id' => $item->getId(),
                'child_cat' => $childCatsLayer1,
                'title' => $title,
            );
        }
        return $list;
    }
    

}
