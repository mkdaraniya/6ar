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
class Magestore_RewardPoints_Block_Checkout_Onepage_Payment_Rewrite_Methods
    extends Mage_Payment_Block_Form
{
    protected function _construct(){
        parent::_construct();
        if ($this->isEnable()) {
            if ($this->isOneStepCheckout()) {
                $this->setTemplate('rewardpoints/checkout/payment/onestep.phtml');
            } else {
                $this->setTemplate('rewardpoints/checkout/payment/methods.phtml');
            }
        }
    }
    /**
     * rewrite output html of payment methods
     * 
     * @return string
     */
//    protected function _toHtml()
//    {
//        if ($this->isEnable()) {
//            if ($this->isOneStepCheckout()) {
//                $this->setTemplate('rewardpoints/checkout/payment/onestep.phtml');
//            } else {
//                $this->setTemplate('rewardpoints/checkout/payment/methods.phtml');
//            }
//        }
//        return parent::_toHtml();
//    }
    
    /**
     * check current page is onestepchekcout or not
     * 
     * @return boolean
     */
    public function isOneStepCheckout()
    {
        if (!$this->hasData('one_step_checkout')) {
//            $isOneStep = ($this->getRequest()->getModuleName() == 'onestepcheckout'
//                || $this->getRequest()->getActionName() == 'onestepcheckout');
            $isOneStep = $this->getRequest()->getModuleName() != 'checkout';
            $this->setData('one_step_checkout', $isOneStep);
        }
        return $this->getData('one_step_checkout');
    }
    
    /**
     * check reward points system is enabled or not
     * 
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::helper('rewardpoints')->isEnable();
    }
    
    /**
     * Rewrite to fix error not exist method on Magento 1.4.0.x
     * 
     * @param string $method
     * @return string
     */
    public function getMethodTitle($method)
    {
        if (version_compare(Mage::getVersion(), '1.4.1.0', '<')) {
            return $method->getTitle();
        }
        return parent::getMethodTitle($method);
    }
    
    /**
     * get reward points spending block helper
     * 
     * @return Magestore_RewardPoints_Helper_Block_Spend
     */
    public function getBlockHelper()
    {
        return Mage::helper('rewardpoints/block_spend');
    }
    
    /**
     * get reward points helper
     * 
     * @return Magestore_RewardPoints_Helper_Point
     */
    public function getPointHelper()
    {
        return Mage::helper('rewardpoints/point');
    }
    
    /**
     * call method that defined from block helper
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        $helper = $this->getBlockHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
            // return call_user_method_array($method, $helper, $args);
        }
        return parent::__call($method, $args);
    }
}
