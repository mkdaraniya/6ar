<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Ztheme
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Ztheme Model
 * 
 * @category    
 * @package     Ztheme
 * @author      Developer
 */
class Simi_Ztheme_Model_Observer {

    public $_productList;

    /**
     *
     * @return Simi_Ztheme_Model_Observer
     */
    public function connectorConfigGetPluginsReturn($observer) {

        if ($this->getConfig("enable") == 0) {
              $observerObject = $observer->getObject();
              $observerData = $observer->getObject()->getData();
              $plugins = array();
              foreach ($observerData['data'] as $key => $plugin) {
              if ($plugin['sku'] == 'simi_ztheme')
              continue;
              $plugins[] = $plugin;
              }
              $observerData['data'] = $plugins;
              $observerObject->setData($observerData);
        }
    }

    public function connectorCatalogGetProductDetailReturn($observer) {
        $observerObject = $observer->getObject();
        $observerData = $observer->getObject()->getData();
        foreach ($observerData['data'] as $index => $product) {
            $productId = $product['product_id'];
            if ($productId) {
                $my_product = Mage::getModel('catalog/product')->load($productId);
                $product['product_url'] = str_replace(' ', '%20',$my_product->getProductUrl());
                $observerData['data'][$index] = $product;
                break;
            }
        }
        $observerObject->setData($observerData);
    }

    public function simicartGetListProductAfter($observer) {
        $collection = $this->_productCollection;
        $producIdArray = array();
        foreach ($collection as $key => $product) {
            $producIdArray[] = $product->getEntityId();
        }
        $information = $observer->getInformation();
        //var_dump($producIdArray);die;
    }

    // public function simiConnectorGetProductListAfter($observer) {
    //     if ($this->getConfig('enable') != 0) {
    //         $observerObject = $observer->getObject();
    //         $information = $observerObject->information;
    //         $collection = $observerObject->collection;
    //         if ($information['message'][0]!=null) {
    //             $collection->setPageSize($information['message'][0]);
    //         }
    //         $producIdArray = array();
    //         foreach ($collection->getData() as $key => $product) {
    //             $producIdArray[] = $product['entity_id'];
    //         }
    //         $information['other'][0] = array('product_id_array' => $producIdArray);
    //         $observerObject->information = $information;
    //         $observer->setObject($observerObject);
    //     }
    // }

    public function getConfig($value) {
        return Mage::getStoreConfig("ztheme/general/" . $value);
    }

    public function getProductAttributes() {
        return Mage::getSingleton('catalog/config')->getProductAttributes();
    }

}
