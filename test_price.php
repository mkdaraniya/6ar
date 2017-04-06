<?php
require_once 'app/Mage.php';
Mage::app();

$ksaFeed = Array();
$ksaFeed['ksa_en'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
$ksaFeed['ksa_ar'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_ar.xml';
$ksaFeed['uae_en'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_en.xml';
$ksaFeed['uae_ar'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_ar.xml';
foreach ($ksaFeed as $key => $feed){
    $ksaXml = simplexml_load_file($feed);
    $ksaEnStoreId = Mage::getModel('core/store')->load($key, 'code')->getId();
    $prods = Array();
    foreach ($ksaXml->channel->item as $itemInfo):
        $sku = $itemInfo->children('g', true)->id;
        $qty = $itemInfo->children('g', true)->quantity;
        $price = $itemInfo->children('g',true)->price;
        array_push($prods, $sku);
        $_product = Mage::getModel('catalog/product')->setStoreId($ksaEnStoreId)->loadByAttribute('sku', $sku);
        if ($_product) {
            $productId = $_product->getIdBySku($sku);
            // set product inventory
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
                unset($stockItem);
            }
            // Update product price
            $_product = Mage::getSingleton('catalog/product_action');
            if(!empty($_product)) {
                $_product->updateAttributes(array($productId), array('price' => $price), $ksaEnStoreId);
            }
        }
        exit;
    endforeach;
    $collection = Mage::getModel('catalog/product')->setStoreId($ksaEnStoreId)->getCollection()
        ->addAttributeToSelect('sku');
    foreach ($collection as $product) {
        try {
            if (!in_array($product->getSku(), $prods)) {
                Mage::log('Ksa ## ' . $product->getSku(), null, 'feed-product.log');
                Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                ), $ksaEnStoreId);
                Mage::log('Save Product ' . $product->getSku(), null, 'feed-product.log');
            } else {
                Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                    'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                ), $ksaEnStoreId);
            }
        } catch (Exception $e) {
            Mage::log($product->getSku() . ' & Error ' . $e->getMessage(), null, 'feed-product.log');
            continue;
        }
        exit;
    }
}

/** disable or enable product
 * @param $prods
 * @param $ksaEnStoreId
 * @param $ksaArStoreId
 */
//
//$ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
//$ksaXml = simplexml_load_file($ksaFeed);
//$ksaEnStoreId = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
//$ksaArStoreId = Mage::getModel('core/store')->load('ksa_ar', 'code')->getId();
//$prods = Array();
//foreach ($ksaXml->channel->item as $itemInfo):
//    $sku = $itemInfo->children('g', true)->id;
//    $qty = $itemInfo->children('g', true)->quantity;
//    $price = $itemInfo->children('g',true)->price;
//
//    $_product = Mage::getModel('catalog/product')->setStoreId($ksaEnStoreId)->loadByAttribute('sku', $sku);
//    if ($_product) {
//
//        $productId = $_product->getIdBySku($sku);
//
//        // set product inventory
//        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
//        if ($qty == 0 && $stockItem->getData('qty') != $qty) {
//            $stockItem->setData('qty', 0);
//            $stockItem->setData('is_in_stock', 0);
//            $stockItem->save();
//        } else if ($qty > 0 && $stockItem->getData('qty') != ($qty - 1)) {
//            $qty = $qty - 1;
//            if ($qty == 0 || $qty == -1) {
//                $stockItem->setData('qty', 0);
//                $stockItem->setData('is_in_stock', 0);
//            } else {
//                $stockItem->setData('qty', $qty);
//                $stockItem->setData('is_in_stock', 1);
//            }
//            $stockItem->save();
//            Mage::log('sku' . $sku . 'qty' . $stockItem->getData('qty'), null, 'stock.log');
//            unset($stockItem);
//        }
//
//        // Update product price
//        $_product = Mage::getSingleton('catalog/product_action');
//        if(!empty($_product)) {
//            $_product->updateAttributes(array($productId), array('price' => $price), $ksaEnStoreId);
//        }
//    }
//
//    exit;
//    array_push($prods, $sku);
//endforeach;
//
////Get Product Collection based on KSA Store
//$collection = Mage::getModel('catalog/product')->setStoreId($ksaEnStoreId)->getCollection()
//    ->addAttributeToSelect('sku');
//foreach ($collection as $product) {
//    try {
//        if (!in_array($product->getSku(), $prods)) {
//            Mage::log('Ksa ## ' . $product->getSku(), null, 'feed-product.log');
//            Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
//                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
//            ), $ksaEnStoreId);
//            Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
//                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
//            ), $ksaArStoreId);
//            Mage::log('Save Product ' . $product->getSku(), null, 'feed-product.log');
//        } else {
//            Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
//                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
//            ), $ksaEnStoreId);
//            Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
//                'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
//            ), $ksaArStoreId);
//        }
//    } catch (Exception $e) {
//        Mage::log($product->getSku() . ' & Error ' . $e->getMessage(), null, 'feed-product.log');
//        continue;
//    }
//    exit;
//}

//// ksa en feed
//$ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
//$ksaEnStoreId1 = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
//$ksaXml = simplexml_load_file($ksaFeed);
//
//foreach ($ksaXml->channel->item as $_itemInfo):
//    $_sku = $_itemInfo->children('g', true)->id;
//    $price = $_itemInfo->children('g',true)->price;
//    $product_id = Mage::getModel("catalog/product")->getIdBySku( $_sku );
//    $updater = Mage::getSingleton('catalog/product_action');
//    if(!empty($updater)) {
//        $updater->updateAttributes(array($product_id), array('price' => $price), $ksaEnStoreId1);
//    }
//endforeach;
//
//// ksa ar feed
//$ksaArFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_ar.xml';
//$ksaArStoreId1 = Mage::getModel('core/store')->load('ksa_ar', 'code')->getId();
//$ksaArXml = simplexml_load_file($ksaArFeed);
//
//foreach ($ksaArXml->channel->item as $_itemInfo):
//    $_sku = $_itemInfo->children('g', true)->id;
//    $price = $_itemInfo->children('g',true)->price;
//    $product_id = Mage::getModel("catalog/product")->getIdBySku( $_sku );
//    $_product = Mage::getSingleton('catalog/product_action');
//    if(!empty($_product)) {
//        $_product->updateAttributes(array($product_id), array('price' => $price), $ksaArStoreId1);
//    }
//endforeach;
//
//// uae ar feed
//$uaeEnFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_en.xml';
//$uaeEnStoreId1 = Mage::getModel('core/store')->load('uae_en', 'code')->getId();
//$uaeEnXml = simplexml_load_file($uaeEnFeed);
//
//foreach ($uaeEnXml->channel->item as $_itemInfo):
//    $_sku = $_itemInfo->children('g', true)->id;
//    $price = $_itemInfo->children('g',true)->price;
//    $product_id = Mage::getModel("catalog/product")->getIdBySku( $_sku );
//    $_product = Mage::getSingleton('catalog/product_action');
//    if(!empty($_product)) {
//        $_product->updateAttributes(array($product_id), array('price' => $price), $uaeEnStoreId1);
//    }
//endforeach;
//
//// uae ar feed
//$uaeArFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_ar.xml';
//$uaeArStoreId1 = Mage::getModel('core/store')->load('ksa_ar', 'code')->getId();
//$uaeArXml = simplexml_load_file($uaeArFeed);
//
//foreach ($uaeArXml->channel->item as $_itemInfo):
//    $_sku = $_itemInfo->children('g', true)->id;
//    $price = $_itemInfo->children('g',true)->price;
//    $product_id = Mage::getModel("catalog/product")->getIdBySku( $_sku );
//    $_product = Mage::getSingleton('catalog/product_action');
//    if(!empty($_product)) {
//        $_product->updateAttributes(array($product_id), array('price' => $price), $uaeArStoreId1);
//    }
//endforeach;
