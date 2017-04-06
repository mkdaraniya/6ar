<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 8/2/2016
 * Time: 6:34 PM
 */
class Scrumwheel_Postorders_Model_Observer
{
    public function syncOrders()
    {
        ini_set('default_socket_timeout', 1000);
        /*Fetch Order Collection*/
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('sync_status', array('eq' => 'Pending'));
        foreach ($orders as $order) {
            $store = Mage::getModel('core/store')->load($order['store_id']);
            $storeCode = $store->getCode();
            $lastOrderId = $order['increment_id'];

            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'ksa_en') {
                $shippingMethod = 'naqel_naqel';
                $flag = true;
            } else {
                $flag = false;
                $shippingMethod = 'fetchr_free';
            }
            /*For based on store code language set pre_lang = 102 for english and 103 for arabic*/
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'uae_ar') {
                $pref_lang = '103';
            } else {
                $pref_lang = '102';
            }
            $customer = array();
            if ($flag == true) {
                Mage::log('Soap API test before', null, 'orders.log');
                /*Get Soap URl*/
                $host = "www.wojooh.com/index.php";
                try {
                    $client = new SoapClient("https://" . $host . "/api/v2_soap?wsdl=1", array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'login' => "stag",
                        'password' => "Dubai2015"
                    )); //soap handle

                    //$session = $client->login((object)array('username' => 'itcan', 'apiKey' => 'fvrJZHyj8MvSDPsx'));
                    //$sess_id = $session->result;
                    /*Get OrderInformation By Order Increment Id*/
                    $orderInfo = $this->getOrderInfo($lastOrderId);
                    $sess_id = $this->getSoapSessionId($client);
                    /*Fetch Items and Qty Of Orders*/
                    $items = $order->getAllVisibleItems();
                    $product = array();
                    foreach ($items as $item) {
                        $tmp = array(
                            'sku' => $item->getSku(),
                            'quantity' => $item->getQtyOrdered()
                        );
                        array_push($product, $tmp);
                        //              Mage::log('item-sku' . $item->getSku(), null, 'orders.log');
                    }
                    $cartId = $client->shoppingCartCreate($sess_id, $storeCode);
                    $cart = $client->shoppingCartProductAdd($sess_id, $cartId, $product);
//$cartIdResult = $client->shoppingCartCreate((object)array('sessionId' => $sess_id, 'storeCode' => $storeCode));
                    //              $cartId = $cartIdResult->result;
                    /** Soap Call : shoppingCartProductAdd
                     *  use : Add Product to Shopping Cart
                     */
                    //$cart = $client->shoppingCartProductAdd($sess_id, $cartId, $product);
                    // $cartResult = $client->shoppingCartProductAdd((object)array('sessionId'=>$sess_id,'quoteId'=> $cartId,'productsData'=>$product));
                    //$cart = $cartResult->result;
                    Mage::log("Products added to cart. Cart with id:" . $cartId, null, 'orders.log');
                    /*Check Order Was Placed By Guest Customer or Not*/
                    $isGuest = $order->getCustomerIsGuest();
                    if ($isGuest == 1) {
                        Mage::log('Soap API isGuest' . $isGuest, null, 'orders.log');
                        $customer = array(
                            "firstname" => $order->getCustomerFirstname(),
                            "lastname" => $order->getCustomerLastname(),
                            "website_id" => "0",
                            "group_id" => $order->getCustomerGroupId(),
                            "store_id" => $order->getStoreId(),
                            "email" => $order->getCustomerEmail(),
                            "mode" => "guest",
                            "prefix" => $order->getCustomerPrefix(),
                            "pref_lang" => $pref_lang,
                            "reward_agree_tc" => "1",
                            "reward_enrolment" => "1"
                        );
                    } else {
                        Mage::log('Soap API Cutomer' . $isGuest, null, 'orders.log');
                        $customer = array(
                            "firstname" => $order->getCustomerFirstname(),
                            "lastname" => $order->getCustomerLastname(),
                            "website_id" => "0",
                            "group_id" => $order->getCustomerGroupId(),
                            "store_id" => $order->getStoreId(),
                            "email" => $order->getCustomerEmail(),
                            "mode" => "guest",
                            "prefix" => $order->getCustomerPrefix(),
                            "pref_lang" => $pref_lang,
                            "reward_agree_tc" => "1",
                            "reward_enrolment" => "1"
                        );
                    }
                    $resultCustomer = $client->shoppingCartCustomerSet($sess_id, $cartId, $customer);
                    //$customerresult = $client->shoppingCartCustomerSet((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'customerData'=> $customer));
                    //$resultCustomer = $customerresult->result;
                    if ($resultCustomer == true) {
                        Mage::log('Soap API Customer is set' . $resultCustomer, null, 'orders.log');
                    }
                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    continue;
                }
                try {
                    $shippingArray = $orderInfo['shipping_address'];
                    $shippingArray['mode'] = 'shipping';
                    $shippingArray['firstname'] = $order->getCustomerFirstname();
                    $shippingArray['lastname'] = $order->getCustomerLastname();
                    $shippingArray['is_default_shipping'] = 0;
                    $shippingArray['is_default_billing'] = 0;
                    $billingArray = $orderInfo['billing_address'];
                    $billingArray['mode'] = 'billing';
                    $billingArray['firstname'] = $order->getCustomerFirstname();
                    $billingArray['lastname'] = $order->getCustomerLastname();
                    $billingArray['is_default_shipping'] = 0;
                    $billingArray['is_default_billing'] = 0;
                    unset($shippingArray['address_id']);
                    unset($billingArray['address_id']);
                    $address = array(
                        $shippingArray, $billingArray
                    );
                    $addR = $client->shoppingCartCustomerAddresses($sess_id, $cartId, $address);
                    //$addrResult = $client->shoppingCartCustomerAddresses((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'customerAddressData'=> $address));
                    // $addR = $addrResult->result;
                    if ($addR == true) {
                        Mage::log('Soap API in Address Is Set' . $addR, null, 'orders.log');
                    }
                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    continue;
                }
                try {
                    $shipMethod = $client->shoppingCartShippingMethod($sess_id, $cartId, $shippingMethod);
                    //$shipMethodResult = $client->shoppingCartShippingMethod((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'shippingMethod'=> $shippingMethod));
                    //$shipMethod = $shipMethodResult->result;
                    if ($shippingMethod) {
                        Mage::log('Soap API in IF shipping' . $shipMethod . $shippingMethod, null, 'orders.log');
                    }
                    $paymentMethod = $orderInfo['payment'];
                    //$payment = $client->shoppingCartPaymentMethod($sess_id, $cartId, $paymentMethod);
                    $payment = $client->shoppingCartPaymentMethod($sess_id, $cartId, $paymentMethod);
                    //$paymentResult = $client->shoppingCartPaymentMethod((object)array('sessionId'=>$sess_id,'quoteId'=> $cartId,'paymentData'=>$paymentMethod));
                    //$payment = $paymentResult->result;
                    if ($payment) {
                        Mage::log('Soap API in IF payment' . $payment, null, 'orders.log');
                    }
                    // place the order
                    $orderId = $client->shoppingCartOrder($sess_id, $cartId, $storeCode, null);
                    //$orderResult = $client->shoppingCartOrder((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'storeCode'=> $storeCode, null));
                    //$orderId = $orderResult->result;
                    if ($orderId != '') {
                        Mage::log('Soap API Order created with orderId' . $orderId, null, 'orders.log');
                        $order->setSyncStatus('Success');
                        $order->save(false);
                    }

                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    continue;
                }
            }
        }
    }

    public function getSoapSessionId($client)
    {
        $apiuser = "itcan"; //webservice user login
        $apikey = "9LQRa2QsW8FR3c6M"; //webservice user pass
        $sess_id = $client->login($apiuser, $apikey); //we do login
        return $sess_id;
    }

    /*Get Order Information From Api*/
    public function getOrderInfo($lastOrderId)
    {
        $host = "6ar.co/index.php"; //our online shop url
        $source = new SoapClient("http://" . $host . "/api/v2_soap/?wsdl=1", array('trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE)); //soap handle
        $apiuser = "itcan"; //webservice user login
        $apikey = "itcan@soap";
        $sess = $source->login($apiuser, $apikey); //we do login
        $orderInfoObj = $source->salesOrderInfo($sess, $lastOrderId);
        $orderInfo = json_decode(json_encode($orderInfoObj), true);
        return $orderInfo;
    }
}