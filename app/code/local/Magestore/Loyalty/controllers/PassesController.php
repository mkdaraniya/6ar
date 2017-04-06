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
 * Loyalty Controller
 * 
 * @category    
 * @package     Loyalty
 * @author      Developer
 */

/**
 * Barcode Library
 */
include_once ('Barcode/barcodes.php');

/**
 * Loyalty Point Controller
 * 
 * @category    Magestore
 * @package     Magestore_Loyalty
 * @author      Magestore Developer
 */
class Magestore_Loyalty_PassesController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		// Customer
		$session = Mage::getSingleton('customer/session');
		if ($session->isLoggedIn()) {
			$passfile = Mage::helper('loyalty/passbook')->generatePassbook(Mage::getSingleton('customer/session')->getCustomer());
			if ($passfile) {
				header('Content-Type: application/vnd.apple.pkpass');
				header('Content-length: ' . filesize($passfile));
			    $handle = fopen($passfile, 'r');
                if ($handle) {
                    while (($buffer = fgets($handle, 4096)) !== false) {
                        echo $buffer;
                    }
                    if (feof($handle)) {
                        fclose($handle);
                    }
                }
			}
		}
	}
	
	public function barcodeAction()
	{
		$barcode = $this->getRequest()->getParam('code');
		$factory = new DNS2DBarcode();
		$factory->getBarcodePNG($barcode, 'PDF417,5', 2, 2);
		exit();
	}
}
