<?php
class Excellence_Fee_Model_Fee extends Varien_Object{
	const FEE_AMOUNT = 10;

	public static function getFee($address){
		$payment_code = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod();
		if ($payment_code=='cashondelivery') {
			if ($address->getAddressType()=='shipping'&& $address->getCountry()=='SA') {
				return 25;
			} else {
				return 15;
			}
		} else {
			return 0;
		}

	}

	public static function canApply($address){
		//put here your business logic to check if fee should be applied or not
	//	if($address->getAddressType() == 'billing'){
		return true;
		//}
	}
}