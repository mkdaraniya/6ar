<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 10/24/2016
 * Time: 3:31 PM
 */
class Scrumwheel_UpdateProduct_Model_Observer
{

    public function manageInventory()
    {
     /*Disable Products Of Specific Feed*/
        $Feeds = Array();
        $Feeds['ksa_en'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_en.xml';
        $Feeds['ksa_ar'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_ksa_ar.xml';
        $Feeds['uae_en'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_en.xml';
        $Feeds['uae_ar'] = 'https://www.wojooh.com/media/wysiwyg/mobileapp-product-feed/mobileapp_export_uae_ar.xml';
        foreach ($Feeds as $key => $feed){
            $Xml = simplexml_load_file($feed);
            $StoreId = Mage::getModel('core/store')->load($key, 'code')->getId();
            $prods = Array();
            foreach ($Xml->channel->item as $itemInfo):
                $sku = $itemInfo->children('g', true)->id;
                $qty = $itemInfo->children('g', true)->quantity;
                $price = $itemInfo->children('g',true)->price;
                array_push($prods, $sku);
                $_product = Mage::getModel('catalog/product')->setStoreId($StoreId)->loadByAttribute('sku', $sku);
                if ($_product) {
                    $productId = $_product->getIdBySku($sku);
                    // set product inventory
                    $this->manageQuantity($productId,$qty);
                    // Update product price
                    $_product = Mage::getSingleton('catalog/product_action');
                    if(!empty($_product)) {
                        $_product->updateAttributes(array($productId), array('price' => $price), $StoreId);
                    }
                }
            endforeach;
            $this->disableProducts($prods,$StoreId,$key);
        }
    }

    public function manageProductStock($observer)
    {

    }
    /** disable or enable product
     * @param $prods
     * @param $ksaEnStoreId
     * @param $ksaArStoreId
     */
    protected function disableProducts($prods,$StoreId,$feed){
        //Get Product Collection based on KSA Store
        $collection = Mage::getModel('catalog/product')->setStoreId($StoreId)->getCollection()
            ->addAttributeToSelect('sku');
        foreach ($collection as $product) {
            try {
                if (!in_array($product->getSku(), $prods)) {
                    Mage::log('Ksa ## ' . $product->getSku(), null, 'update-product.log');
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    ), $StoreId);
                    Mage::log('SKU : '.$product->getSku().' Price : '.$product->getPrice().' QTY : '.$product->getQty().' Store : '.$feed.' Visibility : False', null, 'update-product.log');
                } else {
                    Mage::getModel('catalog/resource_product_action')->updateAttributes([$product->getId()], array(
                        'visibility' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                    ), $StoreId);

                    Mage::log('SKU : '.$product->getSku().' Price : '.$product->getPrice().' QTY : '.$product->getQty().' Store : '.$feed.' Visibility : True', null, 'update-product.log');

                }
            } catch (Exception $e) {
                Mage::log($product->getSku() . ' & Error ' . $e->getMessage(), null, 'update-product.log');
                continue;
            }
        }
    }

    /** set product quantity
     * @param $productId
     * @param $sku
     * @param $qty
     */
    protected function manageQuantity($productId,$qty){
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
    }
}
