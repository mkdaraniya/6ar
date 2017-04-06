<?php

class Simi_Simicategory_Model_Simicategory extends Simi_Connector_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('simicategory/simicategory');
	}

	public function getCategoires(){
		$data = array();
		try{
			$collection = $this->getCollection();
			foreach($collection as $item){
				if($item->getStatus() == 1){
					$category=Mage::getModel('catalog/category')->load($item->getCategoryId());
					if(!$category->hasChildren()){
						$info = array(
							'category_id' => $item->getCategoryId(),
							'category_image' => $item->getSimicategoryFilename(),							
							'category_name' => $item->getSimicategoryName(),
							'has_child' => 'NO',
						);
					}else{
						$info = array(
							'category_id' => $item->getCategoryId(),
							'category_image' => $item->getSimicategoryFilename(),
							'category_name' => $item->getSimicategoryName(),
							'has_child' => 'YES',
						);
					}
					
					$data[] = $info;
				}
			}

			$information = $this->statusSuccess();
	        $information['data'] = $data;
       		return $information;
		}catch(Expetion $e){
			$message = $e->getMessage();
			$information = $this->statusError();
			$information['message'] = array($message);
			if(is_array($message)){
	        	$information['message'] = $message;
			}
       		return $information;
		}		
	}

	public function getCurrencies(){
		$currencies = array();
        $codes = Mage::app()->getStore()->getAvailableCurrencyCodes(true);   
        if (is_array($codes) && count($codes) > 1) {
        	
            $rates = Mage::getModel('directory/currency')->getCurrencyRates(
                Mage::app()->getStore()->getBaseCurrency(),
                $codes
            );

            foreach ($codes as $code) {
                if (isset($rates[$code])) {
                    $currencies[] = array(
                    	'value' => $code,
                    	'title' => Mage::app()->getLocale()->getTranslation($code, 'nametocurrency'),
                    	);                     
                }
            }
        }elseif (count($codes) == 1) {
        	# code...
        	$currencies[] = array(
            	'value' => $codes[0],
            	'title' => Mage::app()->getLocale()->getTranslation($codes[0], 'nametocurrency'),
            );   

        }
        $information = $this->statusSuccess();
        $information['data'] = $currencies;
   		return $information;
	}
}