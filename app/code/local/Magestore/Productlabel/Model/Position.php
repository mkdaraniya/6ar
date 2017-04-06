<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Productlabel
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Productlabel Model
 * 
 * @category    
 * @package     Productlabel
 * @author      Developer
 */
 class Magestore_Productlabel_Model_Position extends Varien_Object
 {
     /**
     * get model option as array
     *
     * @return array
     */
	static public function getOptionArray()
    {
        return array(
            1   => Mage::helper('productlabel')->__('Top-left'),
            2   => Mage::helper('productlabel')->__('Top-center'),
            3   => Mage::helper('productlabel')->__('Top-right'),
            4   => Mage::helper('productlabel')->__('Middle-left'),
            5   => Mage::helper('productlabel')->__('Middle-center'),
            6   => Mage::helper('productlabel')->__('Middle-right'),
            7   => Mage::helper('productlabel')->__('Bottom-left'),
            8   => Mage::helper('productlabel')->__('Bottom-center'),
            9   => Mage::helper('productlabel')->__('Bottom-right'),
			);
    }
	    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionHash()
    {
        $options = array();
        foreach (self::getOptionArray() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }
}
    