<?php

class Simi_Simiconnector_Block_Adminhtml_Simiproductlabel_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getConnectorData()) {
            $data = Mage::getSingleton('adminhtml/session')->getConnectorData();
            Mage::getSingleton('adminhtml/session')->setConnectorData(null);
        } elseif (Mage::registry('simiproductlabel_data'))
            $data = Mage::registry('simiproductlabel_data')->getData();

        $fieldset = $form->addFieldset('simiproductlabel_form', array('legend' => Mage::helper('simiconnector')->__('Label information')));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Status'),
            'name' => 'status',
            'values' => Mage::getSingleton('simiconnector/status')->getOptionHash(),
        ));

        if ($data['status'] == null) {
            $data['status'] = 1;
        }

        $stores = Mage::getModel('core/store')->getCollection();
        $list_store = array();
        foreach ($stores as $store) {
            $list_store[] = array(
                'value' => $store->getId(),
                'label' => $store->getName(),
            );
        }
        $fieldset->addField('storeview_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Store View'),
            'name' => 'storeview_id',
            'values' => $list_store
        ));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Label Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('simiconnector')->__('Description'),
            'title' => Mage::helper('simiconnector')->__('Description'),
            'style' => 'height: 100px;',
            'wysiwyg' => false,
            'required' => false,
        ));

        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image (width:340px, height:340px)'),
            'required' => FALSE,
            'name' => 'image_name_co',
        ));


        $fieldset->addField('position', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Position'),
            'name' => 'position',
            'required' => false,
            'values' => Mage::helper('simiconnector/productlabel')->getOptionHash()
        ));

        $fieldset->addField('priority', 'text', array(
            'name' => 'priority',
            'class' => 'validate-number',
            'label' => Mage::helper('simiconnector')->__('Priority'),
            'note' => Mage::helper('simiconnector')->__('The higher the value, the higher the priority.'),
        ));


        $fieldset->addField('text', 'textarea', array(
            'label' => Mage::helper('simiconnector')->__('Text'),
            'required' => false,
            'name' => 'text',
        ));

        if ($data['is_auto_fill'] == null) {
            $data['is_auto_fill'] = 1;
        }


        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('product_ids', 'text', array(
            'name' => 'product_ids',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Product ID'),
            'note' => Mage::helper('simiconnector')->__('Choose a product'),
            'after_element_html' => '<a id="product_link" href="javascript:void(0)" onclick="toggleMainProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="' . $productIds . '" id="product_all_ids"/><div id="main_products_select" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts(){
                        if($("main_products_select").style.display == "none"){
                            var url = "' . $this->getUrl('adminhtml/simiconnector_simiproductlabel/chooserMainProducts', array('_secure' => true)) . '";
                            var params = $("product_ids").value.split(", ");
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
                            grid.reloadParams["selected[]"] = $("product_ids").value.split(", ");
                        }
                    }
                    function toogleCheckAllProduct(el){
                        if(el.checked == true){
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(!e.checked){
                                        if($("product_ids").value == "")
                                            $("product_ids").value = e.value;
                                        else
                                            $("product_ids").value = $("product_ids").value + ", "+e.value;
                                        e.checked = true;
                                        grid.reloadParams["selected[]"] = $("product_ids").value.split(", ");
                                    }
                                }
                            });
                        }else{
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(e.checked){
                                        var vl = e.value;
                                        if($("product_ids").value.search(vl) == 0){
                                            if($("product_ids").value == vl) $("product_ids").value = "";
                                            $("product_ids").value = $("product_ids").value.replace(vl+", ","");
                                        }else{
                                            $("product_ids").value = $("product_ids").value.replace(", "+ vl,"");
                                        }
                                        e.checked = false;
                                        grid.reloadParams["selected[]"] = $("product_ids").value.split(", ");
                                    }
                                }
                            });
                            
                        }
                    }
                    function selectProduct(e) {
                        if(e.checked == true){
                            if(e.id == "main_on"){
                                $("product_ids").value = $("product_all_ids").value;
                            }else{
                                if($("product_ids").value == "")
                                    $("product_ids").value = e.value;
                                else
                                    $("product_ids").value = $("product_ids").value + ", "+e.value;
                                    
                                grid.reloadParams["selected[]"] = $("product_ids").value;
                            }
                        }else{
                             if(e.id == "main_on"){
                                $("product_ids").value = "";
                            }else{
                                var vl = e.value;
                                if($("product_ids").value.search(vl) == 0){
                                    $("product_ids").value = $("product_ids").value.replace(vl+", ","");
                                }else{
                                    $("product_ids").value = $("product_ids").value.replace(", "+ vl,"");
                                }
                            }
                        }
                        
                    }
                </script>'
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
