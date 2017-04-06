<?php
/**
 * Set Order Sync Status Pending
 * @user : Devangi Thakore
 * @category Scrumwheel
 * http://www.scrumwheel.com/
 */
class Scrumwheel_Checkout_Model_Observer
{
    public function postDataTosServer(Varien_Event_Observer $observer)
    {
        /*Get Current Order Details*/
        $orderIds = $observer->getData('order_ids');
        $orderId = current($orderIds);
        $order = Mage::getModel('sales/order')->load($orderId);
        //Set Sync Status Pending While Placing Orders
        $order->setSyncStatus('Pending');
        $order->save(false);
    }
}
?>