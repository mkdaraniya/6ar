<?php
/**
 * Created by PhpStorm.
 * User: dthakore
 * Date: 3/10/17
 * Time: 11:57 AM
 */

/**
 * Order observer model
 *
 * @category    Local
 * @package     Scrumwheel_Orders
 * @author      Magento Development Team <devangi.thakore@gmail.com>
 */
/*
* Usage: Send Order to remote server via soap api
* event: Once customer place order ,it will sync automatically
* */
require Mage::getBaseDir() . '/PHPMailer-master/PHPMailerAutoload.php';

class Scrumwheel_Orders_Model_Observer
{
    public function export_new_order(Varien_Event_Observer $observer)
    {
        ini_set('default_socket_timeout', 1000);
        /*get Current Orders Detail*/
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $lastOrderId = $order->getIncrementId();
        Mage::log('Begin to export Order # ' . $lastOrderId, null, 'export_orders.log');
        ini_set('default_socket_timeout', 1000);
        /*Fetch Order Collection*/
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('sync_status', array('eq' => 'Pending'));
        if ($order->getData('sync_status') == 'Pending') {
            $store = Mage::getModel('core/store')->load($order['store_id']);
            $storeCode = $store->getCode();
            $shipping_addr = $order->getShippingAddress();
            $billing_addr = $order->getBillingAddress();
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
            Mage::log("bill ".$bill_city_id ." & ship ". $ship_city_id, null, 'export_orders.log');
            /*End */

            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'ksa_en') {
                $shippingMethod = 'naqel_naqel';
                $flag = true;
            } else {
                $flag = false;
                $shippingMethod = 'fetchr_free';
            }
            /*Arabic to English Conversion Array*/
            $western_arabic = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            $eastern_arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
            /*end*/

            /*For based on store code language set pre_lang = 102 for english and 103 for arabic*/
            if (strtolower($storeCode) == 'ksa_ar' || strtolower($storeCode) == 'uae_ar') {
                $pref_lang = '103';
                mysql_query("SET CHARACTER_SET utf8;");
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
                $flag = false;
            }
            $customer = array();
            if ($flag == true ) {
                Mage::log('Soap API test before', null, 'export_orders.log');
                /*Get Soap URl*/
                $host = "www.wojooh.com/index.php";
                try {
                    $client = new SoapClient("http://" . $host . "/api/v2_soap?wsdl=1", array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'login' => "stag",
                        'password' => "Dubai2015"
                    )); //soap handle
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
                    }
                    $cartId = $client->shoppingCartCreate($sess_id, $storeCode);
                    /** Soap Call : shoppingCartProductAdd
                     *  use : Add Product to Shopping Cart
                     */
                    $cart = $client->shoppingCartProductAdd($sess_id, $cartId, $product, $storeCode);
                    //Add Coupon Code If it is applied on order
                    if ($order->coupon_code != null) {
                        Mage::log('Coupon Code' . $order->coupon_code, null, 'export_orders.log');
                        $client->shoppingCartCouponAdd($sess_id, $cartId, $order->coupon_code,$storeCode);
                    }
                    Mage::log("Products added to cart. Cart with id:" . $cartId, null, 'export_orders.log');
                    /*Check Order Was Placed By Guest Customer or Not*/
                    $isGuest = $order->getCustomerIsGuest();
                    Mage::log('Soap API isGuest' . $isGuest, null, 'export_orders.log');
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
                    $resultCustomer = $client->shoppingCartCustomerSet($sess_id, $cartId, $customer);
                    if ($resultCustomer == true) {
                        Mage::log('Soap API Customer is set' . $resultCustomer, null, 'export_orders.log');
                    }
                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    $this->sendMail($fault->getMessage(), $lastOrderId, "Product isn't  add to cart or customer isn't set");
                    $this->error($fault,$lastOrderId);
                }
                try {
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

                    unset($shippingArray['address_id']);
                    unset($billingArray['address_id']);
                    $address = array(
                        $shippingArray, $billingArray
                    );
                    $addR = $client->shoppingCartCustomerAddresses($sess_id, $cartId, $address);
                    if ($addR == true) {
                        Mage::log('Soap API in Address Is Set' . $addR, null, 'export_orders.log');
                    }
                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    $this->sendMail($fault->getMessage(),$lastOrderId,"Item Is not available, Shipping or Billing Address isn't set");
                    $this->error($fault,$lastOrderId);
                }
                try {
                    $shipMethod = $client->shoppingCartShippingMethod($sess_id, $cartId, $shippingMethod);
                    if ($shippingMethod) {
                        Mage::log('Soap API in IF shipping' . $shipMethod . $shippingMethod, null, 'export_orders.log');
                    }
                    $paymentMethod = $orderInfo['payment'];
                    $payment = $client->shoppingCartPaymentMethod($sess_id, $cartId, $paymentMethod);
                    if ($payment) {
                        Mage::log('Soap API in IF payment' . $payment, null, 'export_orders.log');
                    }
                    // place the order
                    $orderId = $client->shoppingCartOrder($sess_id, $cartId, $storeCode, null);
                    if ($orderId != '') {
                        Mage::log('Soap API Order created with orderId' . $orderId, null, 'export_orders.log');
                        Mage::log('Date : ' . date('Y-m-d H:i:s') . ' & Order No : ' . $orderId . ' & Total : ' . $order->getOrderCurrencyCode() . $order->getGrandTotal(), null, 'PlacedOrders.log');
                        $order->setSyncStatus('Success');
                        $order->save(false);
                    }
                } catch (SoapFault $fault) {
                    $order->setSyncStatus('Fail');
                    $order->save(false);
                    $this->sendMail($fault->getMessage(),$lastOrderId,"Shipping Or Payment Methods are not available");
                    $this->error($fault,$lastOrderId);
                }
            }
        }
    }

    /*Send Mail Start*/
    public function sendMail($message, $lastOrderId, $userMsg)
    {
        $mail = new PHPMailer;
        $mail->isSMTP();                            // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';             // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                     // Enable SMTP authentication
        $mail->Username = 'demoitcan@gmail.com';          // SMTP username
        $mail->Password = 'demo123$'; // SMTP password
        $mail->SMTPSecure = 'ssl';                  // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                          // TCP port to connect to
        $mail->setFrom('itcansupport@gmail.com', 'itcanuae');
        $mail->addAddress('6ar@itcan.ae');   // Add a recipient
        $mail->AddCC('devangi@scrumwheel.com', 'Devangi');

        $mail->isHTML(true);  // Set email format to HTML
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $email_body = "<html>
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
                   </html>";
        $mail->Subject = 'Fail Order#' . $lastOrderId;
        $mail->Body = $email_body;

        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function getSoapSessionId($client)
    {
        $apiuser = "itcan"; //webservice user login
        $apikey = "fvrJZHyj8MvSDPsx"; //webservice user pass
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

    public function error($fault,$orderId){
        Mage::log('Failed Order # ' . $orderId, null, 'FailOrders.log');
        Mage::log('Error :' . $fault->getMessage(), null, 'FailOrders.log');
    }
}