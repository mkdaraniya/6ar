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
    public function getSACities()
    {
        $helper = Mage::helper('directory');
        $cities = array(
            'AHB'=>'000000000012',
            'AFIF'=>'000000000100',
            'HSA'=>'000000000108',
            'ABT'=>'000000000019',
            'AJF'=>'000000000044',
            'XWP'=>'000000000054',
            'LITH'=>'000000000060',
            'QNF'=>'000000000073',
            'WAJH'=>'000000000104' ,
            'QUWAIAH'=>'000000000033',
            'HARIQ'=>'000000000036',
            'ULAH'=>'000000000098',
            'RAE'=>'000000000011',
            'SULAYYIL'=>'000000000087',
            'BADR'=>'000000000015',
            'Baljurashi'=>'000000000016',
            'BHH'=>'000000000017',
            'ELQ'=>'000000000018',
            'QDM'=>'000000000021',
            'DWD'=>'000000000029',
            'DHA'=>'000000000022',
            'DUBA'=>'000000000026',
            'DAWMATJANDAL'=>'000000000027',
            'FARASANISLAND'=>'000000000030',
            'URY'=>'000000000032' ,
            'HAS'=>'000000000038',
            'HBT'=>'000000000041',
            'HAJRAH'=>'000000000034',
            'HAQL'=>'000000000035',
            //'HSA'=>'000000000040',
            'HOTATAMIM'=>'000000000039',
            'JALAJIL'=>'000000000043',
            'JED'=>'000000000045',
            'GZN'=>'000000000046',
            //'GZN'=>'000000000047',
            'QJB'=>'000000000048',
            'KHF'=>'000000000050',
            'KHM'=>'000000000053',
            'KHAIBAR'=>'000000000051',
            'OBR'=>'000000000056',
            'MED'=>'000000000055',
            'LAYLA'=>'000000000058',
            'MAK'=>'000000000434',
           // 'MAK'=>'000000000063',
            //'MED'=>'000000000064',
            'MUZAHIMIAH'=>'000000000065',
            'EAM'=>'000000000066',
            'QAYSUMAH'=>'000000000072',
            'QAT'=>'000000000071',
            'RBG'=>'000000000074',
            'RAH'=>'000000000075',
            'RAS'=>'000000000077',
            'RUH'=>'000000000078',
            'SABTALAYA'=>'000000000080',
            'SEI'=>'000000000081',
           // 'AJF'=>'000000000083',
            'SHQ'=>'000000000085',
            'SHW'=>'000000000084',
            'SHAYBAH'=>'000000000086',
            'TUU'=>'000000000089',
            'TIF'=>'000000000088',
            'TANOMAH'=>'000000000090',
            'TAR'=>'000000000091',
            'TAYMA' =>'000000000092',
            'TUI'=>'000000000096',
            'UDALIYA'=>'000000000097',
            //'ELQ'=>'000000000010',
            'WAE'=>'000000000103',
            'YNB'=>'000000000105',
            'ZULFI'=>'000000000107',
            'ALRASS'=>'000000000076',
            'TADIQ'=>'000000000093'
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
    public function getSACity()
    {
        $cities = $this->getSACities();
        return json_encode($cities);
    }
}