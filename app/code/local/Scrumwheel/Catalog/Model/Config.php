<?php
/**
 * Created by PhpStorm.
 * User: Scrumwheel
 * Date: 12/28/2016
 * Time: 3:36 PM
 */
class Scrumwheel_Catalog_Model_Config extends Mage_Catalog_Model_Config
{
    public function getAttributeUsedForSortByArray()
    {
        return array_merge(
            parent::getAttributeUsedForSortByArray(),
            array('availability' => Mage::helper('catalog')->__('Availability')),
            array('popularity' => Mage::helper('catalog')->__('Popularity'))
            
        );
    }
}