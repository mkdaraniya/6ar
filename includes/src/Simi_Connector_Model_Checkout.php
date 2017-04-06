<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Simi
 * @package     Simi_Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Simi Model
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
 
 
 /*
 * SHORTEN URL
 */
	
	
class GoogleUrlApi {
	// Constructor
	function GoogleURLAPI($key,$apiURL = 'https://www.googleapis.com/urlshortener/v1/url') {
		// Keep the API Url
		$this->apiURL = $apiURL.'?key='.$key;
	}
	
	// Shorten a URL
	function shorten($url) {
		// Send information along
		$response = $this->send($url);
		// Return the result
		return isset($response['id']) ? $response['id'] : false;
	}
	
	// Expand a URL
	function expand($url) {
		// Send information along
		$response = $this->send($url,false);
		// Return the result
		return isset($response['longUrl']) ? $response['longUrl'] : false;
	}
	
	// Send information to Google
	function send($url,$shorten = true) {
		// Create cURL
		$ch = curl_init();
		// If we're shortening a URL...
		if($shorten) {
			curl_setopt($ch,CURLOPT_URL,$this->apiURL);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode(array("longUrl"=>$url)));
			curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type: application/json"));
		}
		else {
			curl_setopt($ch,CURLOPT_URL,$this->apiURL.'&shortUrl='.$url);
		}
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		// Execute the post
		$result = curl_exec($ch);
		// Close the connection
		curl_close($ch);
		// Return the result
		return json_decode($result,true);
	}		
}

//end

 
 
 
 
class Simi_Connector_Model_Checkout extends Simi_Connector_Model_Abstract {

    protected $_data;

    public function _getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function customerLogin() {
        if ($this->_getSession()->isLoggedIn())
            return true;
        return false;
    }

    public function _helperCheckout() {
        return Mage::helper('connector/checkout');
    }

    public function changeData($data_change, $event_name, $event_value) {
        $this->_data = $data_change;
        // dispatchEvent to change data
        $this->eventChangeData($event_name, $event_value);
        return $this->getCacheData();
    }

    public function setCacheData($data, $module_name = '') {
        if ($module_name == "simi_connector") {
            $this->_data = $data;
            return;
        }
        if ($module_name == '' || is_null(Mage::getModel('connector/plugin')->checkPlugin($module_name)))
            return;
        $this->_data = $data;
    }

    public function getCacheData() {
        return $this->_data;
    }

    /*
     * input - address shipping and billing
     * ouput - shipping method, payment method, tax, total, discount, coupon.
     */

