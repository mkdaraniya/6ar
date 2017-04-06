<?php
class Scrumwheel_Imageimport_Model_Observer
{
    public function setStatus()
    {
        /*Fetch All Products From Store*/
        $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
        foreach ($products as $product) {
            $productModel = Mage::getModel('catalog/product')->load($product->getId());
            Mage::log($productModel->getName(), null, 'sw.log');
            /*Fetch Media Of Products*/
            $mediaGallery = $productModel->getMediaGallery();
            //if there are images
            if (isset($mediaGallery['images'])) {
                //loop through the images
                foreach ($mediaGallery['images'] as $image) {
                    Mage::log($image['file'], null, 'sw.log');
                    Mage::getSingleton('catalog/product_action')->updateAttributes(array($product->getId()), array('image' => $image['file'], 'thumbnail' => $image['file'], 'small_image' => $image['file']), 0);
                    Mage::log($productModel->getName().' image Set',null,'sw.log');
                    break;
                }

            }

        }
    }
}