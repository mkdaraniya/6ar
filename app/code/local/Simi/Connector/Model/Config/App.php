<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Connector Model
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Connector_Model_Config_App extends Simi_Connector_Model_Abstract {

    public function getCurrentStoreId() {
        return Mage::app()->getStore()->getId();
    }

    public function getConfigApp() {
        $country_code = Mage::getStoreConfig('general/country/default');
        $country = Mage::getModel('directory/country')->loadByCode($country_code);
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        $options = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option['value']) {
                $values[] = array(
                    'label' => $option['label'],
                    'value' => $option['value'],
                );
            }
        }
		//hainh customize
		if($locale=='ar_SA'){
			$locale="ar_AR";
		}
		//end
        //King RTL 5/7/2015
        $rtlCountry = Mage::getStoreConfig('connector/general/rtl_country', Mage::app()->getStore()->getId());
        $isRtl = '0';
        $rtlCountry = explode(',', $rtlCountry);
        if(in_array($country_code, $rtlCountry)){
            $isRtl = '1';
        } 
        //end King
        $data = array(
            'store_config' => array(
                'country_code' => $country->getId(),
                'country_name' => $country->getName(),
                'locale_identifier' => $locale,
				//hainh customer don't want symbol
                'currency_symbol' => NULL,
				//end
                'currency_code' => $currencyCode,
				'currency_position' => $this->getCurrencyPosition(),
                'store_id' => $this->getCurrentStoreId(),
                'store_name' => Mage::app()->getStore()->getName(),
				'store_code' => Mage::app()->getStore()->getCode(),
				'is_show_zero_price' => Mage::getStoreConfig('connector/general/is_show_price_zero'),
				'is_show_link_all_product' => Mage::getStoreConfig('connector/general/is_show_all_product'),
				'use_store' => Mage::getStoreConfig('web/url/use_store'),
                // 'is_use_default_address' => Mage::getStoreConfig('connector/general/is_use_default_address'),
                'is_reload_payment_method' => Mage::getStoreConfig('connector/general/is_reload_payment_method'),
                'is_rtl' => $isRtl,
            ),
            'customer_address_config' => array(
                'prefix_show' => Mage::getStoreConfig('customer/address/prefix_show'),
                'suffix_show' => Mage::getStoreConfig('customer/address/suffix_show'),
                'dob_show' => Mage::getStoreConfig('customer/address/dob_show'),
                'taxvat_show' => Mage::getStoreConfig('customer/address/taxvat_show'),
                'gender_show' => Mage::getStoreConfig('customer/address/gender_show'),
                'gender_value' => $values,
            ),
            'checkout_config' => array(
                'enable_guest_checkout' => Mage::getStoreConfig('checkout/options/guest_checkout'),
                'enable_agreements' => is_null(Mage::getStoreConfig('checkout/options/enable_agreements')) ? 0 : Mage::getStoreConfig('checkout/options/enable_agreements'),
				'taxvat_show' => Mage::getStoreConfig('customer/create_account/vat_frontend_visibility'),
            ),
			'view_products_default'=>Mage::getStoreConfig('connector/general/show_product_type'),
            'android_sender' => Mage::getStoreConfig('connector/android_sendid'),
			//hainh customize
			'carriercode'=> array('50','51','52','53','54','55','56','57','58','59'),
			'citylist'=>$this->getCityList(),
			'image_login'=> array(
				'is_show_image_login' => Mage::getStoreConfig('connector/general/is_show_image_login'),
				'login_image' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'simi/simiconnector/imagelogin/'.Mage::getStoreConfig('connector/general/login_image'),
				'login_link' => Mage::getStoreConfig('connector/general/login_link'),
			),
			//end
        );
        $information = $this->statusSuccess();
        $information['data'] = array($data);
        return $information;
    }

    public function getBannerList() {
        $list = Mage::getModel('connector/banner')->getBannerList();
        if (count($list)) {
            $information = $this->statusSuccess();
            $information['data'] = $list;
            return $information;
        } else {
            $information = $this->statusError();
            return $information;
        }
    }

    public function getMerchantInfo() {
        $website_id = Mage::app()->getWebsite()->getId();
        $listBlock = Mage::getModel('connector/cms')->getCollection()
                ->addFieldToFilter('website_id', array('in' => array($website_id, 0)))
                ->addFieldToFilter("cms_status", 1);
        $data = array();
		$helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();  
        foreach ($listBlock as $block) {
            $path = Mage::getBaseUrl('media') . 'simi/simicart/cms' . '/' . $block->getWebsiteId() . '/' . $block->getCmsImage();
            $data[] = array(
                "title" => $block->getCmsTitle(),
                "content" => $processor->filter($block->getCmsContent()),
                "icon" => $path,
            );
        }

        $information = $this->statusSuccess();
        $information['data'] = $data;
        return $information;
    }

    /**
     * 
     * @param type $data
     */
    public function saveConfigWebsite($logo_url, $web_id) {
        $logo = Mage::helper('connector')->getDirLogoImage($web_id);
        $url = $logo_url;
        file_put_contents($logo, file_get_contents($url));
    }

    public function getListPlugin($device_id) {
        $plugins = Mage::getModel('connector/plugin')->getListPlugin($device_id);
		$data = array();
        if ($plugins->getSize()) {            
            foreach ($plugins as $plugin) {
//                if ($this->checkPlugin($plugin->getPluginSku())) {
                    $data[] = array(
                        'name' => $plugin->getPluginName(),
                        'version' => $plugin->getPluginVersion(),
                        'sku' => $plugin->getPluginSku(),
                    );
//                }
            }
            
        }
		$information = $this->statusSuccess();
        $information['data'] = $data;
        return $information;
    }

    public function checkPlugin($sku_plugins) {
        $modules = Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $moduleName => $moduleInfo) {
            if (strcmp(strtolower($sku_plugins), strtolower($moduleName))) {
                if ($moduleInfo->active == true) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }
	
	public function getCurrencyPosition(){
//hainh
return 'before';

if ((Mage::app()->getStore()->getCode() == 'uae_en') || (Mage::app()->getStore()->getCode() == 'ksa_en'))
	return 'after';
else 
	return 'before';
//end		
		$formated = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0);		
        $number = Mage::app()->getStore()->getCurrentCurrency()->formatTxt(0, array('display' => Zend_Currency::NO_SYMBOL));
		// Zend_debug::dump($number);
		 $ar_curreny = explode($number,$formated);
		if ($ar_curreny['0'] != ''){
			return 'before';
		}
		return 'after';
	}
	
	//hainh customize
	function getCityList() {
		//zend_debug::dump(Mage::app()->getStore()->getCode());die;
		if (strpos(Mage::app()->getStore()->getCode() ,'ae_en') !== false) {
			return explode(',','Abu Dhabi,Ajman,Al Ain,Dubai,Fujairah,Khor Fakkan,Ras al-Khaimah,Sharjah,Umm al-Quwain');
		} 
		if (strpos(Mage::app()->getStore()->getCode() ,'ae_ar') !== false) {
			return explode(',','ابو ظبي,عجمان,العين,دبي,الفجيرة,خور فكان,رأس الخيمة,الشارقة,أم القيوين');
		} 
		if (strpos(Mage::app()->getStore()->getCode() ,'sa_ar') !== false) {
			return explode(',','أبها,ابقيق,عفيف,الأحساء,الباحة,آل جعفر,الجوف,الخرج,العيون,العلا,الرس,عرعر,السليل,الزايمة,بدر,بلجرشي,بيشة,البقاع,بريدة,دهبان,الدمام,الدوادمي,الظهران,ضرما,الدرعية,ضباء,حرمة,الهفوف,حوطة بني تميم,جبل أم الرؤوس,جلاجل,جدة,جازان,مدينة جازان الاقتصادية,الجبيل,دومة الجندل,درة العروس,مدينة فرسان,جرهاء,القريات,حائل,حفر الباطن,الحجرة,حقل,الخفجي,خميس مشيط,خيبر,مدينه الخبر,مدينة الملك عبد الله الاقتصادية,مدينة المعرفة الاقتصادية,ليلى,لحيان,مكة المكرمة,المدينة المنورة,المزاحمية,نجران,أملج,القضيمه,القيصومة,القطيف,شقراء,شرورة,شيبة,تبوك,الطائف,تنومه,تاروت,تيماء,ثادق,رابغ,رفحاء,رأس تنورة,الرياض,الرميلة,سبت العلايا,الصفوة,سيهات,سكاكا,الثقبة,ثول,طريف,العضيلية,ام الساهك,عنيزة,العقير,العيينة,وادي الدواسر,ينبع,الزلفي');
		} 
		$cityArray = explode('**','Abha**Abqaiq**Afif**Al Ahsa**Al Bahah**Al Jafer**Al Jawf**Al Kharj**Al Oyoon**Al Ula**Ar Rass**Arar**As Sulayyil**Az Zaimah**Badr**Baljurashi**Bisha**Buqaa**Buraydah**Dahaban**Dammam**Dawadmi**Dhahran**Dhurma**Diriyah**Duba**Harmah**Hofuf**Hotat Bani Tamim**Jabab Umm al Ru’us**Jalajil**Jeddah**Jizan**Jizan Economic City**Jubail**Dumat Al Jandal**Durat Al Arouss**Farasan City**Gerrha**Gurayat**Ha’il**Hafr Al Batin**Hajrah**Haql**Khafji**Khamis Mushayt**Khaybar**Khobar**King Abdullah Economic City**Knowledge Economic City, Medina**Layla**Lihyan**Makkah**Medina**Muzhmiyya**Najran**Omloj**Qadeimah**Qaisumah**Qatif**Shaqraa**Sharurah**Shaybah**Tabuk**Taif**Tanomah**Tarout**Tayma**Thadiq**Rabigh**Rafha**Ras Tanura**Riyadh**Rumailah**Sabt Al Alaya**Safwa**SaihatSakakah**Thuqbah**Thuwal**Turaif**Udhailiyah**Um Al Sahek**Unaizah**Uqair**Uyayna**Wadi Al Dawasir**Yanbu**Zulfi');
		foreach ($cityArray as $index=>$city) {
			$cityArray[$index] = $city;
		}
		return $cityArray;
	}
	
	//end
}