    public function getOrderConfig($data) {
        try {
            if ($this->customerLogin()) {
                $this->_getOnepage()->saveCheckoutMethod('customer');
            } elseif (isset($data->customer_password) && $data->customer_password) {
                $this->_getOnepage()->saveCheckoutMethod('register');
            } else {
                $this->_getOnepage()->saveCheckoutMethod('guest');
            }
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }
		//hainh customize
		if (isset($data->billingAddress) && (isset($data->billingAddress->name)))
			Mage::getSingleton('core/session')->setSimiPrefix($data->billingAddress->name);
		//end

        $billing_address = $this->_helperCheckout()->convertBillingAddress($data);
        $address_cache = null;
        $shipping_address = $this->_helperCheckout()->convertShippingAddress($data); 
		
        if (version_compare(Mage::getVersion(), '1.4.0.1', '=') === true) {
            $address_cache = array(
                'checkout_method' => $this->_getOnepage()->getCheckoutMehod(),
                'billing_address' => $billing_address,
                'shipping_address' => $shipping_address,
            );
        } else {
            $address_cache = array(
                'checkout_method' => $this->_getOnepage()->getCheckoutMethod(),
                'billing_address' => $billing_address,
                'shipping_address' => $shipping_address,
            );
        }

        $result = array();
        try {
            $result_billing = $this->_getOnepage()->saveBilling($billing_address, $billing_address['customer_address_id']);
            $result_shipping = $this->_getOnepage()->saveShipping($shipping_address, $shipping_address['customer_address_id']);			 
            if(isset($result_billing['message']) && isset($result_shipping['message']) ){
				$result = array_merge($result_billing['message'], $result_shipping['message']);
				$result = array_unique($result);
			}  
			
            $this->getAddress()->collectShippingRates()->save();
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }

        if (count($result)) {
              $result = array_unique($result);
              $information = $this->statusError($result);
              return $information;
        }
		
	
		
        Mage::getSingleton('core/session')->setSimiAddress($address_cache);			
		
        $shipping = $this->getAddress();
        $tax = 0;			
        $methods = $shipping->getGroupedAllShippingRates();
        //list shipping methods
        $list = array();
        foreach ($methods as $_ccode => $_carrier) {
            foreach ($_carrier as $_rate) {
				if($_rate->getData('error_message') != NULL){
					continue;
				}
				$select = false;
				if($shipping->getShippingMethod() != null && $shipping->getShippingMethod() == $_rate->getCode()){
					$select = true;
				}
				
				$s_fee = Mage::getModel('connector/checkout_shipping')->getShippingPrice($_rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax());
				$s_fee_incl = Mage::getModel('connector/checkout_shipping')->getShippingPrice($_rate->getPrice(), true);
				
				if (Mage::helper('tax')->displayShippingBothPrices() && $s_fee != $s_fee_incl){
					$list[] = array(
						's_method_id' => $_rate->getId(),
						's_method_code' => $_rate->getCode(),
						's_method_title' => $_rate->getCarrierTitle(),
						's_method_fee' => Mage::app()->getStore()->convertPrice(floatval($s_fee), false),
						's_method_fee_incl_tax' => $s_fee_incl,
						's_method_name' => $_rate->getMethodTitle(),
						's_method_selected' => $select,
					);
				}else{
					 $list[] = array(
						's_method_id' => $_rate->getId(),
						's_method_code' => $_rate->getCode(),
						's_method_title' => $_rate->getCarrierTitle(),
						's_method_fee' => $s_fee,			
						's_method_name' => $_rate->getMethodTitle(),
						's_method_selected' => $select,
					);
				}
            }
        }
		// $this->_getCheckoutSession()->getQuote()->collectTotals()->save();        
        //total    
		$total = $this->_getCheckoutSession()->getQuote()->getTotals();
        $grandTotal = $total['grand_total']->getValue();
        $subTotal = $total['subtotal']->getValue();
        $discount = 0;

        if (isset($total['discount']) && $total['discount']) {
            $discount = abs($total['discount']->getValue());
        }

        if (isset($total['tax']) && $total['tax']->getValue()) {
            $tax = $total['tax']->getValue();
        } else {
            $tax = 0;
        }

        $quote = $this->_getCheckoutSession()->getQuote();
        $totalPay = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
        $payment = Mage::getModel('connector/checkout_payment');
        Mage::dispatchEvent('simi_add_payment_method', array('object' => $payment));
        $paymentMethods = $payment->getMethods($quote, $totalPay);
        //list payment methods
        $list_payment = array();
        foreach ($paymentMethods as $method) {
			//hainh customize
			$dataPayment =  $payment->getDetailsPayment($method);
			if ($dataPayment['payment_method'] =='CASHONDELIVERY')
				$dataPayment['icon_payment'] = 'http://6ar.co/media/simi/paymenticon/cod.png';
            $list_payment[] = $dataPayment;
			//end
        }		
		//update hai.ta 19/11/2014
		$event_name = 'simicart_change_payment_detail';
		$event_value = array(
				'object' => $this,								
		);
		$list_payment = $this->changeData($list_payment, $event_name, $event_value);
		//end update
        //coupon code (maybe)
        $coupon = '';
        if ($this->_getCheckoutSession()->getQuote()->getCouponCode())
            $coupon = $this->_getCheckoutSession()->getQuote()->getCouponCode();
        $data_return = array();
        //update haita 20-8-2014
        if (!$this->_getCheckoutSession()->getQuote()->isVirtual()) {
            $data_return['shipping_method_list'] = $list;
        }
        //end update
        $data_return['payment_method_list'] = $list_payment;
        $total_data = array(
            'sub_total' => $subTotal,
            'grand_total' => $grandTotal,
            'discount' => $discount,
            'tax' => $tax,
            'coupon_code' => $coupon,
        );

        //hai ta 2082014
        $fee_v2 = array();
        Mage::helper('connector/checkout')->setTotal($total, $fee_v2);
        $total_data['v2'] = $fee_v2;
        //end haita 2082014

        $event_name = $this->getControllerName() . '_total';
        $event_value = array(
            'object' => $this,
        );
        $data_change = $this->changeData($total_data, $event_name, $event_value);
        $data_return['fee'] = $data_change;		
        $information = $this->statusSuccess();
        $information['data'] = array($data_return);
        return $information;
    }

