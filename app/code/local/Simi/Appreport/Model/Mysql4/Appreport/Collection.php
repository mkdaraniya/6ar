<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Appreport
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Appreport Resource Collection Model
 * 
 * @category    
 * @package     Appreport
 * @author      Developer
 */
class Simi_Appreport_Model_Mysql4_Appreport_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('appreport/appreport');
    }
}