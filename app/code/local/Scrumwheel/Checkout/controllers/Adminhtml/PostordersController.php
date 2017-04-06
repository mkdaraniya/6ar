<?php

/**
 * Created by PhpStorm.
 * User: Devangi
 * Date: 8/2/2016
 * Time: 6:34 PM
 */
require Mage::getBaseDir() . '/PHPMailer-master/PHPMailerAutoload.php';

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
            $billing_addr = $order->getBillingAddress();
            Mage::log('Computing for order ' . $lastOrderId, null, 'orders.log');
            /*Start Switch City Id based on city code*/
            $helper = Mage::helper('address');
            $ship_city = $shipping_addr->city;
            $bill_city = $billing_addr->city;
            if (strtolower($storeCode) == 'ksa_ar') {
                $ship_city_id = $helper->getSA_ArCityId($ship_city);
                $bill_city_id = $helper->getSA_ArCityId($bill_city);
            } else if (strtolower($storeCode) == 'ksa_en') {
                $ship_city_id = $helper->getSACityId($ship_city);
                $bill_city_id = $helper->getSACityId($bill_city);
            } else {
                $ship_city_id = '';
                $bill_city_id = '';
            }
            $shippingAddress = Mage::getModel('sales/order_address')->load($order->getShippingAddress()->getId());
            $shippingAddress->setCity_id($ship_city_id)->save();
            $billingAddress = Mage::getModel('sales/order_address')->load($order->getBillingAddress()->getId());
            $billingAddress->setCity_id($bill_city_id)->save();
            Mage::log("bill " . $bill_city_id . " & ship" . $ship_city_id, null, 'orders.log');
            /*End */

            /*Based On Store Send Shipping Method */
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'ksa_en') {
                $shippingMethod = 'naqel_naqel';

            } else {
                $shippingMethod = 'fetchr_free';
            }
            /*Arabic to English Conversion Array*/
            $western_arabic = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            $eastern_arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
            /*end*/

            /*For based on store code language set pre_lang = 102 for english and 103 for arabic*/
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'uae_ar') {
                $pref_lang = '103';
                // @mysql_query("SET CHARACTER_SET utf8;");
                $ship_ar_phone = $shipping_addr['telephone'];
                $ship_en_phone = str_replace($eastern_arabic, $western_arabic, $ship_ar_phone);
                $bill_ar_phone = $billing_addr['telephone'];
                $bill_en_phone = str_replace($eastern_arabic, $western_arabic, $bill_ar_phone);
            } else {
                $pref_lang = '102';
                $ship_en_phone = $shipping_addr['telephone'];
                $bill_en_phone = $billing_addr['telephone'];
            }
            /*test mail not allow to sync orders*/
            if ($order->getCustomerEmail() == 'test@test.com') {
                $flag = true;
            } else {
                $flag = true;
            }
            $customer = array();
            Mage::log('Soap API Call before', null, 'orders.log');
            /** Soap
             *  URL : wojooh.stag.redboxcloud.com
             *  Version : V2 */
            //staging api
            // $host = "wojooh-stag.redboxcloud.com";
            //Production api
            $host = "localhost/6ar/index.php";
            /*Soap Handle*/
            if ($flag == true) {
                try {
                    /*Create Soap Instance*/
                    $client = new SoapClient("http://" . $host . "/api/v2_soap?wsdl=1", array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE

                    ));
                    /*get session token*/
                    //change the api key which is here as this is of staging into 72nd line
                    //$session = $client->login((object)array('username' => 'itcan', 'apiKey' => 'fvrJZHyj8MvSDPsx'));
                    /*Get Order Information Of Itcan*/
                    $orderInfo = $this->getOrderInfo($lastOrderId);
                    $sess_id = $client->login('itcan', 'itcan@soap');
                    $str = $client->__getLastRequest();

                    Mage::log('Soap Call Successfully : ' . $sess_id, null, 'orders.log');
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
                    /** Soap Call : shoppingCartProductAdd
                     *  use : Add Product to Shopping Cart
                     */
                    $cart = $client->shoppingCartProductAdd($sess_id, $cartId, $product, $storeCode);
                    $str .= $client->__getLastRequest();
                    Mage::log("Products added to cart. Cart with id:" . $cartId, null, 'orders.log');
                    //Add Coupon Code If it is applied on order
                    if ($order->coupon_code != null) {
                        Mage::log('Coupon Code' . $order->coupon_code, null, 'orders.log');
                        $client->shoppingCartCouponAdd($sess_id, $cartId, $order->coupon_code, $storeCode);
                        $str .= $client->__getLastRequest();
                    }
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
                        "prefix" => $order->getCustomerPrefix(),
                        "pref_lang" => $pref_lang,
                        "reward_agree_tc" => "0",
                        "reward_enrolment" => "0"
                    );
                    /** Soap Call : shoppingCartCustomerSet
                     *  use : Set Customer Information To Shopping Cart
                     */
                    $resultCustomer = $client->shoppingCartCustomerSet($sess_id, $cartId, $customer);
                    $str .= $client->__getLastRequest();
                    if ($resultCustomer == true) {
                        Mage::log('Soap API Customer is set ' . $resultCustomer, null, 'orders.log');
                    }
                } catch (SoapFault $fault) {
                    $message = $fault->getMessage();
                    $productList = array();
                    foreach ($product as $key => $pro) {
                        $productSku = $pro['sku'];
                        echo $productSku;
                        array_push($productList, $productSku);
                    }
                    $testArray = array(1, 2);
                    print_r('<pre>');
                    print_r($testArray);
                    print_r($productList);exit;
                    $result1 = $client->catalogInventoryStockItemList($sess_id, array($productList));
//                    print_r('<pre>');
//                    print_r($result1);
//                    foreach ($result1 as $items){
//                        print_r($items);
//                    }

                    if ($fault->getCode() == 5) {
                        $order->setSyncStatus('Pending');
                    } else {
                        $order->setSyncStatus('Fail');
                    }
                    $order->save(false);

                    // $this->sendMail($fault->getMessage(),$lastOrderId,"Product can't be add to cart or customer isn't set");
                    Mage::log('Computing For Order : ' . $order->getIncrementId(), null, 'FailOrders.log');
                    Mage::log('Soap Error : ' . $message, null, 'FailOrders.log');
                    Mage::log('Product List : ' . $productList, null, 'FailOrders.log');
                    $this->_getSession()->addError($this->__($message));
                    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
//                    print_r('<pre>');
//                    print_r($fault);
                    die;
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
                    $shippingArray['telephone'] = $ship_en_phone;
                    $billingArray = $orderInfo['billing_address'];
                    $billingArray['mode'] = 'billing';
                    $billingArray['firstname'] = $order->getCustomerFirstname();
                    $billingArray['lastname'] = $order->getCustomerLastname();
                    $billingArray['is_default_shipping'] = 0;
                    $billingArray['is_default_billing'] = 0;
                    $billingArray['city_id'] = $bill_city_id;
                    $billingArray['telephone'] = $bill_en_phone;
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

                    }
                } catch (SoapFault $fault) {
                    $message = $fault;//"Item isn't not available"
                    $this->sendMail($fault->getMessage(), $lastOrderId, "Item Is not available, Shipping or Billing Address isn't set");
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
                    if ($shipMethod) {
                        Mage::log('Soap API shipping method is set ' . $shipMethod . $shippingMethod, null, 'orders.log');
                    }


                    /*Get Payment Method From Customer ORder*/
                    $paymentMethod = $orderInfo['payment'];
                    /** Soap Call : shoppingCartPaymentMethod
                     *  use :  Set Payment Method To Cart
                     */
                    $payment = $client->shoppingCartPaymentMethod($sess_id, $cartId, $paymentMethod);
                    $str .= $client->__getLastRequest();
                    if ($payment) {
                        Mage::log('Soap API  Payment Method is Set' . $payment, null, 'orders.log');
                    }

                    /** Soap Call : shoppingCartOrder
                     *  use :  Create an order from a shopping cart (quote)
                     */
                    // Soap Api Create an order from a shopping cart (quote)
                    $orderId = $client->shoppingCartOrder($sess_id, $cartId, $storeCode, null);
                    $str .= $client->__getLastRequest();
                    $response = $client->__getLastResponse();
                    if (file_exists("var/log/order.xml")) {
                        $Arr = explode('<?xml version="1.0" encoding="UTF-8"?>', $str);
                        $finalStr = implode("", $Arr);
                        file_put_contents("var/log/order.xml", $finalStr, FILE_APPEND);

                    } else {
                        $Arr = explode('<?xml version="1.0" encoding="UTF-8"?>', $str);
                        $finalStr = implode("", $Arr);
                        $dom = new DOMDocument;
                        $dom->preserveWhiteSpace = True;
                        $dom->loadXML($finalStr);
                        $dom->save('var/log/order.xml');
                    }
                    if ($orderId != '') {
                        Mage::log('Soap API Order created with orderId' . $orderId, null, 'orders.log');
                        Mage::log('Date : ' . date('Y-m-d H:i:s') . ' & Order No : ' . $orderId . ' & Total : ' . $order->getOrderCurrencyCode() . $order->getGrandTotal(), null, 'PlacedOrders.log');
                        $order->setSyncStatus('Success');
                        $order->save(false);
                        $this->_getSession()->addSuccess(
                            $this->__('The order has been send successfully.')
                        );
                        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
                    }
                } catch (SoapFault $fault) {
                    $message = $fault->getMessage();
                    $this->sendMail($fault->getMessage(), $lastOrderId, "Shipping Or Payment Methods are not available");
                    $this->setError($message, $order);
                }
            } else {
                $this->_getSession()->addError($this->__("UAE Orders cannot proceed further"));
                Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));
            }
        }
    }

    public function getSoapSessionId($client)
    {
        $apiuser = "itcan"; //webservice user login
        $apikey = "9LQRa2QsW8FR3c6M"; //webservice user pass
        $sess_id = $client->login($apiuser, $apikey); //Soap Login
        return $sess_id->result;
    }

    /*Send Mail Start*/
    public function sendMail($message, $lastOrderId, $userMsg)
    {
        $to = "6ar@itcan.ae,manasa@itcan.ae,ameer@itcan.ae,shreerag@itcan.ae";
        $subject = 'Fail Order#' . $lastOrderId;

        $message = "
<html>
                        <body>
                            <table style='border:5px solid #f1f1f1;margin:50px auto;width:500px;'>
                                <tbody>
                                    <tr width='100%' height='57'> 
                                        <td valign='top' align='left' style='border-top-left-radius:4px;border-top-right-radius:4px;padding:12px 18px;text-align:center;background:#f1f1f1'>
                                            <h1 style='font-size:20px;margin:0;color:#333'> Fail Order#$lastOrderId </h1>
                                        </td>
                                    </tr>
                                    <tr width='100%'> 
                                        <td valign='top' align='left' style='border-bottom-left-radius:4px;border-bottom-right-radius:4px;background:#ffff;padding:18px'>
                                            <h1 style='font-size:20px;margin:0;color:#333'> Dear Support, </h1>
                                            <p style='font:18px Helvetica Neue ,Arial,Helvetica;color:#333'> $userMsg</p>

                                            <p style='font:15px Helvetica Neue ,Arial,Helvetica;color:#333'>Soap Error : $message</p>
                                        </td> 
                                    </tr>
                                </tbody>
                            </table>
                        </body>
</html>
";

// Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
        $headers .= 'From: <6ar@itcan.ae>' . "\r\n";
        $headers .= 'Cc: devangi@scrumwheel.com' . "\r\n";

        if (mail($to, $subject, $message, $headers)) {
            return true;
        } else {
            return false;
        }
    }
    /*End*/

    /*Get Order Information From Api*/
    public function getOrderInfo($lastOrderId)
    {
        $host = "localhost/6ar/index.php"; //our online shop url
        $source = new SoapClient("http://" . $host . "/api/v2_soap/?wsdl=1", array('trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE)); //soap handle
        $apiuser = "itcan"; //webservice user login
        $apikey = "itcan@soap";   //webservice password
        $sess = $source->login($apiuser, $apikey); //Soap login
        Mage::log('ItCan Soap Call Successfully : ' . $sess, null, 'orders.log');
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
        Mage::log('Computing For Order : ' . $order->getIncrementId(), null, 'FailOrders.log');
        Mage::log('Soap Error : ' . $message, null, 'FailOrders.log');
        $this->_getSession()->addError($this->__($message));
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/index"));

    }
}