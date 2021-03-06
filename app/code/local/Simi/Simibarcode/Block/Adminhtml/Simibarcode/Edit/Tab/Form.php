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
 * Simibarcode Edit Form Content Tab Block
 * 
 * @category 	
 * @package 	Simibarcode
 * @author  	Developer
 */
class Simi_Simibarcode_Block_Adminhtml_Simibarcode_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * prepare tab form's information
	 *
	 * @return Simi_Simibarcode_Block_Adminhtml_Simibarcode_Edit_Tab_Form
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		if (Mage::getSingleton('adminhtml/session')->getSimibarcodeData()){
			$data = Mage::getSingleton('adminhtml/session')->getSimibarcodeData();
			Mage::getSingleton('adminhtml/session')->setSimibarcodeData(null);
		}elseif(Mage::registry('simibarcode_data'))
			$data = Mage::registry('simibarcode_data')->getData();
		
		$fieldset = $form->addFieldset('simibarcode_form', array('legend'=>Mage::helper('simibarcode')->__('Barcode Information')));
		$fieldset->addType('datetime', 'Simi_Simibarcode_Block_Adminhtml_Simibarcode_Edit_Renderer_Datetime');

		$fieldset->addField('barcode', 'text', array(
			'label'		=> Mage::helper('simibarcode')->__('Barcode'),
			'required'	=> false,
			'bold'		=> true,
			'name'		=> 'barcode',
		));

		$fieldset->addField('qrcode', 'text', array(
			'label'		=> Mage::helper('simibarcode')->__('QR code'),
			'required'	=> false,
			'bold'		=> true,
			'name'		=> 'qrcode',
		));

		$fieldset->addField('barcode_status', 'select', array(
			'label'		=> Mage::helper('simibarcode')->__('Status'),
			'name'		=> 'barcode_status',
			'required'	=> false,
			'values'	=> Mage::getSingleton('simibarcode/status')->getOptionHash(),
		));

		$fieldset->addField('product_name', 'label', array(
			'label'		=> Mage::helper('simibarcode')->__('Product Name'),
			'required'	=> false,
			'bold'		=> true,
			'name'		=> 'product_name',
		));

		$fieldset->addField('product_sku', 'label', array(
			'label'		=> Mage::helper('simibarcode')->__('Product Sku'),
			'required'	=> false,
			'bold'		=> true,
			'name'		=> 'product_sku',
		));

		$fieldset->addField('created_date', 'datetime', array(
			'label'		=> Mage::helper('simibarcode')->__('Created Date'),
			'required'	=> false,
			'bold'		=> true,
			'name'		=> 'created_date',
		));

		$form->setValues($data);
		return parent::_prepareForm();
	}
}