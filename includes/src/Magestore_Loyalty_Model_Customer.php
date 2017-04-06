<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Loyalty
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Loyalty Model
 * 
 * @category    
 * @package     Loyalty
 * @author      Developer
 */
class Magestore_Loyalty_Model_Customer extends Simi_Connector_Model_Customer
{
	public function login($data)
	{
		$information = parent::login($data);
		if ($this->_getSession()->isLoggedIn() && isset($information['data'][0])) {
			$information['data'][0]['loyalty_balance'] = Mage::helper('loyalty')->getMenuBalance();
		}
		return $information;
	}
	
	public function getCart($data)
	{
		$information = parent::getCart($data);
		if (!Mage::getStoreConfigFlag(Magestore_Loyalty_Helper_Data::XML_PATH_SHOW_CART)) {
			return $information;
		}
		// Add Reward Information
		$earningPoints = Mage::helper('rewardpoints/calculation_earning')->getTotalPointsEarning();
		if ($earningPoints && isset($information['data'][0])) {
			$label = Mage::helper('loyalty')->__('Checkout now and earn %s in rewards',
			    Mage::helper('rewardpoints/point')->format($earningPoints)
			);
			$information['data'][0]['loyalty_image'] = Mage::helper('rewardpoints/point')->getImage();
			$information['data'][0]['loyalty_label'] = $label;
		}
		return $information;
	}
}
