<?php

/**
 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{


    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getSiminotificationData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSiminotificationData();
            Mage::getSingleton('adminhtml/session')->setSiminotificationData(null);
        } elseif (Mage::registry('siminotification_data'))
            $data = Mage::registry('siminotification_data')->getData();

        if(isset($data['time_to_send']) && $data['time_to_send'] && $data['time_to_send'] == '0000-00-00 00:00:00'){
            $data['time_to_send'] = '';
        }

        //Zend_Debug::dump($data);die('Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Form');

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
            'onchange' => 'changeDeviceType(this.value)'
        ));

        $fieldset->addField('show_popup', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Show Popup'),
            'name' => 'show_popup',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            ),
            'onchange' => 'changeShowPopup(this.value)',
            'note' => 'If you choose Yes, there will be a popup shown on mobile screen when notification comes',
        ));

        $fieldset->addField('notice_title', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'notice_title',
            'onchange' => 'changeNoticeTitle(this.value)'
        ));

        $image_url_uploaded = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $data['image_url'];
        $fieldset->addField('image_url', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image'),
            'name' => 'image_url',
            'note' => Mage::helper('simiconnector')->__('Size max: 1000 x 1000 (PX)'),
            'onchange' => 'changeImage(this)',
            'after_element_html' => '<script> var image_url_uploaded = \'' . $image_url_uploaded . '\';</script>'
        ));

        $fieldset->addField('notice_content', 'editor', array(
            'name' => 'notice_content',
            // 'class' => 'required-entry',
            // 'required' => true,
            'label' => Mage::helper('simiconnector')->__('Message'),
            'title' => Mage::helper('simiconnector')->__('Message'),
            'note' => Mage::helper('simiconnector')->__('Characters recommended: < 250'),
            'onchange' => 'changeMessage(this.value)'
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
        $app_icon = Mage::getBaseUrl('media'). DS . 'simi' . DS . 'simiconnector' . DS . 'applogo'.DS. Mage::getStoreConfig("simiconnector/notification/app_logo",Mage::app()->getStore()->getId());

        $app_name = Mage::getStoreConfig("simiconnector/notification/app_name",Mage::app()->getStore()->getId());

        $fieldset->addField('preview_notification', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Show preview'),
            'name' => 'click',
            'onchange' => 'previewNoti(this.value)',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            ),
            'after_element_html' => '<script> var app_icon = \'' . $app_icon . '\';</script>' .
                '<div id="div_preview_notification" style="display: none">

    <table id="table_preview" border="0px" cellpadding="10px" cellspacing="10px">
        <tr>
            <th>

            </th>
            <th class="android" style="text-align: center;font-weight: bold">
                ANDROID
            </th>
            <th class="ios" style="text-align: center;font-weight: bold">
                IOS
            </th>
        </tr>
        <tr>
            <td style="text-align: center;vertical-align: middle;font-weight: bold">TOP</td>
            
            <td class="content_preview">
               <div id="top_andorid_preview" class="top_preview android"
     style=" border-radius: 8px;background:whitesmoke;width: 400px; padding:10px 10px 45px 10px;">

    <img class="img_icon" src="'.$app_icon.'" style="border-radius: 4px;width: 10%;height: 40px;float:left;">

    <span id="title_android_top" style="float: left; font-family: sans-serif;white-space: nowrap ;overflow: hidden;text-overflow: ellipsis; width:70%;font-size: 16px; margin: 0 2px 0 5px;">

</span>
    <span id="time_android_top" style="font-family: sans-serif;font-size: 12px;color:#5b5b5b; padding: 0 10px 0 0;margin: 0px; width:10%;float: right">
9:42PM
</span>

    <div class="clearfix" style="height: 3px;"></div>
    <span id="message_android_top" style="font-family: sans-serif;font-size: 14px;float:left;color:#5b5b5b; white-space: nowrap ;overflow: hidden;text-overflow: ellipsis;margin:5px 0 0 5px; width:80%;">
</span>

</div>
            </td>
            
            <td class="content_preview" style="padding-left: 20px;">
                <div id="top_ios_preview" class="top_preview ios"
     style="font-family: sans-serif;background:whitesmoke; border-radius: 8px;width: 400px;padding: 10px;">

    <img class="img_icon" src="'.$app_icon.'" width="24px" height="24px" style=" border-radius: 4px;">

    <span id="company_name_ios_top" style="font-family: sans-serif; font-size: 14px;">'.$app_name.'</span>
    <span id="time_ios_top" style="font-family: sans-serif;margin: 10px 0 0 0; font-size: 12px; float: right;">Today,9:42PM</span>

    <p id="title_ios_top" style="font-family: sans-serif;white-space: nowrap ;overflow: hidden;text-overflow: ellipsis;margin: 5px 0 0 0; font-size: 14px; font-weight: bold;"></p>
    <p id="message_ios_top" style="font-family: sans-serif;margin: 3px 0 0 0;"></p>
</div>
            </td>
            
        </tr>
        <tr>
            <td style="text-align: center;vertical-align: middle;font-weight: bold">POPUP</td>
            <td class="content_preview">

               <div id="popup_android_preview" class="popup_preview android"
     style="background: whitesmoke;width:400px; padding: 10px;border-radius: 8px;"
>

    <p id="title_android_popup"
       style="white-space: nowrap ;overflow: hidden;text-overflow: ellipsis;font-family: sans-serif;text-align: center;width: 80%;font-size: 16px;font-weight: bolder;  margin:  auto auto;">
        
    </p>


    <img class="img_popup" id="img_popup" style="padding: 10px; display: block; margin:  auto auto;" width="60%" height="auto" src="'.$app_icon.'">

    <p id="message_android_popup"
       style="font-family: sans-serif;display: block; margin:  auto auto;text-align: center;width: 80%;font-size: 16px; ">
        
    </p>

    <div style="height: 15px"></div>
    <span style="font-family: sans-serif;width:50% ;text-align: center;font-weight:bold;float: left;  ">
            CLOSE
        </span>
    <span style="font-family: sans-serif;width:50% ;text-align: center;font-weight: bold;float: left;">
            SHOW
        </span>

    <div class="clearfix"></div>
    <div style="height: 15px"></div>

</div>


            </td>
            <td class="content_preview" style="padding-left: 20px;">
            <div id="popup_ios_preview" class="popup_preview ios" style="background: whitesmoke;border-radius: 8px;width:400px;padding: 10px 10px 40px 10px;">

    <p id="title_ios_popup" style="font-family: sans-serif;text-align: center;width: 80%;font-size: 16px; margin:  auto auto;">

    </p>

    <img class="img_popup" id="img_popup" style="font-family: sans-serif;padding: 10px; display: block; margin:  auto auto;" width="60%" height="auto" src="'.$app_icon.'">

    <p id="message_ios_popup" style="font-family: sans-serif;display: block; margin:  auto auto;text-align: center;width: 80%;font-size: 16px;">

    </p>

    <div style="margin: 10px 0 0 0; height: 1px; background-color:#cacaca"></div>

    <span style="font-family: sans-serif; padding: 10px 5px 10px 5px; font-size: 14px; width:45% ;text-align: center;font-weight:bold;  float: left;color: #2e8ab8">
        Close
    </span>
    <span style="font-family: sans-serif; padding: 10px 5px 10px 5px; font-size: 14px;width:45% ;text-align: center;font-weight:bold;  float: right;color: #2e8ab8">
        Show
    </span>

    <div class="clearfix"></div>



</div>
            </td>
        </tr>


    </table>

</div>'
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('time_to_send', 'date', array(
            'label' => Mage::helper('simiconnector')->__('Time to send'),
            'bold' => true,
            'name' => 'time_to_send',
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => $dateFormatIso,
            'format'       => $dateFormatIso,
            'time' => true,
            'note' => 'Sever time : '. date("m/d/Y h:i:s a", Mage::getModel('core/date')->timestamp(time())),
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
