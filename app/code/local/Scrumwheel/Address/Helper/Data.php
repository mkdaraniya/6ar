<?php

/**
 * @category   Scrumwheel
 * @package    Scrumwheel_Carrierdropdown
 * @author     devangi.thakore@gmail.com
 */
class Scrumwheel_Address_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getUaeCarrierCodes()
    {
        $helper = Mage::helper('directory');
        $carriers = array(
            $helper->__('50'),
            $helper->__('52'),
            $helper->__('54'),
            $helper->__('55'),
            $helper->__('56'),
            $helper->__('Other'),
        );
        return $carriers;
    }

    public function getKsaCarrierCodes()
    {
        $helper = Mage::helper('directory');
        $carriers = array(
            $helper->__('50'),
            $helper->__('53'),
            $helper->__('54'),
            $helper->__('55'),
            $helper->__('56'),
            $helper->__('57'),
            $helper->__('58'),
            $helper->__('59'),
            $helper->__('Other'),
        );
        return $carriers;
    }
    
    public function getCountryCodes()
    {
        $helper = Mage::helper('directory');
        $carriers = array(
            $helper->__('+966'),
            $helper->__('+971'),
            $helper->__('Other'),
        );
        return $carriers;
    }

    public function getUaeCarrierAsDropdown($selectedCity = '')
    {
        $carriers = $this->getUaeCarrierCodes();
        $options = '';
        foreach ($carriers as $city) {
            $isSelected = $selectedCity == $city ? ' selected="selected"' : null;
            $options .= '<option value="' . $city . '"' . $isSelected . '>' . $city . '</option>';
        }
        return $options;
    }

    public function getKsaCarrierAsDropdown($selectedCity = '')
    {
        $carriers = $this->getKsaCarrierCodes();
        $options = '';
        foreach ($carriers as $city) {
            $isSelected = $selectedCity == $city ? ' selected="selected"' : null;
            $options .= '<option value="' . $city . '"' . $isSelected . '>' . $city . '</option>';
        }
        return $options;
    }
    
    public function getCountryCodeAsDropdown($selectedCountry = '')
    {
        $codes = $this->getCountryCodes();
        $options = '';
        foreach ($codes as $country) {
            $isSelected = $selectedCountry == $country ? ' selected="selected"' : null;;
            $options .= '<option value="' . $country . '"' . $isSelected . '>' . $country . '</option>';
        }
        return $options;
    }
    /*City Codes For KSA*/
  /*City Codes For KSA_en*/
    public function getSACities()
    {
        $helper = Mage::helper('directory');
        $cities = array(
            "Abha"=>"000000000012",
            "Abqaiq"=>"000000000013",
            "Afif"=>"000000000100",
            "Al Ahsa"=>"000000000108",
            "Al Bahah"=>"000000000019",
            "Al Hofuf Al Sharqia"=>"000000000684",
            "Al Jafer"=>"000000000049",
            "Al Jawf"=>"000000000044",
            "Al Jubail"=>"000000000685",
            "Al Kharj"=>"000000000054",
            "Al Khobar"=>"000000000686",
            "Al Lith"=>"000000000060",
            "Al Oyun - Hofuf"=>"000000000687",
            "Al Qatif"=>"000000000688",
            "Al Qunfudhah"=>"000000000073",
            "Al Wajh"=>"000000000104",
            "Al-Abwa"=>"000000000014",
            "Al-Gwei iyyah"=>"000000000033",
            "Al-Hareeq"=>"000000000036",
            "Al-Khutt"=>"000000000057",
            "Al-Mubarraz"=>"000000000062",
            "Al-Omran"=>"000000000068",
            "Al-Oyoon"=>"000000000069",
            "Al-Ula"=>"000000000098",
            "Ar Rass"=>"000000000076",
            "Arar"=>"000000000011",
            "As Sulayyil"=>"000000000087",
            "Ath Thybiyah"=>"000000000689",
            "Az Zaimah"=>"000000000106",
            "Badr"=>"000000000015",
            "Baljurashi"=>"000000000016",
            "Baqayq - Hofuf"=>"000000000690",
            "Bisha"=>"000000000017",
            "Buqaa"=>"000000000020",
            "Buraydah"=>"000000000018",
            "Dahaban"=>"000000000024",
            "Dammam"=> "000000000021",
            "Dawadmi"=>"000000000029",
            "Dhahran"=>"000000000022",
            "Dhurma"=>"000000000023",
            "Diriyah"=>"000000000025",
            "Duba"=>"000000000026",
            "Dumat Al-Jandal"=>"000000000027",
            "Durat Al Arouss"=>"000000000028",
            "Farasan city"=>"000000000030",
            "Gerrha"=>"000000000031",
            "Gurayat"=>"000000000032",
            "Ha il"=>"000000000038",
            "Hafr Al-Batin"=>"000000000041",
            "Hajrah"=>"000000000034",
            "Haql"=>"000000000035",
            "Harmah"=>"000000000037",
            "Hofuf"=>"000000000040",
            "Hotat Bani Tamim"=>"000000000039",
            "Jabal Umm al Ru us"=>"000000000042",
            "Jalajil"=>"000000000043",
            "Jeddah"=>"000000000045",
            "Jizan"=>"000000000046",
            "Jizan Economic City"=>"000000000047",
            "Jubail"=>"000000000048",
            "Khafji"=>"000000000050",
            "Khamis Mushayt"=>"000000000053",
            "Khaybar"=>"000000000051",
            "Khobar"=>"000000000056",
            "King Abdullah Economic City"=>"000000000052",
            "King Khalid Military City"=>"000000000691",
            "Knowledge Economic City , Medina"=>"000000000055",
            "Layla"=>"000000000058",
            "Lihyan"=>"000000000059",
            "Majmaah"=>"000000000692",
            "Makkah"=>"000000000063",
            "Mastoorah"=>"000000000061",
            "Medina"=>"000000000064",
            "Muzahmiyya"=>"000000000065",
            "Najran"=>"000000000066",
            "Omloj"=>"000000000067",
            "Onaiza"=>"000000000682",
            "Qadeimah"=>"000000000070",
            "Qaisumah"=>"000000000072",
            "Qassim"=>"000000000693",
            "Qatif"=>"000000000071",
            "Rabigh"=>"000000000074",
            "Rafha"=>"000000000075",
            "Ras Tanura"=>"000000000077",
            "Riyadh"=>"000000000078",
            "Rumailah"=>"000000000079",
            "Sabt Al Alaya"=>"000000000080",
            "Safwa"=>"000000000082",
            "Saihat"=>"000000000081",
            "Sakakah"=>"000000000083",
            "Shaqraa"=>"000000000085",
            "Sharurah"=>"000000000084",
            "Shaybah"=>"000000000086",
            "Tabuk"=>"000000000089",
            "Taif"=>"000000000088",
            "Tanomah"=>"000000000090",
            "Tarout"=>"000000000091",
            "Tayma"=>"000000000092",
            "Thadiq"=>"000000000093",
            "Thuqbah"=>"000000000095",
            "Thuwal"=>"000000000094",
            "Turaif"=>"000000000096",
            "Udhailiyah"=>"000000000097",
            "Um Al-Sahek"=>"000000000099",
            "Unaizah"=>"000000000010",
            "Uqair"=>"000000000101",
            "Uyayna"=>"000000000102",
            "Wadi Al-Dawasir"=>"000000000103",
            "Yanbu"=>"000000000105",
            "Zulfi"=>"000000000107",
        );
        return $cities;
    }
    /*City Codes For KSA_ar*/
    public function getSA_ArCities()
    {
        $helper = Mage::helper('directory');
        $cities = array(
            "أبها"=>"000000000012",
            "ابقيق"=>"000000000013",
            "عفيف"=>"000000000100",
            "الأحساء"=>"000000000108",
            "الباحة"=>"000000000019",
            "آل جعفر"=>"000000000049",
            "الجوف"=>"000000000044",

            "الخرج"=>"000000000054",
            "العيون"=>"000000000069",
            "العلا"=>"000000000098",
            "الرس"=>"000000000076",
            "عرعر"=>"000000000011",
            "السليل"=>"000000000087",
            "الزايمة"=>"000000000106",
            "بدر"=>"000000000015",
            "بلجرشي"=>"000000000016",
            "بيشة"=>"000000000017",
            "البقاع"=>"000000000020",
            "بريدة"=>"000000000018",
            "دهبان"=>"000000000024",
            "الدمام"=> "000000000021",
            "الدوادمي"=>"000000000029",
            "الظهران"=>"000000000022",
            "ضرما"=>"000000000023",
            "الدرعية"=>"000000000025",
            "ضباء"=>"000000000026",
            "دومة الجندل"=>"000000000027",
            "درة العروس"=>"000000000028",
            "مدينة فرسان"=>"000000000030",
            "جرهاء"=>"000000000031",
            "القريات"=>"000000000032",
            "حائل"=>"000000000038",
            "حفر الباطن"=>"000000000041",
            "الحجرة"=>"000000000034",
            "حقل"=>"000000000035",
            "حرمة"=>"000000000037",
            "الهفوف"=>"000000000040",
            "حوطة بني تميم"=>"000000000039",
            "جبل أم الرؤوس"=>"000000000042",
            "جلاجل"=>"000000000043",
            "جدة"=>"000000000045",
            "جازان"=>"000000000046",
            "مدينة جازان الاقتصادية"=>"000000000047",
            "الجبيل"=>"000000000048",
            "الخفجي"=>"000000000050",
            "خميس مشيط"=>"000000000053",
            "خيبر"=>"000000000051",
            "مدينه الخبر"=>"000000000056",
            "مدينة الملك عبد الله الاقتصادية"=>"000000000052",
            "مدينة المعرفة الاقتصادية"=>"000000000055",
            "ليلى"=>"000000000058",
            "لحيان"=>"000000000059",
            "مكة المكرمة"=>"000000000063",
            "المدينة المنورة"=>"000000000064",
            "المزاحمية"=>"000000000065",
            "نجران"=>"000000000066",
            "أملج"=>"000000000067",
            "القضيمه"=>"000000000070",
            "القيصومة"=>"000000000072",
            "القطيف"=>"000000000071",
            "رابغ"=>"000000000074",
            "رفحاء"=>"000000000075",
            "رأس تنورة"=>"000000000077",
            "الرياض"=>"000000000078",
            "الرميلة"=>"000000000079",
            "سبت العلايا"=>"000000000080",
            "الصفوة"=>"000000000082",
            "سيهات"=>"000000000081",
            "سكاكا"=>"000000000083",
            "شقراء"=>"000000000085",
            "شرورة"=>"000000000084",
            "شيبة"=>"000000000086",
            "تبوك"=>"000000000089",
            "الطائف"=>"000000000088",
            "تنومه"=>"000000000090",
            "تاروت"=>"000000000091",
            "تيماء"=>"000000000092",
            "ثادق"=>"000000000093",
            "الثقبة"=>"000000000095",
            "ثول"=>"000000000094",
            "طريف"=>"000000000096",
            "العضيلية"=>"000000000097",
            "ام الساهك"=>"000000000099",
            "عنيزة"=>"000000000010",
            "العقير"=>"000000000101",
            "العيينة"=>"000000000102",
            "وادي الدواسر"=>"000000000103",
            "ينبع"=>"000000000105",
            "الزلفي"=>"000000000107",
        );
        return $cities;
    }

    public function getSACitiesAsDropdown($selectedCity = '')
    {
        $cities = $this->getSACities();
        $options = '';
        foreach($cities as $key=>$value){
            $isSelected = $selectedCity == $key ? ' selected="selected"' : null;
            $options .= '<option value="' . $key . '"' . $isSelected . '>' . $key . '</option>';
        }
        return $options;
    }

   public function getSA_ArCitiesAsDropdown($selectedCity = '')
    {
        $cities = $this->getSA_arCities();
        $options = '';
        foreach($cities as $key=>$value){
            $isSelected = $selectedCity == $key ? ' selected="selected"' : null;
            $options .= '<option value="' . $key . '"' . $isSelected . '>' . $key . '</option>';
        }
        return $options;
    }
    public function getSACity()
    {
        $cities = $this->getSACities();
        return json_encode($cities);
    }

    public function getSA_ArCity()
    {
        $cities = $this->getSA_ArCities();
        return json_encode($cities);
    }
    public function getSACityId($city)
    {
        $cities = $this->getSACities();
        return $cities[$city];
    }
    public function getSA_ArCityId($city)
    {
        $cities = $this->getSA_ArCities();
        return $cities[$city];
    }
}