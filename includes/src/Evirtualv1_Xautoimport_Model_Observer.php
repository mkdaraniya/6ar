<?php
class Evirtual_Xautoimport_Model_Observer {
	
	public static function licenseCheck(){
		
		$key = Mage::getStoreConfig('evirtual_xautoimport/general/key');
		//Zend_Debug::dump($key);
		//exit;
        Mage::helper('evirtual_xautoimport')->licenseCheck($key);
			
	}
	
	public static function xautoimport() {
		Mage::log("eyK1",null,"crontest.log");	
		$key = Mage::getStoreConfig('evirtual_xautoimport/general/key');
		//Mage::log("eyK",null,"crontest.log");	
		Mage::log("eyK2",null,"crontest.log");		
		if(Mage::helper('evirtual_xautoimport')->globlelicenseCheck($key)=='Valid'){
				$collection = Mage::getResourceModel('evirtual_xautoimport/profile_collection');
				$collection->getSelect()->where('`generate_day` like "%' . strtolower(date('D')) . '%"');
				Mage::log("eyK3",null,"crontest.log");	
				foreach ($collection as $_profile) {
				
					$profile=Mage::getModel('evirtual_xautoimport/profile')->load($_profile->getId());
				
					if (!$profile->getData('generate_status')){
						continue;
					}
					
					if (date('d.m.Y:H') == date('d.m.Y:H', strtotime($profile->getData('cron_started_at')))) {				
					//	continue;			//nelson
					}
					if (!Mage::helper('evirtual_xautoimport')->needRunCron($profile->getData('generate_interval'), 
								  $profile->getData('generate_hour'), 
								  $profile->getData('generate_hour_to'), 
								  $profile->getData('cron_started_at'))){
					//	continue;		 //nelson	  	
					} 
					
					try {				
						$cron_started_at = date('Y-m-j H:00:00', time());
						$profile->setData('cron_started_at', $cron_started_at);
						//$profile->save();
						Mage::helper('evirtual_xautoimport')->loadFile($profile->getUrl(),$profile->getProfileSourceType());
					//	Mage::log($profile,NULL,"crontest1.log");
						Mage::helper('evirtual_xautoimport')->RunImportProfile($profile);
		
						$profile->setData('restart_cron', 0);
						$cron_started_at = date('Y-m-j H:00:00', time());
						$profile->setData('last_run', $cron_started_at);
					//	$profile->save();	
								
					}
					catch (Exception $e) {
						$profile->setData('error', $e->getMessage());
						$profile->setData('restart_cron', intval($profile->getData('restart_cron')) + 1);
					//	$profile->save();				
						continue;			
					}		
				}	
		}else{
			Mage::log("Invalid Key or Empty Key");	
		}
	}
	
	
	
}