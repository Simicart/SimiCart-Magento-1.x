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
 * Simi Edit Form Content Tab Block
 * 
 * @category 	
 * @package 	Madapter
 * @author  	Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Productlist_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getConnectorData()) {
            $data = Mage::getSingleton('adminhtml/session')->getConnectorData();
            Mage::getSingleton('adminhtml/session')->setConnectorData(null);
        } elseif (Mage::registry('productlist_data'))
            $data = Mage::registry('productlist_data')->getData();

        if ($data['productlist_id']) {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
            $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                    ->addFieldToFilter('content_type', $typeID)
                    ->addFieldToFilter('item_id', $data['productlist_id']);
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
        $fieldset = $form->addFieldset('simiconnector_form', array('legend' => Mage::helper('simiconnector')->__('Product List information')));


        $field = $fieldset->addField('storeview_id', 'multiselect', array(
            'name' => 'storeview_id[]',
            'label' => Mage::helper('simiconnector')->__('Store View'),
            'title' => Mage::helper('simiconnector')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
        ));

        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('list_title', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'list_title',
        ));
        if(Mage::helper('simiconnector/cloud')->getThemeLayout() == 'matrix'
            || Mage::helper('simiconnector/cloud')->getThemeLayout() == 'zara'){
            $fieldset->addField('list_image', 'image', array(
                'label' => Mage::helper('simiconnector')->__('Product List Image'),
                'name' => 'productlist_image_o',
            ));

            $fieldset->addField('list_image_tablet', 'image', array(
                'label' => Mage::helper('simiconnector')->__('Product List Tablet Image'),
                'name' => 'productlist_image_tablet_o',
            ));
        }

        if (!isset($data['sort_order']))
            $data['sort_order'] = 1;
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Sort Order'),
            'required' => false,
            'class' => 'validate-not-negative-number',
            'name' => 'sort_order',
        ));

        if (!$data['list_type'])
            $data['list_type'] = 1;
        $fieldset->addField('list_type', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Product List Type'),
            'name' => 'list_type',
            'values' => Mage::helper('simiconnector/productlist')->getTypeOption(),
            'onchange' => 'onchangeNoticeType(this.value)',
            'after_element_html' => '<script> Event.observe(window, "load", function(){onchangeNoticeType(\'' . $data['list_type'] . '\');});</script>',
        ));

        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('list_products', 'text', array(
            'name' => 'list_products',
            'label' => Mage::helper('simiconnector')->__('Product ID'),
            'note' => Mage::helper('simiconnector')->__('Choose a product'),
            'after_element_html' => '<a id="product_link" href="javascript:void(0)" onclick="toggleMainProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="' . $productIds . '" id="product_all_ids"/><div id="main_products_select" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts(){
                        if($("main_products_select").style.display == "none"){
                            var url = "' . $this->getUrl('adminhtml/simiconnector_productlist/chooserMainProducts') . '";
                            var params = $("list_products").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                            {
                                evalScripts: true,
                                parameters: parameters,
                                onComplete:function(transport){
                                    $("main_products_select").update(transport.responseText);
                                    $("main_products_select").style.display = "block"; 
                                }
                            });
                        }else{
                            $("main_products_select").style.display = "none";
                        }
                    };
                    var grid;
                   
                    function constructData(div){
                        grid = window[div.id+"JsObject"];
                        if(!grid.reloadParams){
                            grid.reloadParams = {};
                            grid.reloadParams["selected[]"] = $("list_products").value.split(", ");
                        }
                    }
                    function toogleCheckAllProduct(el){
                        if(el == true){
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(!e.checked){
                                        if($("list_products").value == "")
                                            $("list_products").value = e.value;
                                        else
                                            $("list_products").value = $("list_products").value + ", "+e.value;
                                        e.checked = true;
                                        grid.reloadParams["selected[]"] = $("list_products").value.split(", ");
                                    }
                                }
                            });
                        }else{
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(e.checked){
                                        var vl = e.value;
                                        if($("list_products").value.search(vl) == 0){
                                            if($("list_products").value == vl) $("list_products").value = "";
                                            $("list_products").value = $("list_products").value.replace(vl+", ","");
                                        }else{
                                            $("list_products").value = $("list_products").value.replace(", "+ vl,"");
                                        }
                                        e.checked = false;
                                        grid.reloadParams["selected[]"] = $("list_products").value.split(", ");
                                    }
                                }
                            });
                            
                        }
                    }
                    
                    function selectProduct(e) {
                        if(e.checked == true){
                            if(e.id == "main_on"){
                                $("list_products").value = $("product_all_ids").value;
                            }else{
                                if($("list_products").value == "")
                                    $("list_products").value = e.value;
                                else
                                    $("list_products").value = $("list_products").value + ", "+e.value;
                                    
                                grid.reloadParams["selected[]"] = $("list_products").value;
                            }
                        }else{
                             if(e.id == "main_on"){
                                $("list_products").value = "";
                            }else{
                                var vl = e.value;
                                if($("list_products").value.search(vl) == 0){
                                    $("list_products").value = $("list_products").value.replace(vl+", ","");
                                }else{
                                    $("list_products").value = $("list_products").value.replace(", "+ vl,"");
                                }
                            }
                        }
                        
                    }
                </script>'
        ));


        $fieldset->addField('list_status', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Enable'),
            'name' => 'list_status',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            )
        ));
        if(Mage::helper('simiconnector/cloud')->getThemeLayout() == 'matrix'){
            $matrixfieldset = $form->addFieldset('productlist_matrix', array('legend' => Mage::helper('simiconnector')->__('Matrix Layout Config')));

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
                'label' => Mage::helper('simiconnector')->__('Image Width/ Screen Width Ratio'),
                'required' => false,
                'name' => 'matrix_width_percent',
				'class' => 'required-entry validate-number',
                'note' => Mage::helper('simiconnector')->__('With Screen Width is 100%'),
            ));

            $matrixfieldset->addField('matrix_height_percent', 'text', array(
                'label' => Mage::helper('simiconnector')->__('Image Height/ Screen Width Ratio'),
                'required' => false,
                'name' => 'matrix_height_percent',
				'class' => 'required-entry validate-number',
                'note' => Mage::helper('simiconnector')->__('With Screen Height is 100%'),
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
                var urlsend = "' . Mage::helper("adminhtml")->getUrl("*/*/getMockup") . '?storeview_id=" + storeview;
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