    public function saveOrder($check_card, $data) {
        $information = null;
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array();
                $condition = $data->condition;
                if ($condition == "0") {
                    $information = $this->statusError(array($this->__('Please agree to all the terms and conditions before placing the order.')));
                    return $information;
                }
            }
            if ($check_card) {
                $dataPayment = $check_card;
                if (version_compare(Mage::getVersion(), '1.8.0.0', '>=') === true) {
                    $dataPayment['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                }
            }
            $this->_getOnepage()->getQuote()->getPayment()->importData($dataPayment);
            $this->_getOnepage()->saveOrder();
			$redirectUrl = $this->_getOnepage()->getCheckout()->getRedirectUrl();
            // } catch (Mage_Payment_Model_Info_Exception $e) {
            //} catch (Mage_Core_Exception $e) {                       
        } catch (Exception $e) {
            $informtaion = '';
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            $this->_getOnepage()->getCheckout()->setUpdateSection(null);
//            $this->cleanSession();
            return $information;
        }
        $this->_getOnepage()->getQuote()->save();
        $informtaion = $this->statusSuccess();
		$data_return = array(
			'invoice_number' => $this->_getCheckoutSession()->getLastRealOrderId(),
			'payment_method' => $this->_getOnepage()->getQuote()->getPayment()->getMethodInstance()->getCode(),
		);		
		//update hai.ta 19/11/2014
		$event_name = 'simicart_after_place_order';
		$event_value = array(
				'object' => $this,								
		);
		$data_return = $this->changeData($data_return, $event_name, $event_value);
		//end update
        $informtaion['data'] = array($data_return);
        $message_success = Mage::helper('checkout')->__("Thank you for your purchase!");
        $informtaion['message'] = array($message_success);
        Mage::getSingleton('core/session')->unsSimiAddress();
        Mage::getSingleton('core/session')->unsSimiShippingMethod();
		
		//hainh customize
		//check paypal express for same function as well
		if (Mage::getSingleton('core/session')->getSimiPrefix() && Mage::getSingleton('customer/session')->isLoggedIn()) {
			try{
				$customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
				if (Mage::getSingleton('core/session')->getSimiPrefix()!=$customer->getPrefix()) {
					$customer->setPrefix(Mage::getSingleton('core/session')->getSimiPrefix());
					$customer->save();
				}
			}
			catch (Exception $e) {}			
		}
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->_getCheckoutSession()->getLastRealOrderId());
		$productInfo = array();
        $itemCollection = $order->getAllVisibleItems();
		$layernavigationObject =  new Simi_Similayerednavigation_Model_Observer();
        foreach ($itemCollection as $item) {
            $options = array();
            $product_id = $item->getProductId();
			$product = Mage::getModel('catalog/product')->load($product_id);
            $productInfo[] = $layernavigationObject->getProductDetailForListing($product);
        }
		$information['data'][0]['product_array'] = $productInfo;
		//end
	
        $this->cleanSession();
		
