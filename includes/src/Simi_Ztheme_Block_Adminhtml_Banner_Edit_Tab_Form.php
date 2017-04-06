<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Ztheme
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Ztheme Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Ztheme
 * @author      Magestore Developer
 */
class Simi_Ztheme_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Simi_Ztheme_Block_Adminhtml_Ztheme_Edit_Tab_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);


        if (Mage::getSingleton('adminhtml/session')->getZthemeData()) {
            $data = Mage::getSingleton('adminhtml/session')->getZthemeData();
            Mage::getSingleton('adminhtml/session')->setZthemeData(null);
        } elseif (Mage::registry('ztheme_banner_data')) {
            $data = Mage::registry('ztheme_banner_data')->getData();
        }


        $fieldset = $form->addFieldset('ztheme_form', array(
            'legend' => Mage::helper('ztheme')->__('Banner information')
        ));

		//hainh customize
		$stores = Mage::getModel('core/store')->getCollection();
        $list_store = array();
        foreach ($stores as $store) {
            $list_store[] = array(
                'value' => $store->getId(),
                'label' => $store->getGroup()->getName() .' - '.$store->getName(),
            );
        }
		/*
        $fieldset->addField('website_id', 'select', array(
            'label' => Mage::helper('ztheme')->__('Website'),
            'name' => 'website_id',
            'values' => Mage::getSingleton('ztheme/status')->getWebsite(),
            'disabled' => true
        ));
		*/
		
		$fieldset->addField('website_id', 'select', array(
            'label' => Mage::helper('ztheme')->__('Store View'),
            'name' => 'website_id',
            'values' => $list_store
        ));
		//end

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('ztheme')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('ztheme/status')->getOptionHash(),
        ));

        $fieldset->addField('banner_title', 'text', array(
            'label' => Mage::helper('ztheme')->__('Title'),
            'class' => 'required-entry',
            'required' => TRUE,
            'name' => 'banner_title',
        ));


        if (isset($data['banner_name']) && $data['banner_name']) {
            $data['banner_name'] = Mage::getBaseUrl('media') . 'simi/ztheme/banner/' . $data['banner_name'];
        }
        
        if (isset($data['banner_name_tablet']) && $data['banner_name_tablet']) {
            $data['banner_name_tablet'] = Mage::getBaseUrl('media') . 'simi/ztheme/banner_tab/' . $data['banner_name_tablet'];
        }

        $fieldset->addField('banner_name', 'image', array(
            'label' => Mage::helper('ztheme')->__('Image for Phone (width:900px, height:600px)'),
            'required' => FALSE,
            'name' => 'banner_name',
        ));
        
       $fieldset->addField('banner_name_tablet', 'image', array(
            'label' => Mage::helper('ztheme')->__('Image for Tablet (width:1800px, height:1200px)'),
            'required' => FALSE,
            'name' => 'banner_name_tablet',
        ));
//hainh customize
        
        $fieldset->addField('category_id', 'text', array(
            'name' => 'category_id',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('ztheme')->__('Category ID'),
            'note'  => Mage::helper('ztheme')->__('Choose a category'),
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleMainCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Category"></a>
                <div id="main_categories_select" style="display:none"></div>
                    <script type="text/javascript">
                    function toggleMainCategories(check){
                        var cate = $("main_categories_select");
                        if($("main_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            var url = "' . $this->getUrl('adminhtml/ztheme_banner/chooserMainCategories') . '";                        
                            if(check == 1){
                                $("category_id").value = $("category_all_ids").value;
                            }else if(check == 2){
                                $("category_id").value = "";
                            }
                            var params = $("category_id").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("main_categories_select").update(transport.responseText);
                                        $("main_categories_select").style.display = "block"; 
                                    }
                                });
                        if(cate.style.display == "none"){
                            cate.style.display = "";
                        }else{
                            cate.style.display = "none";
                        } 
                    }else{
                        cate.style.display = "none";                    
                    }
                };
				
				function showChildCat() {
					if ($("banner_content").value == "2") {
						$("childcatdiv_simi").style.display = "none";
					}
					else {
						$("childcatdiv_simi").style.display = "";
					}
				}
        </script>
            '
        ));

        $fieldset->addField('banner_position', 'text', array(
          'label'     => Mage::helper('ztheme')->__('Position'),
          'class'     => 'validate-number',
          'name'      => 'banner_position'));
        
		$listChildCat = '';
		$category = Mage::getModel('catalog/category');
		$category->load($data['category_id']);
		foreach ($category->getChildrenCategories() as $childCat) {
			$listzaraChild = Mage::getModel('ztheme/banner')->getCollection()
			->addFieldToFilter('banner_content','2')
			->addFieldToFilter('category_id',$childCat->getId())
			->getFirstItem();
			$existingBanner = ' - <a href="'.$this->getUrl('*/*/new').'">Create Banner</a>';
			if ($listzaraChild->getId())
			{
				$existingBanner = ' - <a href="'.$this->getUrl('*/*/edit', array('banner_id'=>$listzaraChild->getId())).'">Edit Banner '.$listzaraChild->getBannerTitle().'</a>';
			}
			$listChildCat.= '<li>'.strtoupper($childCat->getName()).$existingBanner.'</li>';
		}
        $fieldset->addField('banner_content', 'select', array(
            'name' => 'banner_content',
            'label' => Mage::helper('ztheme')->__('Show on'),
            'title' => Mage::helper('ztheme')->__('Show on'),
            'values' => array(
			array('value'    => '1','label'    => Mage::helper('ztheme')->__('Home Screen')),
			array('value'    => '2','label'    => Mage::helper('ztheme')->__('Sub-Category Screen'))
			),
            'required' => FALSE,
			'onchange' => 'showChildCat()',
			'after_element_html' => '</br></br><div id="childcatdiv_simi"><b>CHILDREN CATEGORIES:</b><br><ul>'.$listChildCat.'</ul></div>
			<script>
			showChildCat();
			</script>
			',
        ));	
        $form->setValues($data);
        return parent::_prepareForm();
		//end
    }

   

}
