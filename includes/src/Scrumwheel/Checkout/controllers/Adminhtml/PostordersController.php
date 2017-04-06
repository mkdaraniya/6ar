<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 8/2/2016
 * Time: 6:34 PM
 */
class Scrumwheel_Checkout_Adminhtml_PostordersController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        ini_set('display_errors', 0);
        ini_set('default_socket_timeout', 1000);
        //Get Requested Order Id and Load Order Model
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        if (!$order->getId()) {
            $message = 'This order no longer exists.';
            $this->setError($message, $order);
        } else {
            /*Get Store Details Of Order*/
            $store = Mage::getModel('core/store')->load($order->getStoreId());
            $storeCode = $store->getCode();
            $lastOrderId = $order->getIncrementId();
            $shipping_addr = $order->getShippingAddress();
            $ship_city_id = $shipping_addr->city_id;
            $billing_addr = $order->getBillingAddress;
            $bill_city_id = $billing_addr->city_id;
            /*Based On Store Send Shipping Method */
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'ksa_en') {
                $shippingMethod = 'naqel_naqel';
                
            } else {           
                 $shippingMethod = 'fetchr_free';
            }
            /*For based on store code language set pre_lang = 102 for english and 103 for arabic*/
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'uae_ar') {
                $pref_lang= '103';
                $flag = true;
            } else {
            	$flag = true;
                $pref_lang= '102';
            }
            $customer = array();
            Mage::log('Soap API Call before', null, 'orders.log');
            /** Soap
             *  URL : wojooh.stag.redboxcloud.com
             *  Version : V2 */
            //$host = "wojooh-stag.redboxcloud.com";
           $host = "www.wojooh.com/index.php";
            /*Soap Handle*/
            if ($flag){
            try {
                /*Create Soap Instance*/
                $client = new SoapClient("https://" . $host . "/api/v2_soap?wsdl=1", array(
                    'trace' => true,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'login' => "stag",
                    'password' => "Dubai2015"
                ));
                /*get session token*/
                //$session = $client->login((object)array('username' => 'itcan', 'apiKey' => 'fvrJZHyj8MvSDPsx'));
//		$sess_id = $session->result;
               
                /*Get Order Information Of Itcan*/
                $orderInfo = $this->getOrderInfo($lastOrderId);
                $sess_id = $client->login('itcan','9LQRa2QsW8FR3c6M');
                $str = $client->__getLastRequest();
                Mage::log('Soap Call Successfully : ' . $sess_id,null,'orders.log');
                /*Fetch Items and Qty Of Orders*/
                $items = $order->getAllVisibleItems();
                $product = array();
                foreach ($items as $item) {
                    $tmp = array(
                        'sku' => $item->getSku(),
                        'quantity' => $item->getQtyOrdered()
                    );
                    array_push($product, $tmp);
                }
                /** Soap Call : shoppingCartCreate
                 *  use : Create Shopping Cart
                 */
                $cartId = $client->shoppingCartCreate($sess_id, $storeCode);
                $str .= $client->__getLastRequest();
                //$cartIdResult = $client->shoppingCartCreate((object)array('sessionId' => $sess_id, 'storeId' => $storeCode));
//                $cartId = $cartIdResult->result;
                /** Soap Call : shoppingCartProductAdd
                 *  use : Add Product to Shopping Cart
                 */
                $cart = $client->shoppingCartProductAdd($sess_id, $cartId, $product);
                $str .= $client->__getLastRequest();
                //$cartResult = $client->shoppingCartProductAdd((object)array('sessionId'=>$sess_id,'quoteId'=> $cartId,'productsData'=>$product));
                //$cart = $cartResult->result;
                Mage::log("Products added to cart. Cart with id:" . $cartId, null, 'orders.log');
                /*Always Place Order As Guest*/
                $isGuest = $order->getCustomerIsGuest();
                Mage::log('Customer Place Order As Guest ' . $isGuest, null, 'orders.log');
                $customer = array(
                    "firstname" => $order->getCustomerFirstname(),
                    "lastname" => $order->getCustomerLastname(),
                    "email" => $order->getCustomerEmail(),
                    "website_id" => "0",
                    "group_id" => $order->getCustomerGroupId(),
                    "store_id" => $order->getStoreId(),
                    "mode" => "guest",
                    "prefix"=>$order->getCustomerPrefix(),
                    "pref_lang"=>$pref_lang,
                    "reward_agree_tc"=>"1",
                    "reward_enrolment"=>"1"
                );
                /** Soap Call : shoppingCartCustomerSet
                 *  use : Set Customer Information To Shopping Cart
                 */
                $resultCustomer = $client->shoppingCartCustomerSet($sess_id, $cartId, $customer);
                $str .= $client->__getLastRequest();
                //$customerresult = $client->shoppingCartCustomerSet((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'customerData'=> $customer));
                //$resultCustomer = $customerresult->result;
                if ($resultCustomer == true) {
                    Mage::log('Soap API Customer is set ' . $resultCustomer, null, 'orders.log');
                  }
            } catch (SoapFault $fault) {
                $message = $fault->getMessage();
                $this->setError($message, $order);
            }
            try {
                /*Make Shipping Address As per Customer Order*/
                $shippingArray = $orderInfo['shipping_address'];
                $shippingArray['mode'] = 'shipping';
                $shippingArray['firstname'] = $order->getCustomerFirstname();
                $shippingArray['lastname'] = $order->getCustomerLastname();
                $shippingArray['is_default_shipping'] = 0;
                $shippingArray['is_default_billing'] = 0;
                $shippingArray['city_id'] = $ship_city_id; 
               // $shippingArray['city'] = "Abha";
               // $shippingArray['country_id'] = "SA";
                $billingArray = $orderInfo['billing_address'];
                $billingArray['mode'] = 'billing';
                $billingArray['firstname'] = $order->getCustomerFirstname();
                $billingArray['lastname'] = $order->getCustomerLastname();
                $billingArray['is_default_shipping'] = 0;
                $billingArray['is_default_billing'] = 0;
               // $billingArray['country_id'] = "SA";
                $billingArray['city_id'] = $bill_city_id; 
               // $billingArray['city'] = "Abha";
                unset($shippingArray['address_id']);
                unset($billingArray['address_id']);
                $address = array(
                    $shippingArray, $billingArray
                );
                /** Soap Call : shoppingCartCustomerAddresses
                 *  use : Set Customer Address To Shopping Cart
                 */
                $addR = $client->shoppingCartCustomerAddresses($sess_id, $cartId, $address);
                 $str .= $client->__getLastRequest();
                //$addrResult = $client->shoppingCartCustomerAddresses((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'customerAddressData'=> $address)); 
                //$addR = $addrResult->result;
                Mage::log("Address request:", null, 'test.log');
		Mage::log($client->__getLastRequest(), null, 'test.log');
                if ($addR == true) {
                    Mage::log('Soap API Shipping Address Is Set ' . $addR, null, 'orders.log');
                    Mage::log("Address request:", null, 'test.log');
		    Mage::log($client->__getLastRequest(), null, 'test.log');
                }
            } catch (SoapFault $fault) {
                $message = $fault;//"Item isn't not available"
                $this->setError($message, $order);
            }
            try {
                /** Soap Call : shoppingCartShippingMethod
                 *  Set Shipping Method To Cart For
                 *  KSA : naqel_naqel
                 *  UAE : fetchr_fetchr
                 */
                $shipMethod = $client->shoppingCartShippingMethod($sess_id, $cartId, $shippingMethod);
                 $str .= $client->__getLastRequest();
                //$shipMethodResult = $client->shoppingCartShippingMethod((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'shippingMethod'=> $shippingMethod));
                //$shipMethod = $shipMethodResult->result;
                if ($shipMethod) {
                    Mage::log('Soap API shipping method is set ' . $shipMethod . $shippingMethod, null, 'orders.log');
                }


                /*Get Payment Method From Customer ORder*/
                $paymentMethod =  $orderInfo['payment'];
                /** Soap Call : shoppingCartPaymentMethod
                 *  use :  Set Payment Method To Cart
                 */
                $payment = $client->shoppingCartPaymentMethod($sess_id, $cartId, $paymentMethod);
                 $str .= $client->__getLastRequest();
                //$paymentResult = $client->shoppingCartPaymentMethod((object)array('sessionId'=>$sess_id,'quoteId'=> $cartId,'paymentData'=>$paymentMethod));
                //$payment = $paymentResult->result;
                if ($payment) {
                    Mage::log('Soap API  Payment Method is Set' . $payment, null, 'orders.log');
                }

                /** Soap Call : shoppingCartOrder
                 *  use :  Create an order from a shopping cart (quote)
                 */
                // Soap Api Create an order from a shopping cart (quote)
                $orderId = $client->shoppingCartOrder($sess_id, $cartId, $storeCode, null);
                 $str .= $client->__getLastRequest();
	         if(file_exists("var/log/order.xml")){
	            $Arr = explode('<?xml version="1.0" encoding="UTF-8"?>',$str);
	            $finalStr = implode("",$Arr);
	            file_put_contents("var/log/order.xml", $finalStr,FILE_APPEND);
	
	        } else {
	            $Arr = explode('<?xml version="1.0" encoding="UTF-8"?>',$str);
	            $finalStr = implode("",$Arr);
	            $dom = new DOMDocument;
	            $dom->preserveWhiteSpace = True;
	            $dom->loadXML($finalStr);
	            $dom->save('var/log/order.xml');
	        }
                //$orderResult = $client->shoppingCartOrder((object)array('sessionId'=>$sess_id,'quoteId'=>$cartId,'storeId'=> $storeCode, null));
                //$orderId = $orderResult->result;
                if ($orderId != '') {
                    Mage::log('Soap API Order created with orderId' . $orderId, null, 'orders.log');
                    $order->setSyncStatus('Success');
                    $order->save(false);
                    $this->_getSession()->addSuccess(
                        $this->__('The order has been send successfully.')
                    );
                    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
                }
            } catch (SoapFault $fault) {
                $message = $fault->getMessage();
                $this->setError($message, $order);
            }
          }
        }
    }

    public function getSoapSessionId($client)
    {
        $apiuser = "itcan"; //webservice user login
        $apikey = "fvrJZHyj8MvSDPsx"; //webservice user pass
        $sess_id = $client->login($apiuser , $apikey); //Soap Login
        return $sess_id->result;
    }

    /*Get Order Information From Api*/
    public function getOrderInfo($lastOrderId)
    {
        $host = "6ar.co/index.php"; //our online shop url
        $source = new SoapClient("http://" . $host . "/api/v2_soap/?wsdl=1", array('trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE)); //soap handle
        $apiuser = "itcan"; //webservice user login
        $apikey = "itcan@soap";   //webservice password
        $sess = $source->login($apiuser, $apikey); //Soap login
        Mage::log('ItCan Soap Call Successfully : ' . $sess,null,'orders.log');
        //Retrieve  Order Information From itcan
        $orderInfoObj = $source->salesOrderInfo($sess, $lastOrderId);
        $orderInfo = json_decode(json_encode($orderInfoObj), true);
        return $orderInfo;
    }

    /*Render Error If Soap Fault Occurs*/
    public function setError($message, $order)
    {
        $order->setSyncStatus('Fail');
        $order->save(false);
        Mage::log('Soap Error : ' . $message,null,'orders.log');
        $this->_getSession()->addError($this->__($message));
        $this->_redirect('*/*/');
        $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
        return false;
    }
}