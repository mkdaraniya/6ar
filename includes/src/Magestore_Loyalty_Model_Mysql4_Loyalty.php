<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Loyalty
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Loyalty Model
 * 
 * @category    
 * @package     Loyalty
 * @author      Developer
 */
class Magestore_Loyalty_Model_Mysql4_Loyalty extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('loyalty/loyalty', 'loyalty_id');
    }
}