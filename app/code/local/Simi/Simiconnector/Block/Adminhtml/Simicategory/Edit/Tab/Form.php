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
        } else {
            $storeIdArray = array();
            foreach (Mage::getModel('core/store')->getCollection() as $storeModel)
                $storeIdArray[] = $storeModel->getId();
            $data['storeview_id'] = implode(',', $storeIdArray);
        }

        $fieldset = $form->addFieldset('simicategory_form', array('legend' => Mage::helper('simiconnector')->__('Item information')));

        $field = $fieldset->addField('storeview_id', 'multiselect', array(
            'name' => 'storeview_id[]',
            'label' => Mage::helper('cms')->__('Store View'),
            'title' => Mage::helper('cms')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('simicategory_filename', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image'),
            'required' => true,
            'name' => 'simicategory_filename',
        ));

        $fieldset->addField('simicategory_filename_tablet', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Tablet Image'),
            'required' => true,
            'name' => 'simicategory_filename_tablet',
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

        if (!isset($data['sort_order']))
            $data['sort_order'] = 1;
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Sort Order'),
            'required' => false,
            'class' => 'validate-not-negative-number',
            'name' => 'sort_order',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('simiconnector/status')->getOptionHash(),
        ));

        if(Mage::helper('simiconnector/cloud')->getThemeLayout() == 'matrix'){
            $matrixfieldset = $form->addFieldset('simicategory_matrix', array('legend' => Mage::helper('simiconnector')->__('Matrix Layout Config')));

            if (!$data['matrix_width_percent'])
                $data['matrix_width_percent'] = 100;
            if (!$data['matrix_height_percent'])
                $data['matrix_height_percent'] = 30;
            if (!$data['matrix_width_percent_tablet'])
                $data['matrix_width_percent_tablet'] = 100;
            if (!$data['matrix_height_percent_tablet'])
                $data['matrix_height_percent_tablet'] = 30;
            if (!$data['matrix_row'])
                $data['matrix_row'] = 1;

            $matrixfieldset->addField('matrix_width_percent', 'text', array(
                'label' => Mage::helper('simiconnector')->__('Image Width/Screen Width Ratio'),
                'required' => false,
				'class' => 'required-entry validate-number',
                'name' => 'matrix_width_percent',
                'note' => Mage::helper('simiconnector')->__('With Screen Width is 100%'),
            ));

            $matrixfieldset->addField('matrix_height_percent', 'text', array(
                'label' => Mage::helper('simiconnector')->__('Image Height/Screen Width Ratio'),
                'required' => false,
                'name' => 'matrix_height_percent',
				'class' => 'required-entry validate-number',
                'note' => Mage::helper('simiconnector')->__('With Screen Width is 100%'),
            ));

            $matrixfieldset->addField('matrix_width_percent_tablet', 'text', array(
                'label' => Mage::helper('simiconnector')->__('Tablet Image Width/Screen Width Ratio'),
                'required' => false,
                'name' => 'matrix_width_percent_tablet',
				'class' => 'required-entry validate-number',
                'note' => Mage::helper('simiconnector')->__('Leave it empty if you want to use Phone Value'),
            ));

            $matrixfieldset->addField('matrix_height_percent_tablet', 'text', array(
                'label' => Mage::helper('simiconnector')->__('Tablet Image Height/Screen Width Ratio'),
                'required' => false,
                'name' => 'matrix_height_percent_tablet',
				'class' => 'required-entry validate-number',
                'note' => Mage::helper('simiconnector')->__('Leave it empty if you want to use Phone Value'),
            ));

            $matrixfieldset->addField('matrix_row', 'select', array(
                'label' => Mage::helper('simiconnector')->__('Row Number'),
                'values' => Mage::helper('simiconnector/productlist')->getMatrixRowOptions(),
                'onchange' => 'autoFillHeight(this.value)',
                'name' => 'matrix_row',
            ));


            foreach (Mage::getModel('core/store')->getCollection() as $storeView) {
                if (!$data['storeview_scope'])
                    $data['storeview_scope'] = $storeView->getId();
                $storeviewArray[$storeView->getId()] = $storeView->getName();
            }

            $matrixfieldset->addField('storeview_scope', 'select', array(
                'label' => Mage::helper('simiconnector')->__('Storeview for Mockup Preview'),
                'name' => 'storeview_scope',
                'values' => $storeviewArray,
                'onchange' => 'updateMockupPreview(this.value)',
                'after_element_html' => '<div id="mockuppreview"></div> <script>
            ' . Mage::helper('simiconnector/productlist')->autoFillMatrixRowHeight() . '
            function updateMockupPreview(storeview){
                var urlsend = "' . Mage::helper("adminhtml")->getUrl("*/simiconnector_productlist/getMockup") . '?storeview_id=" + storeview;
                xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                  if (xhttp.readyState == 4 && xhttp.status == 200) {
                    document.getElementById("mockuppreview").innerHTML = xhttp.responseText;
                  }
                };
                xhttp.open("GET", urlsend, true);
                xhttp.send();
            }
            Event.observe(window, "load", function(){updateMockupPreview(\'' . $data['storeview_scope'] . '\');});</script>',
            ));

        }
        $form->setValues($data);
        return parent::_prepareForm();
    }

}
