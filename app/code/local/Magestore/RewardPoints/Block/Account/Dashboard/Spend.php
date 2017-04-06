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
class Magestore_RewardPoints_Block_Account_Dashboard_Spend extends Magestore_RewardPoints_Block_Template
{
    /**
     * check showing container
     * 
     * @return boolean
     */
    public function getCanShow()
    {
        $rate = $this->getSpendingRate();
        if ($rate && $rate->getId()) {
            $canShow = true;
        } else {
            $canShow = false;
        }
        $container = new Varien_Object(array(
            'can_show' => $canShow
        ));
        Mage::dispatchEvent('rewardpoints_block_dashboard_spend_can_show', array(
            'container' => $container,
        ));
        return $container->getCanShow();
    }
    
    /**
     * get spending rate
     * 
     * @return Magestore_RewardPoints_Model_Rate
     */
    public function getSpendingRate()
    {
        if (!$this->hasData('spending_rate')) {
            $this->setData('spending_rate',
                Mage::getModel('rewardpoints/rate')->getRate(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY)
            );
        }
        return $this->getData('spending_rate');
    }
    
    /**
     * get current money formated of rate
     * 
     * @param Magestore_RewardPoints_Model_Rate $rate
     * @return string
     */
    public function getCurrentMoney($rate)
    {
        if ($rate && $rate->getId()) {
            $money = $rate->getMoney();
            return Mage::app()->getStore()->convertPrice($money, true);
        }
        return '';
    }
}
