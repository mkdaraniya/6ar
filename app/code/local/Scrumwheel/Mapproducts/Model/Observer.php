<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 8/2/2016
 * Time: 6:34 PM
 */
class Scrumwheel_Mapproducts_Model_Observer
{
    public function syncCatpro()
    {
        set_time_limit(0);
        //uae_en
        $uaeFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_en.xml';
        $uaeXml = simplexml_load_file($uaeFeed);
        $uaeStore = Mage::getModel('core/store')->load('uae_en', 'code')->getId();
        $_prods = Array();
        foreach ($uaeXml->channel->item as $_itemInfo):
            $_sku = $_itemInfo->children('g', true)->id;
            $_cats = $_itemInfo->children('g', true)->product_type;
            $_prods["$_sku"] = "$_cats";
        endforeach;
        foreach ($_prods as $key => $value) {
            try {
                $_categories = explode('>', $value);
                $_category = Mage::getModel('catalog/category')->load(end($_categories));
                $_catIds = array();
                foreach ($_category->getParentCategories() as $parent) {
                    $_catIds[] = $parent->getId();
                }
                $_pro = Mage::getModel('catalog/product')->loadByAttribute('sku', $key);
                if (count($_pro) > 0 && $_pro != '') {
                    Mage::log('uae before assign cat' . $_pro->getSku(), null, 'test.log');
                    $_pro->setStoreId($uaeStore);
                    $_pro->setNewsFromDate('');
                    $_pro->setNewsToDate('');
                    $_pro->setCategoryIds($_catIds);
                    $_pro->save();
                    Mage::log('after assign cat' . $_pro->getSku(), null, 'test.log');
                }
            } catch (Exception $e) {
                Mage::log('Error' . $e->getMessage(), null, 'test.log');
                continue;
            }
        }
        //end

        //ksa_en start
        $ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
        $ksaXml = simplexml_load_file($ksaFeed);
        $ksaStore = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
        $prods = Array();
        foreach ($ksaXml->channel->item as $itemInfo):
            $sku = $itemInfo->children('g', true)->id;
            $cats = $itemInfo->children('g', true)->product_type;
            $prods["$sku"] = "$cats";
        endforeach;
        foreach ($prods as $key => $value) {
            try {
                $categories = explode('>', $value);
                $category = Mage::getModel('catalog/category')->load(end($categories));
                $catIds = array();
                foreach ($category->getParentCategories() as $parent) {
                    $catIds[] = $parent->getId();
                }
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $key);
                if (count($product) > 0 && $product != '') {
                    Mage::log('uae before assign cat' . $product->getSku(), null, 'test.log');
                    $product->setStoreId($ksaStore);
                    $product->setNewsFromDate('');
                    $product->setNewsToDate('');
                    $product->setCategoryIds($catIds);
                    $product->save();
                    Mage::log('after assign cat' . $product->getSku(), null, 'test.log');
                }
            } catch (Exception $e) {
                Mage::log('KSA ERRor' . $e->getMessage(), null, 'test.log');
                continue;
            }
        }
        //end
        /*Disable Products Of Specific Feed*/
        $ksaFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
        $ksaXml = simplexml_load_file($ksaFeed);
        $ksaEnStoreId = Mage::getModel('core/store')->load('ksa_en', 'code')->getId();
        $ksaArStoreId = Mage::getModel('core/store')->load('ksa_ar', 'code')->getId();
        $prods = Array();
        foreach ($ksaXml->channel->item as $itemInfo):
            $sku = $itemInfo->children('g', true)->id;
            array_push($prods, $sku);
        endforeach;
//Get Product Collection based on KSA Store
        $collection = Mage::getModel('catalog/product')->setStoreId($ksaEnStoreId)->getCollection()
            ->addAttributeToSelect('sku');
        foreach ($collection as $product) {
            try {
                if (!in_array($product->getSku(), $prods)) {
                    Mage::log('Ksa ## ' . $product->getSku(), null, 'feed-product.log');
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    ), $ksaEnStoreId);
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    ), $ksaArStoreId);
                    Mage::log('Save Product ' . $product->getSku(), null, 'feed-product.log');
                } else {
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                    ), $ksaEnStoreId);
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                    ), $ksaArStoreId);
                }
            } catch (Exception $e) {
                Mage::log($product->getSku() . ' & Error ' . $e->getMessage(), null, 'feed-product.log');
                continue;
            }
        }

//uae_en Start
        $uaeFeed = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_en.xml';
        $uaeXml = simplexml_load_file($uaeFeed);
        $uaeEnStoreId = Mage::getModel('core/store')->load('uae_en', 'code')->getId();
        $uaeArStoreId = Mage::getModel('core/store')->load('uae_ar', 'code')->getId();
        $uaeprods = Array();
        foreach ($uaeXml->channel->item as $uaeitemInfo):
            $sku = $uaeitemInfo->children('g', true)->id;
            array_push($uaeprods, $sku);
        endforeach;
//Get Product Collection based on KSA Store
        $uaecollection = Mage::getModel('catalog/product')->setStoreId($uaeEnStoreId)->getCollection()
            ->addAttributeToSelect('sku');
        foreach ($uaecollection as $uaeproduct) {
            try {
                if (!in_array($uaeproduct->getSku(), $uaeprods)) {
                    Mage::log('UAE ##' . $uaeproduct->getSku(), null, 'feed-product.log');
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$uaeproduct->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    ), $uaeEnStoreId);
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$uaeproduct->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    ), $uaeArStoreId);
                    Mage::log('Save Product ' . $uaeproduct->getSku(), null, 'feed-product.log');
                }
            } catch (Exception $e) {
                Mage::log($uaeproduct->getSku() . ' & Error ' . $e->getMessage(), null, 'feed-product.log');
                continue;
            }
        }
    }
}