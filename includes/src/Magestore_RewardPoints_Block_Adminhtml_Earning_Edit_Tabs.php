<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Rewardpoints
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Rewardpoints Block
 * 
 * @category    
 * @package     Rewardpoints
 * @author      Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Earning_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpoints_earning_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpoints')->__('Earning Rate Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Earning_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('rewardpoints')->__('Earning Rate Information'),
            'title'     => Mage::helper('rewardpoints')->__('Earning Rate Information'),
            'content'   => $this->getLayout()
                                ->createBlock('rewardpoints/adminhtml_earning_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
