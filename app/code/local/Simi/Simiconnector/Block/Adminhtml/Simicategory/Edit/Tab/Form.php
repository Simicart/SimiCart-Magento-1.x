<?php

class Simi_Simiconnector_Block_Adminhtml_Simicategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getSimicategoryData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSimicategoryData();
            Mage::getSingleton('adminhtml/session')->setSimicategoryData(null);
        } elseif (Mage::registry('simicategory_data'))
            $data = Mage::registry('simicategory_data')->getData();
        if ($data['simicategory_id']) {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
            $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                    ->addFieldToFilter('content_type', $typeID)
                    ->addFieldToFilter('item_id', $data['simicategory_id']);
            $storeIdArray = array();
            foreach ($visibleStoreViews as $visibilityItem) {
                $storeIdArray[] = $visibilityItem->getData('store_view_id');
            }
            $data['storeview_id'] = implode(',', $storeIdArray);
        }

        $fieldset = $form->addFieldset('simicategory_form', array('legend' => Mage::helper('simiconnector')->__('Item information')));

        $field = $fieldset->addField('storeview_id', 'multiselect', array(
            'name' => 'storeview_id[]',
            'label' => Mage::helper('cms')->__('Store View'),
            'title' => Mage::helper('cms')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('simicategory_filename', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image (width:220px, height:220px)'),
            'required' => true,
            'name' => 'simicategory_filename',
        ));

        $fieldset->addField('category_id', 'text', array(
            'name' => 'category_id',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Category ID'),
            'note' => Mage::helper('simiconnector')->__('Choose a category'),
            'after_element_html' => '<a id="category_link" href="javascript:void(0)" onclick="toggleMainCategories()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Category"></a>
                <div id="main_categories_select" style="display:none"></div>
                    <script type="text/javascript">
                    function toggleMainCategories(check){
                        var cate = $("main_categories_select");
                        if($("main_categories_select").style.display == "none" || (check ==1) || (check == 2)){
                            var url = "' . $this->getUrl('adminhtml/simiconnector_banner/chooserMainCategories') . '";                        
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
        </script>
            '
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Sort Order'),
            'required' => false,
            'name' => 'sort_order',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('simiconnector/status')->getOptionHash(),
        ));


        $form->setValues($data);
        return parent::_prepareForm();
    }

}
