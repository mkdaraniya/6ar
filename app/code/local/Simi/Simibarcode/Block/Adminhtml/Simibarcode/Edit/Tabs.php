<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Simibarcode
 * @copyright   Copyright (c) 2012 
 * @license     
 */

 /**
 * Simibarcode Edit Tabs Block
 * 
 * @category    
 * @package     Simibarcode
 * @author      Developer
 */
class Simi_Simibarcode_Block_Adminhtml_Simibarcode_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
    {
		parent::__construct();
		$this->setId('simibarcode_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('simibarcode')->__('Barcode'));
	}
	
	/**
	 * prepare before render block to html
	 *
	 * @return Simi_Simibarcode_Block_Adminhtml_Simibarcode_Edit_Tabs
	 */
	protected function _beforeToHtml()
    {
		if(!$this->getRequest()->getParam('id')){
            $this->addTab('products_section', array(
                        'label' => Mage::helper('simibarcode')->__('Barcode'),
                        'title' => Mage::helper('simibarcode')->__('Barcode'),
                        'url' => $this->getUrl('*/*/products', array('_current' => true)),
                        'class' => 'ajax',
                    ));
        }else{
            $this->addTab('form_section', array(
                'label' => Mage::helper('simibarcode')->__('Barcode Information'),
                'title' => Mage::helper('simibarcode')->__('Barcode Information'),
                'content' => $this->getLayout()
                        ->createBlock('simibarcode/adminhtml_simibarcode_edit_tab_form')
                        ->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
	}
}