<?php
class Scrumwheel_Brandfilter_Model_Observer
{
    public function lockAttributes($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $product->lockAttribute('brand_code');
    }
}