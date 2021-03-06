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
class Magestore_RewardPoints_Block_Account_Policy extends Magestore_RewardPoints_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $page = $this->getPage();
        if ($pageId = $this->getPageId()) {
            $page->setStoreId(Mage::app()->getStore()->getId())
                ->load($pageId);
        }
    }
    
    /**
     * get Policy CMS Page ID
     * 
     * @return int
     */
    public function getPageId()
    {
        $page = $this->getPage();
        $identifier = Mage::getStoreConfig(Magestore_RewardPoints_Helper_Policy::XML_PATH_POLICY_PAGE);
        $pageId = $page->checkIdentifier($identifier, Mage::app()->getStore()->getId());
        if (!$pageId) {
            $idArray = explode('|', $identifier);
            if (count($idArray) > 1) {
                return end($idArray);
            }
        }
        return $pageId;
    }
    
    /**
     * get cms page model
     * 
     * @return Mage_Cms_Model_Page
     */
    public function getPage()
    {
        return Mage::getSingleton('cms/page');
    }
    
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $helper     = Mage::helper('cms');
        $processor  = $helper->getPageTemplateProcessor();
        
        $html   = $this->getMessagesBlock()->getGroupedHtml();
        if ($pageHeading = $this->getChild('page_content_heading')) {
            $pageHeading->setContentHeading($this->getPage()->getContentHeading());
            $html .= $pageHeading->toHtml();
        }
        $html .= $processor->filter($this->getPage()->getContent());
        return $html;
    }
}
