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
 * Themeone Grid Block
 * 
 * @category    
 * @package     Themeone
 * @author      Developer
 */
class Simi_Themeone_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareCollection() {
        $webId = 0;
        $collection = Mage::getModel('themeone/banner')->getCollection();
        if ($this->getRequest()->getParam('website')){            
            $webId = $this->getRequest()->getParam('website');
            $collection->addFieldToFilter('website_id', array('eq'=>$webId));
          
        }         
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('banner_id', array(
            'header' => Mage::helper('themeone')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'banner_id',
        ));

        $this->addColumn('banner_title', array(
            'header' => Mage::helper('themeone')->__('Title'),
            'align' => 'left',
            'index' => 'banner_title',
        ));

        // $this->addColumn('banner_url', array(
        //     'header' => Mage::helper('themeone')->__('URL'),
        //     'width' => '550px',
        //     'index' => 'banner_url',
        // ));

        $this->addColumn('type', array(
            'header' => Mage::helper('themeone')->__('Direct viewers to'),
            'width' => '150px',
            'index' => 'type',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('themeone')->__('Product In-app'),
                2 => Mage::helper('themeone')->__('Category In-app'),
                3 => Mage::helper('themeone')->__('Website Page'),
            ),
        ));
        /*
        $this->addColumn('website_id', array(
            'header' => Mage::helper('themeone')->__('Website'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'website_id',
            'type' => 'options',
            'options' => Mage::getSingleton('themeone/status')->getWebGird(),
             'filter' => false
        ));
        */
        $this->addColumn('status', array(
            'header' => Mage::helper('themeone')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('themeone')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('themeone')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
            )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('themeone')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('themeone')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('themeone');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('themeone')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('themeone')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('themeone/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('themeone')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('themeone')->__('Status'),
                    'values' => $statuses
            ))
        ));
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        $webId = Mage::getBlockSingleton('connector/adminhtml_web_switcher')->getWebsiteId();
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}