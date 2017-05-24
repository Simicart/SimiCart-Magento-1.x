<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getSiminotificationData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSiminotificationData();
            Mage::getSingleton('adminhtml/session')->setSiminotificationData(null);
        } elseif (Mage::registry('siminotification_data'))
            $data = Mage::registry('siminotification_data')->getData();

        $fieldset = $form->addFieldset('siminotification_form', array('legend' => Mage::helper('simiconnector')->__('Notification Content')));
        $fieldset->addType('datetime', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime');

        $stores = Mage::getModel('core/store')->getCollection();

        $list_store = array();
        foreach ($stores as $store) {
            $list_store[] = array(
                'value' => $store->getId(),
                'label' => Mage::getModel('core/store_group')->load($store->getData('group_id'))->getName() . ' - ' . $store->getName(),
            );
        }
        $fieldset->addField('storeview_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Store View'),
            'name' => 'storeview_id',
            'note' => Mage::helper('simiconnector')->__('After changed this setting, you would need to re-select the devices to be sent'),
            'values' => $list_store,
            'onchange' => 'clearDevices()'
        ));
        
        $fieldset->addField('notice_sanbox', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Send To'),
            'name' => 'notice_sanbox',
            'values' => array(			
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Test App')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Live App')),
            ),
            'note' => '',
        ));
        
		$data['device_type'] = $data['device_id'];
        $fieldset->addField('device_type', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Device Type'),
            'name' => 'device_type',
            'values' => array(
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('All')),
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('IOS')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Android')),
            ),
        ));

        $fieldset->addField('show_popup', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Show Popup'),
            'name' => 'show_popup',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            ),
            'note' => 'If you choose Yes, there will be a popup shown on mobile screen when notification comes',
        ));

        $fieldset->addField('notice_title', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'notice_title',
        ));

        $fieldset->addField('image_url', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image'),
            'name' => 'image_url',
            'note' => Mage::helper('simiconnector')->__('Size max: 1000 x 1000 (PX)'),
        ));

        $fieldset->addField('notice_content', 'editor', array(
            'name' => 'notice_content',
            // 'class' => 'required-entry',
            // 'required' => true,
            'label' => Mage::helper('simiconnector')->__('Message'),
            'title' => Mage::helper('simiconnector')->__('Message'),
            'note' => Mage::helper('simiconnector')->__('Characters recommended: < 250'),
        ));

        $fieldset->addField('type', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Direct viewers to'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'type',
            'values' => Mage::getModel('simiconnector/siminotification')->toOptionArray(),
            'onchange' => 'onchangeNoticeType(this.value)',
            'after_element_html' => '<script> Event.observe(window, "load", function(){onchangeNoticeType(\'' . $data['type'] . '\');});</script>',
        ));

        $productIds = implode(", ", Mage::getResourceModel('catalog/product_collection')->getAllIds());
        $fieldset->addField('product_id', 'text', array(
            'name' => 'product_id',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Product ID'),
            'note' => Mage::helper('simiconnector')->__('Choose a product'),
            'after_element_html' => '<a id="product_link" href="javascript:void(0)" onclick="toggleMainProducts()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Products"></a><input type="hidden" value="' . $productIds . '" id="product_all_ids"/><div id="main_products_select" style="display:none;width:640px"></div>
                <script type="text/javascript">
                    function toggleMainProducts(){
                        if($("main_products_select").style.display == "none"){
                            var url = "' . $this->getUrl('adminhtml/simiconnector_siminotification/chooserMainProducts') . '";
                            var params = $("product_id").value.split(", ");
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
                            grid.reloadParams["selected[]"] = $("product_id").value.split(", ");
                        }
                    }
                    function toogleCheckAllProduct(el){
                        if(el.checked == true){
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(!e.checked){
                                        if($("product_id").value == "")
                                            $("product_id").value = e.value;
                                        else
                                            $("product_id").value = $("product_id").value + ", "+e.value;
                                        e.checked = true;
                                        grid.reloadParams["selected[]"] = $("product_id").value.split(", ");
                                    }
                                }
                            });
                        }else{
                            $$("#main_products_select input[type=checkbox][class=checkbox]").each(function(e){
                                if(e.name != "check_all"){
                                    if(e.checked){
                                        var vl = e.value;
                                        if($("product_id").value.search(vl) == 0){
                                            if($("product_id").value == vl) $("product_id").value = "";
                                            $("product_id").value = $("product_id").value.replace(vl+", ","");
                                        }else{
                                            $("product_id").value = $("product_id").value.replace(", "+ vl,"");
                                        }
                                        e.checked = false;
                                        grid.reloadParams["selected[]"] = $("product_id").value.split(", ");
                                    }
                                }
                            });
                            
                        }
                    }
                    function selectProduct(e) {
                        if(e.checked == true){
                            if(e.id == "main_on"){
                                $("product_id").value = $("product_all_ids").value;
                            }else{
                                if($("product_id").value == "")
                                    $("product_id").value = e.value;
                                else
                                    $("product_id").value = e.value;
                                grid.reloadParams["selected[]"] = $("product_id").value;
                            }
                        }else{
                             if(e.id == "main_on"){
                                $("product_id").value = "";
                            }else{
                                var vl = e.value;
                                if($("product_id").value.search(vl) == 0){
                                    $("product_id").value = $("product_id").value.replace(vl+", ","");
                                }else{
                                    $("product_id").value = $("product_id").value.replace(", "+ vl,"");
                                }
                            }
                        }
                        
                    }
                </script>'
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
                            var url = "' . $this->getUrl('adminhtml/simiconnector_siminotification/chooserMainCategories') . '";                        
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

        $fieldset->addField('notice_url', 'text', array(
            'name' => 'notice_url',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('URL'),
        ));

        $fieldset->addField('created_time', 'datetime', array(
            'label' => Mage::helper('simiconnector')->__('Created Date'),
            'bold' => true,
            'name' => 'created_date',
        ));

        $fieldsetFilter = $form->addFieldset('filter_form', array(
            'legend' => Mage::helper('simiconnector')->__('Notification Devices Select')
        ));
        $deviceIds = Mage::getModel('simiconnector/device')->getCollection()->getAllIds();

        $fieldsetFilter->addField('devices_pushed', 'textarea', array(
            'name' => 'devices_pushed',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Device IDs'),
            'note' => Mage::helper('simiconnector')->__('Select your Devices'),
            'after_element_html' => '
                <a id="product_link" href="javascript:void(0)" onclick="toggleMainDevices()"><img src="' . $this->getSkinUrl('images/rule_chooser_trigger.gif') . '" alt="" class="v-middle rule-chooser-trigger" title="Select Device"></a>
                <input type="hidden" value="' . $deviceIds . '" id="device_all_ids"/>
                <div id="main_devices_select" style="display:none"></div>  
                <script type="text/javascript">
                    function clearDevices(){                    
                        $("main_devices_select").style.display == "none";
                        toggleMainDevices(2);
                    }
                    function updateNumberSeleced(){
                        $("note_devices_pushed_number").update($("devices_pushed").value.split(", ").size());
                    }
                    function toggleMainDevices(check){
                        var cate = $("main_devices_select");
                        if($("main_devices_select").style.display == "none" || (check ==1) || (check == 2)){
                            var url = "' . $this->getUrl('adminhtml/simiconnector_siminotification/chooseDevices') . '?storeview_id="+$("storeview_id").value;                        
                            if(check == 1){
                                $("devices_pushed").value = $("devices_all_ids").value;
                            }else if(check == 2){
                                $("devices_pushed").value = "";
                            }
                            var params = $("devices_pushed").value.split(", ");
                            var parameters = {"form_key": FORM_KEY,"selected[]":params };
                            var request = new Ajax.Request(url,
                                {
                                    evalScripts: true,
                                    parameters: parameters,
                                    onComplete:function(transport){
                                        $("main_devices_select").update(transport.responseText);
                                        $("main_devices_select").style.display = "block"; 
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
                    updateNumberSeleced();
                };
                
                var griddevice;
                   
                function constructDataDevice(div){
                    griddevice = window[div.id+"JsObject"];
                    if(!griddevice.reloadParams){
                        griddevice.reloadParams = {};
                        griddevice.reloadParams["selected[]"] = $("devices_pushed").value.split(", ");
                    }
                }
                function toogleCheckAllDevices(el){
                    if(el == true){
                        $$("#main_devices_select input[type=checkbox][class=checkbox]").each(function(e){
                            if(e.name != "check_all"){
                                if(!e.checked){
                                    if($("devices_pushed").value == "")
                                        $("devices_pushed").value = e.value;
                                    else
                                        $("devices_pushed").value = $("devices_pushed").value + ", "+e.value;
                                    e.checked = true;
                                    griddevice.reloadParams["selected[]"] = $("devices_pushed").value.split(", ");
                                }
                            }
                        });
                    }else{
                        $$("#main_devices_select input[type=checkbox][class=checkbox]").each(function(e){
                            if(e.name != "check_all"){
                                if(e.checked){
                                    var vl = e.value;
                                    if($("devices_pushed").value.search(vl) == 0){
                                        if($("devices_pushed").value == vl) $("devices_pushed").value = "";
                                        $("devices_pushed").value = $("devices_pushed").value.replace(vl+", ","");
                                    }else{
                                        $("devices_pushed").value = $("devices_pushed").value.replace(", "+ vl,"");
                                    }
                                    e.checked = false;
                                    griddevice.reloadParams["selected[]"] = $("devices_pushed").value.split(", ");
                                }
                            }
                        });
                    }
                    updateNumberSeleced();
                }
                function selectDevice(e) {
                        if(e.checked == true){
                            if(e.id == "main_on"){
                                $("devices_pushed").value = $("device_all_ids").value;
                            }else{
                                if($("devices_pushed").value == "")
                                    $("devices_pushed").value = e.value;
                                else
                                    $("devices_pushed").value = $("devices_pushed").value + ", "+e.value;
                                e.checked == false;
                                griddevice.reloadParams["selected[]"] = $("devices_pushed").value.split(", ");
                            }
                        }else{
                             if(e.id == "main_on"){
                                $("devices_pushed").value = "";
                            }else{
                                var vl = e.value;
                                if($("devices_pushed").value.search(vl) == 0){
                                    if ($("devices_pushed").value.search(",") == -1)
                                        $("devices_pushed").value = "";
                                    else
                                        $("devices_pushed").value = $("devices_pushed").value.replace(vl+", ","");
                                }else{
                                    $("devices_pushed").value = $("devices_pushed").value.replace(", "+ vl,"");
                                }
                                e.checked == false;
                                griddevice.reloadParams["selected[]"] = $("devices_pushed").value.split(", ");
                            }
                        }
                        updateNumberSeleced();
                    }
            </script>
            '
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
