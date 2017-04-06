<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	
 * @package 	Simicheckoutcom
 * @copyright 	Copyright (c) 2012 
 * @license 	
 */

 /**
 * Simicheckoutcom Edit Grid Block
 * 
 * @category 	
 * @package 	Simicheckoutcom
 * @author  	Developer
 */
class Simi_Simicheckoutcom_Block_Adminhtml_Simicheckoutcom_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
		parent::__construct();
		$this->setId('simicheckoutcomGrid');
		$this->setDefaultSort('simicheckoutcom_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}
	
	/**
	 * prepare collection for block to display
	 *
	 * @return Simi_Simicheckoutcom_Block_Adminhtml_Simicheckoutcom_Grid
	 */
	protected function _prepareCollection(){
		$collection = Mage::getModel('simicheckoutcom/simicheckoutcom')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	/**
	 * prepare columns for this grid
	 *
	 * @return Simi_Simicheckoutcom_Block_Adminhtml_Simicheckoutcom_Grid
	 */
	protected function _prepareColumns(){
		$this->addColumn('simicheckoutcom_id', array(
			'header'	=> Mage::helper('simicheckoutcom')->__('ID'),
			'align'	 =>'right',
			'width'	 => '50px',
			'index'	 => 'simicheckoutcom_id',
		));

		$this->addColumn('title', array(
			'header'	=> Mage::helper('simicheckoutcom')->__('Title'),
			'align'	 =>'left',
			'index'	 => 'title',
		));

		$this->addColumn('content', array(
			'header'	=> Mage::helper('simicheckoutcom')->__('Item Content'),
			'width'	 => '150px',
			'index'	 => 'content',
		));

		$this->addColumn('status', array(
			'header'	=> Mage::helper('simicheckoutcom')->__('Status'),
			'align'	 => 'left',
			'width'	 => '80px',
			'index'	 => 'status',
			'type'		=> 'options',
			'options'	 => array(
				1 => 'Enabled',
				2 => 'Disabled',
			),
		));

		$this->addColumn('action',
			array(
				'header'	=>	Mage::helper('simicheckoutcom')->__('Action'),
				'width'		=> '100',
				'type'		=> 'action',
				'getter'	=> 'getId',
				'actions'	=> array(
					array(
						'caption'	=> Mage::helper('simicheckoutcom')->__('Edit'),
						'url'		=> array('base'=> '*/*/edit'),
						'field'		=> 'id'
					)),
				'filter'	=> false,
				'sortable'	=> false,
				'index'		=> 'stores',
				'is_system'	=> true,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('simicheckoutcom')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('simicheckoutcom')->__('XML'));

		return parent::_prepareColumns();
	}
	
	/**
	 * prepare mass action for this grid
	 *
	 * @return Simi_Simicheckoutcom_Block_Adminhtml_Simicheckoutcom_Grid
	 */
	protected function _prepareMassaction(){
		$this->setMassactionIdField('simicheckoutcom_id');
		$this->getMassactionBlock()->setFormFieldName('simicheckoutcom');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'		=> Mage::helper('simicheckoutcom')->__('Delete'),
			'url'		=> $this->getUrl('*/*/massDelete'),
			'confirm'	=> Mage::helper('simicheckoutcom')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('simicheckoutcom/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('simicheckoutcom')->__('Change status'),
			'url'	=> $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name'	=> 'status',
					'type'	=> 'select',
					'class'	=> 'required-entry',
					'label'	=> Mage::helper('simicheckoutcom')->__('Status'),
					'values'=> $statuses
				))
		));
		return $this;
	}
	
	/**
	 * get url for each row in grid
	 *
	 * @return string
	 */
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}