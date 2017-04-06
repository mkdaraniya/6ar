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
 * Ztheme Edit Block
 * 
 * @category    
 * @package     Ztheme
 * @author      Developer
 */
class Simi_Ztheme_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'banner_id';
        $this->_blockGroup = 'ztheme';
        $this->_controller = 'adminhtml_banner';
        
        $this->_updateButton('save', 'label', Mage::helper('ztheme')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('ztheme')->__('Delete Banner'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('ztheme_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'ztheme_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'ztheme_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::app()->getRequest()->getParam('banner_id') || (Mage::registry('ztheme_data')
            && Mage::registry('ztheme_data')->getId()
        )) {
            return Mage::helper('ztheme')->__("Edit Banner");
        }
        return Mage::helper('ztheme')->__('Add Banner');
    }
}