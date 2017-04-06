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
 * Ztheme Grid Block
 * 
 * @category    
 * @package     Ztheme
 * @author      Developer
 */
class Simi_Ztheme_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Simi_Ztheme_Block_Adminhtml_Ztheme_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ztheme/banner')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
		foreach($collection as $banner) {
			$cat = Mage::getModel('catalog/category');
            $cat->load($banner->getCategoryId());
            $banner->setData('category_name',$cat->getName());
			$banner->setData('parent_name',$cat->getParentCategory()->getName());
			$storeviewId = $banner->getWebsiteId();
			$storeviewModel = Mage::getModel('core/store')->load($storeviewId);
			$banner->setData('storeview_name', $storeviewModel->getGroup()->getName() .' - '.$storeviewModel->getName());
        } 
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Simi_Ztheme_Block_Adminhtml_Ztheme_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('banner_id', array(
            'header'    => Mage::helper('ztheme')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'banner_id',
        ));

        $this->addColumn('banner_title', array(
            'header'    => Mage::helper('ztheme')->__('Title'),
            'align'     =>'left',
            'index'     => 'banner_title',
        ));
        $this->addColumn('banner_position', array(
            'header'    => Mage::helper('ztheme')->__('Position'),
            'align'     =>'left',
            'index'     => 'banner_position',
        ));
        $this->addColumn('category_name', array(
            'header'    => Mage::helper('ztheme')->__('Category'),
            'index'     => 'category_name',
        ));

		$this->addColumn('parent_name', array(
            'header'    => Mage::helper('ztheme')->__('Parent Name'),
            'index'     => 'parent_name',
        ));
		
		$this->addColumn('website_id', array(
            'header'    => Mage::helper('ztheme')->__('Storeview Id'),
            'index'     => 'website_id',
        ));
		
		//hainh customize
		$stores = Mage::getModel('core/store')->getCollection();
        $list_store = array();
        foreach ($stores as $store) {
            $list_store[$store->getId()] = $store->getGroup()->getName() .' - '.$store->getName();
        }
		
		$this->addColumn('storeview_name', array(
            'header'    => Mage::helper('ztheme')->__('Storeview Name'),
            'index'     => 'storeview_name',
			'type'        => 'options',
            'options'     => $list_store,
        ));
		
		
		$this->addColumn('banner_content', array(
            'header'    => Mage::helper('ztheme')->__('Show on'),
            'align'     => 'left',
            'index'     => 'banner_content',
            'type'        => 'text',
            'options'     => array(
                '1' => Mage::helper('ztheme')->__('Home Screen'),
                '2'=> Mage::helper('ztheme')->__('Sub-Category Screen'),
            ),
        ));
		
        $this->addColumn('status', array(
            'header'    => Mage::helper('ztheme')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options'     => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));


        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('ztheme')->__('Action'),
                'width'        => '100',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('ztheme')->__('Edit'),
                        'url'        => array('base'=> '*/*/edit'),
                        'field'        => 'banner_id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

       // $this->addExportType('*/*/exportCsv', Mage::helper('ztheme')->__('CSV'));
       // $this->addExportType('*/*/exportXml', Mage::helper('ztheme')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Simi_Ztheme_Block_Adminhtml_Ztheme_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('ztheme');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('ztheme')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('ztheme')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('ztheme/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('ztheme')->__('Change status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('ztheme')->__('Status'),
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
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('banner_id' => $row->getId()));
    }
}