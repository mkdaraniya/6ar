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
class Simi_Themeone_Model_Spotproduct extends Simi_Connector_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('themeone/spotproduct');
    }
}