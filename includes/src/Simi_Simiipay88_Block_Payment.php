<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Simiipay88
 * @copyright   Copyright (c) 2012 
 * @license     
 */

 /**
 * Simiipay88 Block
 * 
 * @category    
 * @package     Simiipay88
 * @author      Developer
 */
class Simi_Simiipay88_Block_Payment extends Mage_Payment_Block_Info_Cc
{
	protected $_tranS;
    

    protected function _prepareSpecificInformation($transport = null) {
		// die("xxxxxxxx");
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
		
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $orderId = $invoice->getOrderId();
        } elseif (Mage::getSingleton('core/session')->getOrderIdForEmail()) {
            $orderId = Mage::getSingleton('core/session')->getOrderIdForEmail();
        }
        $trainM = Mage::getModel('simiipay88/simiipay88');
		$train = $trainM->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->getLastItem();        
        $info = null;
        $transport = parent::_prepareSpecificInformation($transport);
        if (count($train->getData())) {
            $info = array('Transaction Id' => $train->getTransactionId(),
                // 'Fund Source Type' => $train->getTransactionName()
                    );
        } else {
            $info = array('Notice' => 'Pending');
        }

        return $transport->addData($info);
    }

    public function getCcTypeName() {
        return '';
    }
}