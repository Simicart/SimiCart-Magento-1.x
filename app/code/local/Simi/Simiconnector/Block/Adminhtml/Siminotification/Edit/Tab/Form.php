<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $websites = Mage::helper('simiconnector')->getWebsites();

        $list_web = array();
        foreach ($websites as $website) {
            $list_web[] = array(
                'value' => $website->getId(),
                'label' => $website->getName(),
            );
        }

        if (Mage::getSingleton('adminhtml/session')->getSiminotificationData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSiminotificationData();
            Mage::getSingleton('adminhtml/session')->setSiminotificationData(null);
        } elseif (Mage::registry('siminotification_data'))
            $data = Mage::registry('siminotification_data')->getData();

        $fieldset = $form->addFieldset('siminotification_form', array('legend' => Mage::helper('simiconnector')->__('Notification Content')));
        $fieldset->addType('datetime', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime');
        $fieldset->addField('website_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Website'),
            'name' => 'website_id',
            'values' => $list_web,
        ));

        $fieldset->addField('notice_sanbox', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Send To'),
            'name' => 'notice_sanbox',
            'values' => array(
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('Both Live App and Test App')),
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Test App')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Live App')),
            ),
            'note' => '',
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
            'legend' => Mage::helper('simiconnector')->__('Notification Device & Location')
        ));

        $fieldsetFilter->addField('device_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Device Type'),
            'name' => 'device_id',
            'values' => array(
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('All')),
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('IOS')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Android')),
            ),
        ));



        $fieldsetFilter->addField('address', 'text', array(
            'name' => 'address',
            'label' => Mage::helper('simiconnector')->__('Address'),
        ));

        $fieldsetFilter->addField('country', 'select', array(
            'name' => 'country',
            'label' => Mage::helper('simiconnector')->__('Country'),
            'values' => Mage::helper('simiconnector/siminotification')->getOptionCountry(),
        ));

        $fieldsetFilter->addField('state', 'text', array(
            'name' => 'state',
            'label' => Mage::helper('simiconnector')->__('State/Province'),
        ));

        $fieldsetFilter->addField('city', 'text', array(
            'name' => 'city',
            'label' => Mage::helper('simiconnector')->__('City'),
        ));

        $fieldsetFilter->addField('zipcode', 'text', array(
            'name' => 'zipcode',
            'label' => Mage::helper('simiconnector')->__('Zip Code'),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
