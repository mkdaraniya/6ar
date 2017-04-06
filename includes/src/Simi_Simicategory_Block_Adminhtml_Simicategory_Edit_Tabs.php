<?php

class Simi_Simicategory_Block_Adminhtml_Simicategory_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('simicategory_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('simicategory')->__('Category Information'));
	}

	protected function _beforeToHtml(){
		$this->addTab('form_section', array(
			'label'	 => Mage::helper('simicategory')->__('Category Information'),
			'title'	 => Mage::helper('simicategory')->__('Category Information'),
			'content'	 => $this->getLayout()->createBlock('simicategory/adminhtml_simicategory_edit_tab_form')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}