        return $informtaion;
    }

    public function cleanSession() {
        $session = $this->_getOnepage()->getCheckout();
        $lastOrderId = $session->getLastOrderId();
        $this->_oldQuote = $session->getData('old_quote');
        $session->clear();
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
    }

    public function indexPlace() {
        $message = null;
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            $this->_getCheckoutSession()->addError($this->__('The onepage checkout is disabled.'));
            $message = 'The onepage checkout is disabled.';
            return $message;
        }
        $quote = $this->_getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            return 'NO DATA';
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            $this->_getCheckoutSession()->addError($error);
            $message = $error;
            return $message;
        }
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_getOnepage()->initCheckout();
        return $message;
    }
	
	public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->_getCheckoutSession()->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }
	protected $_address;
	
	
    public function reOrder($data) { 
        try {
            //$order = Mage::getModel('sales/order')->load($data->order_id);
            $order = Mage::getModel('sales/order')->loadByIncrementId($data->order_id);
            if (!$order->getId()) {
                return false;
            }
            $cart = Mage::getSingleton('checkout/cart');

            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }
            $cart->save();
            $information = $this->statusSuccess();
            $cartQty = array('cart_qty' => Mage::helper('checkout/cart')->getSummaryCount());
            $information['other'] = array($cartQty);
            $information['data'] = array($cartQty);
            return $information;
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }
    }
	
	public function cancelOrder($order_id){

        try{
            $information = $this->statusSuccess();
            $order=Mage::getModel('sales/order')->loadByIncrementId($order_id);
            // Zend_Debug::dump($order->getData());die;
            if(!$order->getId()){
                $information = $this->statusError(array(Mage::helper('connector')->__("Order doesn't exist.")));
            }
            $order->cancel();
            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
            $order->save();
        }catch(Exception $e){
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }

        }
        return $information;
    }
	
	//hainh customize
	
    //check if order validated
    public function checkIfValidated($data) {
        try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($data->order_id);
            $transaction = Mage::getModel('appreport/appreport')->getCollection()->addFieldToFilter('order_id',$order->getId())->getFirstItem();
            $information = $this->statusSuccess();
            $data_return = array(
                'is_validated' => $transaction->getData('validated')
            );      
            $information['data'] = array($data_return);
            return $information;
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }
    }

	//send sms confirmation
	public function sendOrderValidation($data) { 
        try {
			// get order and transaction
            $order = Mage::getModel('sales/order')->loadByIncrementId($data->order_id);
            if (!$order->getId()) {
                return $this->statusError(array(Mage::helper('connector')->__('Order does not exist')));
            }
			$transaction = Mage::getModel('appreport/appreport')->getCollection()->addFieldToFilter('order_id',$order->getId())->getFirstItem();
			
			//get key sent
			$keySent = $transaction->getData('key_sent_to_device');
			if ($keySent == 'N/A') {
				$existed = true;
				while($existed) {
					if (Mage::getModel('appreport/appreport')->getCollection()->addFieldToFilter('key_sent_to_device',$keySent)->count() == 0) 
						$existed = true;
					else 
						$existed = false;
					$keySent = substr(md5(time().rand()), 0, 5);
				}
				
				$transaction->setData('key_device_pushed_back','N/A');
				$transaction->setData('validated',0);
				$transaction->setData('key_sent_to_device',$keySent);
				$transaction->save();
			}
			
			// get url
			$urlToValidate = Mage::getBaseUrl().'connector/checkout/validate/key/'.$keySent;
			$key = 'AIzaSyDsiQ9ekOY4xQoT7FabVXowPjwouQUUCn4';
			$googer = new GoogleURLAPI($key);
			
			///* temporarity commented due to wrong google api key
			//$urlToValidate = $googer->shorten($urlToValidate);
			//*/
			
			$body = Mage::helper('connector')->__('Thank you for your order of %s %s! To ensure faster delivery, verify the order using this code: %s', $order->getData('order_currency_code'), round($order->getData('grand_total'), 2), $keySent);
			//.Mage::helper('connector')->__(' or click here: ').$urlToValidate;

			$billingAddressModel = $order->getBillingAddress();
			$customerPhoneNumber = $billingAddressModel->getData('postcode').$billingAddressModel->getData('telephone');
			//$customerPhoneNumber = '+841666323809';
			
			include("twillio/vendor/twilio/sdk/Twilio/autoload.php");
			include("twillio/vendor/twilio/sdk/Twilio/Rest/Client.php");
			$sid = 'ACf3562165bff6cd672ff53f242094e9eb';
			$token = 'eb678b93e5145fcb470359d66636bb17';
			$client = new Twilio\Rest\Client($sid, $token);
			
			// Use the client to do fun stuff like send text messages!
			$client->messages->create(
				// the number you'd like to send the message to
				$customerPhoneNumber,
				array(
					// A Twilio phone number you purchased at twilio.com/console
					'from' => '+14807393896',
					// the body of the text message you'd like to send
					'body' => $body
				)
			);
			
            $information = $this->statusSuccess();
            return $information;
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }
    }
	
	public function validateOrder($data) {
		try {
			if (strpos(Mage::helper('core/url')->getCurrentUrl(), 'connector') === false) {
				$information = $this->statusError(Mage::helper('checkout')->__('Authorization Key is not valid'));
				return $information;
			}
            $order = Mage::getModel('sales/order')->loadByIncrementId($data->order_id);
			if ($order->getId()) {
				$transaction = Mage::getModel('appreport/appreport')->getCollection()->addFieldToFilter('order_id',$order->getId())->getFirstItem();
				$transaction->setData('key_device_pushed_back',$data->authorize_key);
				$keySent = $transaction->getData('key_sent_to_device');
				$transaction->setData('validated',0);
				if ($data->authorize_key == $keySent) {
					$transaction->setData('validated',1);
					$transaction->save();
					$information = $this->statusSuccess();
					return $information;
				}
				$transaction->save();
				$information = $this->statusError(Mage::helper('checkout')->__('Authorization Key is not valid'));
				return $information;
			}
			$information = $this->statusError(Mage::helper('checkout')->__('No Order Found'));
			return $information;
		} catch (Exception $e) {
            if (is_array($e->getMessage())) {
                $information = $this->statusError($e->getMessage());
            } else {
                $information = $this->statusError(array($e->getMessage()));
            }
            return $information;
        }
	}
}
