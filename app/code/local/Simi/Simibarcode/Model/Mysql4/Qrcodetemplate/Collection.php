<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	
 * @package 	Simibarcode
 * @copyright 	Copyright (c) 2012 
 * @license 	
 */

 /**
 * Simibarcode Model
 * 
 * @category 	
 * @package 	Simibarcode
 * @author  	Developer
 */
class Simi_Simibarcode_Model_Mysql4_Qrcodetemplate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('simibarcode/qrcodetemplate');
    }
}