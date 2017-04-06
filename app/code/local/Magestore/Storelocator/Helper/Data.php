<?php

class Magestore_Storelocator_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * save image icon
     */
    public function saveIcon($flie, $id) {

        $this->createImageIcon($flie, $id);
    }

    /**
     * save images Store
     */
    public function saveImageStore($images, $id, $file, $radio) {
        foreach ($images as $item) {
            $mod = Mage::getModel('storelocator/image');
            $file_name = $file['images_id' . $item['options']]['name'];
            $name_image = $this->renameImage($file_name, $id, $item['options']);
            if ($item['delete'] == 0) {
                $last = $mod->getCollection()->getLastItem()->getData('options') + 1;
                $mod->setData('storelocator_id', $id);
                if (($name_image != "") && isset($name_image) != NULL) {
                    $mod->setData('name', $name_image);
                    $this->createImage($name_image, $id, $last, $item['options']);
                }
                if ($item['options'] == $radio) {
                    $mod->setData('statuses', 1);
                } else {
                    $mod->setData('statuses', 0);
                }
                $mod->setData('image_delete', 2);
                $mod->setData('options', $last);
                $mod->save();
            } else if ($item['delete'] == 2) {
                if (($name_image != "") && isset($name_image) != NULL) {
                    $mod->setData('name', $name_image)->setId($item['id']);
                    $this->createImage($name_image, $id, $item['options'], $item['options']);
                }
                //$mod->setData('link', $item['link'])->setId($item['id']);    
                if ($item['options'] == $radio) {
                    $mod->setData('statuses', 1);
                } else {
                    $mod->setData('statuses', 0);
                }
                $mod->setData('image_delete', $item['delete'])->setId($item['id']);
                $mod->save();
            } else {
                if ($item['id'] != 0) {
                    if (($name_image != "") && isset($name_image) != NULL) {
                        $mod->setData('name', $name_image)->setId($item['id']);
                        $this->createImage($name_image, $id, $item['options'], $item['options']);
                    }
                    if ($item['options'] == $radio) {
                        $mod->setData('statuses', 1);
                    } else {
                        $mod->setData('statuses', 0);
                    }
                    $mod->setData('image_delete', $item['delete'])->setId($item['id']);
                    $mod->save();
                }
            }
        }
        $this->deleteImageStore();
    }

    private function renameImage($image_name, $store_id, $id_img) {

        $name = "";
        if (isset($image_name) && ($image_name != null)) {
            $array_name = explode('.', $image_name);
            $array_name[0] = $store_id . '_' . $id_img;
            $name = $array_name[0] . '.' . end($array_name);
        }
        return $name;
    }

    /**
     * 
     * @param type $url
     * call response return content
     */
    public function getResponseBody($url) {
        if (ini_get('allow_url_fopen') != 1) {
            @ini_set('allow_url_fopen', '1');
        }

        if (ini_get('allow_url_fopen') != 1) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $contents = curl_exec($ch);
            curl_close($ch);
        } else {
            $contents = file_get_contents($url);
        }

        return $contents;
    }

    public function getConfig($nameConfig) {
        return Mage::getStoreConfig('storelocator/general/' . $nameConfig);
    }

    /**
     * return list country in magento
     */
    public function getOptionCountry() {
        $optionCountry = array();
        $collection = Mage::getResourceModel('directory/country_collection')
                ->loadByStore();
        if (count($collection)) {
            foreach ($collection as $item) {
                $optionCountry[] = array('value' => $item->getId(), 'label' => $item->getName());
            }
        }

        return $optionCountry;
    }

    public function tagArray() {
        $tag_array = array();
        $taglist = "";
        $collection = Mage::getModel('storelocator/storelocator')->getCollection();
        //$taglist->getSelect()->group('tag_store');
        foreach ($collection as $tag) {
            $taglist = $taglist . $tag;
        }
        $tag_array = explode(",", $taglist);
    }

    public function getListCountry() {
        $listCountry = array();

        $collection = Mage::getResourceModel('directory/country_collection')
                ->loadByStore();

        if (count($collection)) {
            foreach ($collection as $item) {
                $listCountry[$item->getId()] = $item->getName();
            }
        }

        return $listCountry;
    }

    /**
     * 
     * @param type $name
     * return url to show image Store with big image
     */
    public function getBigImagebyStore($id_store) {
        $collection = Mage::getModel('storelocator/image')->getCollection()->addFieldToFilter('storelocator_id', $id_store)->addFieldToFilter('image_delete', 2);
        $url = "";
        foreach ($collection as $item) {
            if ($item->getData('name')) {
                if ($item->getData('statuses') == 1) {
                    $url = Mage::getBaseUrl('media') . 'storelocator/images/' . $id_store . '/' . $item->getData('options') . '/' . $item->getData('name');
                    break;
                } else {
                    $url = Mage::getBaseUrl('media') . 'storelocator/images/' . $id_store . '/' . $item->getData('options') . '/' . $item->getData('name');
                }
            }
        }
        return $url;
    }

    /**
     * delete image (back-end)
     */
    public function deleteImageStore() {
        $image_info = Mage::getModel('storelocator/image')->getCollection()->addFilter('image_delete', 1);
        foreach ($image_info as $item) {
            $id = $item->getData('storelocator_id');
            $option = $item->getData('options');
            $image = $item->getData('name');

            $image_path = $this->getImagePath($id, $option) . DS . $image;
            $image_path_cache = $this->getImagePathCache($id, $option) . DS . $image;
            try {
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                if (file_exists($image_path_cache)) {
                    unlink($image_path_cache);
                }
            } catch (Exception $e) {
                
            }
        }
    }

    public function getDataImage($id) {
        $collection = Mage::getModel('storelocator/image')->getCollection()->addFilter('storelocator_id', $id);
        return $collection;
    }

    public function getImageUrlJS() {
        $url = Mage::getBaseUrl('media') . 'storelocator/images/';
        return $url;
    }

    public function getImagePath($store_id, $options) {
        $path = Mage::getBaseDir('media') . DS . 'storelocator' . DS . 'images' . DS . $store_id . DS . $options;
        return $path;
    }

    public function getImagePathCache($id, $options) {
        $path = Mage::getBaseDir('media') . DS . 'storelocator' . DS . 'images' . DS . 'cache' . DS . $id . DS . $options;
        return $path;
    }

    public function createImage($image, $id, $last, $options) {
        try {
            /* Starting upload */
            $uploader = new Varien_File_Uploader('images_id' . $options);
            // Any extention would work
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            // We set media as the upload dir
            $path = $this->getImagePath($id, $last);
            $uploader->save($path, $image);
        } catch (Exception $e) {
            
        }
    }

    public function createImageIcon($flie, $id) {
        try {
            /* Starting upload */
            $uploader = new Varien_File_Uploader($flie);
            // Any extention would work
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            // We set media as the upload dir
            $path = $this->getPathImageIcon($id);
            $uploader->save($path, $flie['name']);
            $this->reSizeImage($id, $flie['name']);
        } catch (Exception $e) {
            
        }
    }

    public function deleteImageIcon($id, $image) {
        $image_path = Mage::getBaseDir('media') . DS . 'storelocator' . DS . 'images' . DS . 'icon' . DS . $id . DS . $image;
        try {
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        } catch (Exception $e) {
            
        }
    }

    public function getPathImageIcon($id) {
        $path = Mage::getBaseDir('media') . DS . 'storelocator' . DS . 'images' . DS . 'icon' . DS . $id;
        return $path;
    }

    public function reSizeImage($id, $nameimage) {
        $_imageUrl = $this->getPathImageIcon($id) . DS . $nameimage;
        $imageResized = $this->getPathImageIcon($id) . DS . 'resize' . DS . $nameimage;
        if (!file_exists($imageResized) && file_exists($_imageUrl)) {
            $imageObj = new Varien_Image($_imageUrl);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize(18, 22);
            $imageObj->save($imageResized);
        }
    }

    public function saveTagToStore($tags, $storeId) {
        $this->deleteTagFormStore($storeId);
//        Zend_debug::dump($tags);die();
        foreach ($tags as $tag) {
            $modelTag = Mage::getModel('storelocator/tag');
            $modelTag->setData('value', $tag);
            $modelTag->setData('storelocator_id', $storeId);
            $modelTag->save();
        }
    }

    public function deleteTagFormStore($storeId) {
        if ($storeId) {
            $collectionTag = Mage::getModel('storelocator/tag')->getCollection()
                    ->addFieldToFilter('storelocator_id', $storeId);
            foreach ($collectionTag as $tag) {
                $tag->delete();
            }
        }
    }

    public function getTags($storeId) {
        if ($storeId) {
            $collectionTag = Mage::getModel('storelocator/tag')->getCollection()
                    ->addFieldToFilter('storelocator_id', $storeId);
            $tags = '';

            foreach ($collectionTag as $tag) {
                $tags .= $tag->getValue() . ',';
            }
            return substr($tags, 0, -1);
        }
        return '';
    }

    public function getImageNameByStore($storeId) {
        if ($storeId) {

            $collectionImages = Mage::getModel('storelocator/image')->getCollection()
                    ->addFieldToFilter('storelocator_id', $storeId);
            $image_names = "";
            foreach ($collectionImages as $image) {
                $image_names .= $image->getName() . ',';
            }

            return $image_names;
        }
        return '';
    }

    public function deleteImageFormStore($storeId) {
        if ($storeId) {
            $collectionImage = Mage::getModel('storelocator/image')->getCollection()
                    ->addFieldToFilter('storelocator_id', $storeId);
            foreach ($collectionImage as $image) {
                $image->delete();
            }
        }
    }

    public static function getStoreOptions() {
        $options = array();
        $collection = Mage::getModel('storelocator/storelocator')->getCollection()
                ->setOrder('name', 'ASC');
        foreach ($collection as $store) {
            $option = array();
            $option['label'] = $store->getName();
            $option['value'] = $store->getId();
            $options[] = $option;
        }

        return $options;
    }

    public function getSpecialDays($storeId) {
        $specialdays = Mage::getModel('storelocator/specialday')
                ->getCollection()
                ->addFieldToFilter('store_id', array('finset' => $storeId));

        $day_show = Mage::getStoreConfig('storelocator/general/show_spencial_days', Mage::app()->getStore()->getStoreId());
        $dateLimit = date('Y-m-d', strtotime('+' . $day_show . ' day'));
        $dateLimit = str_replace('-', '', $dateLimit);
        $count = 0;
        $days = array();
        foreach ($specialdays as $specialday) {
            $dateFrom = str_replace('-', '', $specialday->getDate());
            $dateTo = str_replace('-', '', $specialday->getSpecialdayDateTo());
            for ($i = $dateFrom; $i <= $dateTo; $i++) {
                if ($i <= $dateLimit) {
                    $yyy = substr((string) $i, 0, 4);
                    $mm = substr((string) $i, 4, 2);
                    $dd = substr((string) $i, 6, 2);
                    if(0 < (int)$dd && (int)$dd < 32){
                        $j = $yyy . '-' . $mm . '-' . $dd;
                        $days[$count]['date'] = $j;
                        $days[$count]['time_open'] = $specialday->getSpecialdayTimeOpen();
                        $days[$count]['time_close'] = $specialday->getSpecialdayTimeClose();

                        $count++;
                    }
                }
            }
        }

        for ($k = 0; $k < count($days); $k++) {
            for ($l = $k + 1; $l < count($days); $l++) {
                if (strtotime($days[$l]['date']) < strtotime($days[$k]['date'])) {
                    $temp = $days[$k];
                    $days[$k] = $days[$l];
                    $days[$l] = $temp;
                }
            }
        }
        return $days;
    }

    public function getHolidayDays($storeId) {
        $holidays = Mage::getModel('storelocator/holiday')
                ->getCollection()
                ->addFieldToFilter('store_id', array('finset' => $storeId));
        
        $specialdays = $this->getSpecialDays($storeId);
        
        $days = array();
        $day_show = Mage::getStoreConfig('storelocator/general/show_spencial_days', Mage::app()->getStore()->getStoreId());
        $dateLimit = date('Y-m-d', strtotime('+' . $day_show . ' day'));
        $dateLimit = str_replace('-', '', $dateLimit);
        $count = 0;
        foreach ($holidays as $holiday) {
            $dateFrom = str_replace('-', '', $holiday->getDate());
            $dateTo = str_replace('-', '', $holiday->getHolidayDateTo());
            for ($i = $dateFrom; $i <= $dateTo; $i++) {
                if ($i <= $dateLimit) {
                    $yyy = substr((string) $i, 0, 4);
                    $mm = substr((string) $i, 4, 2);
                    $dd = substr((string) $i, 6, 2);
                    if(0 < (int)$dd && (int)$dd < 32){
                        $j = $yyy . '-' . $mm . '-' . $dd;
                        $check_specialday = false;
                        foreach($specialdays as $specialday){
                            if($j==$specialday['date']){
                                $check_specialday = true;
                            }
                        }
                        if(!$check_specialday)
                            $days[$count]['date'] = $j;


                        $count++;
                    }
                    
                }
            }
        }

       
        for ($k = 0; $k < count($days); $k++) {
            for ($l = $k + 1; $l < count($days); $l++) {
                if(isset($days[$l]) && isset($days[$k]))
                    if (strtotime($days[$l]['date']) < strtotime($days[$k]['date'])) {
                        $temp = $days[$k];
                        $days[$k] = $days[$l];
                        $days[$l] = $temp;
                    }
            }
        }
   
        return $days;
    }

    public function getSpecialdayOption() {
        $specialdays = Mage::getModel('storelocator/storelocator')->getCollection();
        $result = array();

        foreach ($specialdays as $specialday) {
            $result[$specialday->getId()] = $specialday->getName();
        }

        return $result;
    }

    public function filterDates($array, $dateFields) {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

}
