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
 * Simibarcode Adminhtml Block
 * 
 * @category 	
 * @package 	Simibarcode
 * @author  	Developer
 */
class Simi_Simibarcode_Block_Adminhtml_Printqrcode extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_printqrcode';
        $this->_blockGroup = 'simibarcode';
        $this->_headerText = Mage::helper('simibarcode')->__('Print QR codes');
        
        parent::__construct();       
    }
    
}