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
 * Customer Controller
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Connector_CustomerController extends Simi_Connector_Controller_Action {

    public function check_login_statusAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->checkLoginStatus($data);
        $this->_printDataJson($information);
    }

    public function registerAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->register($data);
        $this->_printDataJson($information);
    }

    public function sign_inAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->login($data);
        $this->_printDataJson($information);
    }

    public function sign_outAction() {
        $information = Mage::getModel('connector/customer')->logout();
        $this->_printDataJson($information);
    }

    public function change_userAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->changeUser($data);
        $this->_printDataJson($information);
    }

    public function get_profileAction() {        
        $information = Mage::getModel('connector/customer')->getProfile();
        $this->_printDataJson($information);
    }

    public function get_user_addressesAction() {      
		$data = $this->getData();
        $information = Mage::getModel('connector/customer')->getAddressUser($data);
        $this->_printDataJson($information);
    }
    
    public function save_addressAction(){
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->saveAddress($data);
        $this->_printDataJson($information);
    }
    public function get_cartAction() {        
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->getCart($data);
        $this->_printDataJson($information);
    }

    public function get_order_historyAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->getOrderList($data);
        $this->_printDataJson($information);
    }

    public function get_order_detailAction() {
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->getOrderDetail($data);
        $this->_printDataJson($information);
    }
    
    public function forgot_passwordAction(){
        $data = $this->getData();
        $information = Mage::getModel('connector/customer')->forgetPassword($data);
        $this->_printDataJson($information);
    }

     public function get_download_productsAction(){                     
        $information = Mage::getModel('connector/catalog_product_options_downloadable')->getItems();
        $this->_printDataJson($information);
    }
}