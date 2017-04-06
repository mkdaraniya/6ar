<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 10/24/2016
 * Time: 3:31 PM
 */
class Scrumwheel_Outofstock_Model_Observer
{

    public function manageInventory()
    {
      Mage::log('in log action', null, 'stock.log');
	$ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
	$ksaXml = simplexml_load_file($ksaFeed);
	$ksaStore = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
	$_prods = Array();
	foreach ($ksaXml->channel->item as $_itemInfo):
	    $_sku = $_itemInfo->children('g', true)->id;
	    $_qty = $_itemInfo->children('g', true)->quantity;
	    $_prods["$_sku"] = "$_qty";
	endforeach;
	foreach ($_prods as $sku => $qty):
	    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
	    if ($_product) {
	        $productId = $_product->getIdBySku($sku);
	        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	        if ($qty == 0 && $stockItem->getData('qty') != $qty) {
	            $stockItem->setData('qty', 0);
	            $stockItem->setData('is_in_stock', 0);
	            $stockItem->save();
	        } else if ($qty > 0 && $stockItem->getData('qty') != ($qty - 1)) {
	            $qty = $qty - 1;
	            if ($qty == 0 || $qty == -1) {
	                $stockItem->setData('qty', 0);
	                $stockItem->setData('is_in_stock', 0);
	            } else {
	                $stockItem->setData('qty', $qty);
	                $stockItem->setData('is_in_stock', 1);
	            }
	            $stockItem->save();
	            Mage::log('sku' . $sku . 'qty' . $stockItem->getData('qty'), null, 'stock.log');
	            unset($stockItem);
	            unset($_product);
	        } else {
	            continue;
	        }
	    }
	endforeach;
	Mage::log('end action', null, 'stock.log');
        //$process = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
        //$process->reindexAll();
       // Mage::log('in module6', null, 'customstock.log');


    }


    public function manageProductStock($observer)
    {

    }
}