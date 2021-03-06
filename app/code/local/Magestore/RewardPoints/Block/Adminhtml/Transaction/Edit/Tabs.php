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
class Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpoints_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpoints')->__('Transaction Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('rewardpoints')->__('Transaction Information'),
            'title'     => Mage::helper('rewardpoints')->__('Transaction Information'),
            'content'   => $this->getLayout()
                                ->createBlock('rewardpoints/adminhtml_transaction_edit_tab_form')
                                ->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
