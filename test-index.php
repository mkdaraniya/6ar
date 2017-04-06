<?php
require_once 'app/Mage.php';
Mage::app();
//ksa_en start
$ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_ar.xml';
$ksaXml = simplexml_load_file($ksaFeed);
$ksaEnStoreId = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
$ksaArStoreId = Mage::getModel('core/store')->load('ksa_ar', 'code')->getId();
$prods = Array();
foreach ($ksaXml->channel->item as $itemInfo):
    $sku = $itemInfo->children('g', true)->id;
    array_push($prods, $sku);
endforeach;
echo count($prods);
