<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Config Controller
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Connector_ConfigController extends Simi_Connector_Controller_Action {

//hainh customize
	public function get_groupsAction() {
        $information = Mage::getModel('connector/switch')->getGroups();
        $this->_printDataJson($information);
    }

    public function set_groupAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/switch')->setGroup();
        $this->_printDataJson($information);
    }	
//end
    public function get_storesAction() {
        $information = Mage::getModel('connector/switch')->getStores();
        $this->_printDataJson($information);
    }
	

    public function get_store_viewAction() {
        $data = $this->getData();
        //if (isset($data->store_id) && $data->store_id) {
			//hainh customize
			$all_stores= Mage::getModel('connector/switch')->getAllStores();
			//end
			$all_ids=array();
			foreach($all_stores['data'] as $store){
				$all_ids[]=$store['store_id'];
			}
			//$countryId = $this->getCountryId($_SERVER['REMOTE_ADDR']);
			$countryId = '';
			if ($countryId == 'AE')
				$group = Mage::getModel('core/store_group')->load(5);
			else 
				$group = Mage::getModel('core/store_group')->load(1);
			if(!isset($data->store_id) || !in_array($data->store_id,$all_ids)){
				$store_id= $group->getDefaultStoreId();
			}else{
				$store_id=$data->store_id;
			}
            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, Mage::app()->getStore($store_id)->getCode(), TRUE);
            Mage::app()->setCurrentStore(
                    Mage::app()->getStore($store_id)->getCode()
            );
            Mage::getSingleton('core/locale')->emulate($store_id);
        //}
        $information = Mage::getModel('connector/config_app')->getConfigApp();
        $this->_printDataJson($information);
    }

    public function save_store_viewAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/config_app')->statusSuccess();
        if ($data && $data->store_id) {
            Mage::app()->setCurrentStore(
                    Mage::app()->getStore($data->store_id)->getCode()
            );
            Mage::getSingleton('core/locale')->emulate($data->store_id);
        } else {
            $information = Mage::getModel('connector/config_app')->statusError();
        }
        // Zend_debug::dump(Mage::app()->getStore()->getId());die();
        $this->_printDataJson($information);
    }

    public function get_bannerAction() {
        $information = Mage::getModel('connector/config_app')->getBannerList();
        $this->_printDataJson($information);
    }

    public function get_cms_pagesAction() {
        $information = Mage::getModel('connector/config_app')->getMerchantInfo();
        $this->_printDataJson($information);
    }

    //for cms is same  function up
    public function get_merchant_infoAction() {
        $information = Mage::getModel('connector/config_app')->getMerchantInfo();
        $this->_printDataJson($information);
    }
    //end cms

    public function register_deviceAction() {
        $data = $this->getData();
        $device_id = $this->getDeviceId();
        $information = Mage::getModel('connector/device')->setDataDevice($data, $device_id);
        $this->_printDataJson($information);
    }
	
	public function get_notification_listAction() {
        $data = $this->getData();
        $device_id = $this->getDeviceId();
        $information = Mage::getModel('connector/device')->getNotificationList($data, $device_id);
        $this->_printDataJson($information);
    }

    public function get_pluginsAction() {
        $device_id = $this->getDeviceId();
        $information = Mage::getModel('connector/config_app')->getListPlugin($device_id);
        $this->_printDataJson($information);
    }

    public function get_splash_dataAction(){
        $data = $this->getData();
        $device_id = $this->getDeviceId();
        $information = Mage::getModel('connector/connector')->getSplashData($data, $device_id);
        $this->_printDataJson($information);
    }

    public function get_home_dataAction(){
        $data = $this->getData();
        $device_id = $this->getDeviceId();
        $information = Mage::getModel('connector/connector')->getHomeData($data, $device_id);
        $this->_printDataJson($information);
    }

	//hainh customize
	public function getCountryId($ip){
		//uae ip
		//$ip = '83.110.250.231';
        $url = 'http://ipinfo.io/'.trim($ip).'/json';
        $json = @file_get_contents($url);
        $data = json_decode($json);
        $country = $data->country;
		
        if($country){
            return $country;
        }else{
            return false;
        }
    }	
	
	
	//end

}
