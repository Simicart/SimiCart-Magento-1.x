<?php

class Simi_Simiconnector_Adminhtml_Simiconnector_SiminotificationController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('simiconnector/siminotification')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('simiconnector/siminotification')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('siminotification_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/siminotification');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Notification Manager'), Mage::helper('adminhtml')->__('Notification Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Notification News'), Mage::helper('adminhtml')->__('Notification News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_siminotification_edit'))
                ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_siminotification_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Notification does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            unset($data['click']);
            unset($data['click_rate']);

            if (isset($_FILES['image_url']['name']) && $_FILES['image_url']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image_url');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //  (file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'notification' . DS . 'images';
                    // $result = $uploader->save($path, $_FILES['image_url']['name'] );
                    $result = $uploader->save($path, md5(time()) . '.png');
                    $imageUrl = 'simi/simiconnector/notification/images/' . md5(time()) . '.png';
                } catch (Exception $e) {
                    $imageUrl = 'simi/simiconnector/notification/images/' . md5(time()) . '.png';
                }
            }

            if(isset($data['time_to_send']) && $data['time_to_send']){

                $data['time_to_send'] = str_replace('/','-',$data['time_to_send']);

                $shedule_time =  strtotime($data['time_to_send']);

                if($shedule_time > now()){
                    // greater now
                    $data['status_send'] = '1'; // pending status

                    $this->activeShedule(now(),$shedule_time);

                }
                else{
                    $data['status_send'] = '0'; // sent
                }
            }



            $data['created_time'] = now();
            $model = Mage::getModel('simiconnector/siminotification');
            $data['device_id'] = $data['device_type'];
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            if (!$imageUrl && is_array($data['image_url'])) {
                if ($data['image_url']['delete'])
                    $data['delete'] = $data['image_url']['delete'];
                $data['image_url'] = $data['image_url']['value'];
                $imageUrl = $data['image_url'];
            }

            if ($data['delete']) {
                $data['image_url'] = null;
                $imageUrl = null;
            }
            if ($imageUrl) {
                $data['image_url'] = $imageUrl;
                $model->setImageUrl($imageUrl);
            }
            try {
                $test_data['before_save'] = $data;
                $model->save();
                $test_data['after_save'] = $model->getData();
                Mage::unregister('siminotification_data');
                //Zend_Debug::dump($test_data);die('saveAction');

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Message was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            } else {
                if ($data['image_url'])
                    $data['image_url'] = Mage::getBaseUrl('media') . $data['image_url'];

                $data['notice_type'] = 0;
                $data['notice_id'] = $model->getId();
                $list = @getimagesize($data['image_url']);
                $data['width'] = $list[0];
                $data['height'] = $list[1];
                $resultSend = Mage::helper('simiconnector/siminotification')->sendNotice($data);
            }
            if (!$resultSend) {
                $this->_redirect('*/*/');
                return;
            }
            $this->_redirect('*/*/');
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Unable to find item to send'));
        $this->_redirect('*/*/');
    }


    public function activeShedule($timecreated,$timescheduled)
    {

        $jobCode = 'simiconnector_notification';
        try {
            $schedule = Mage::getModel('cron/schedule');
            $schedule->setJobCode($jobCode)
                ->setCreatedAt($timecreated)
                ->setScheduledAt($timescheduled)
                ->setStatus(Mage_Cron_Model_Schedule::STATUS_PENDING)
                ->save();

            echo json_encode($schedule->getData());

        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save Cron expression'));
        }

    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/siminotification');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $messageIds = $this->getRequest()->getParam('simiconnector');

        if (!is_array($messageIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($messageIds as $messageId) {
                    $notice = Mage::getModel('simiconnector/siminotification')->load($messageId);
                    $notice->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($bannerIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('simiconnector');
    }


    public function guideAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Google Application Guide'));
        $this->renderLayout();
    }

    public function chooserMainCategoriesAction() {
        $request = $this->getRequest();
        $id = $request->getParam('selected', array());
        $block = $this->getLayout()->createBlock('simiconnector/adminhtml_siminotification_edit_tab_categories', 'maincontent_category', array('js_form_object' => $request->getParam('form')))
            ->setCategoryIds($id)
        ;

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction() {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Initialize category object in registry
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory() {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $storeId = (int) $this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current' => true, 'id' => null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }

    public function categoriesJson2Action() {
        $this->_initItem();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('simiconnector/adminhtml_siminotification_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function chooserMainProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
            'simiconnector/adminhtml_siminotification_edit_tab_products', 'promo_widget_chooser_sku', array('js_form_object' => $request->getParam('form'),
        ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function filterStateCityAction(){
        $is_state = $this->getRequest()->getParam('is_state');
        $country_code = $this->getRequest()->getParam('country_code');
        $city_code = $this->getRequest()->getParam('city_code');
        if($is_state == 1) {
            if($country_code) {
                $states = Mage::helper('simiconnector/siminotification')->getListState($country_code);
                if (count($states) > 0) {
                    $states_response = "<option value=''> </option>";
                    foreach ($states as $key => $state) {
                        $states_response .= "<option value='" . $key . '_' . $state . "'>" . $state . " </option>";
                    }
                    echo $states_response;
                }
            }
            echo '';
        }
        else {
            $array = explode('_',$city_code);
            if(count($array)){
                $city_code = $array[0];
            }
            $counties =Mage::getModel('romcity/romcity')->getCollection()->addFieldToFilter('country_id', $country_code)
                ->addFieldToFilter('region_id', $city_code);
            if(count($counties) > 0){
                $counties_response = "<option value=''></option>";
                foreach ($counties as $county){
                    $full_city_name = $county->getData('cityname');
                    $city_name = trim( str_replace('Quận','',$full_city_name));
                    $counties_response  .= "<option value='".$city_name."'>".$full_city_name." </option>";
                }
                echo $counties_response;
            }
            echo '';
        }
    }


    /*
     * Get Device to Push Notification
     */

    public function chooseDevicesAction() {
        $request = $this->getRequest();
        echo '<p class="note"><span id="note_devices_pushed_number"> </span> <span> '.Mage::helper('simiconnector')->__('Device(s) Selected').'</span></p>';

        $block = $this->getLayout()->createBlock(
            'simiconnector/adminhtml_siminotification_edit_tab_devices', 'promo_widget_chooser_device_id', array('js_form_object' => $request->getParam('form'),
        ));



        if ($block) {
            $block->storeview_id = $this->getRequest()->getParam('storeview_id');

            $script = '<script>$j = jQuery.noConflict();
 $j(\'select[name=country]\').change(function () {
        console.log(\'You selected country\' + $j(\'select[name=country]\').val());
        change_country($j(\'select[name=country]\').val());
    });

    function change_country(code) {
        var url = $j(\'#span_hidden_simi\').text();

        $j.ajax(
            {
                url: url,
                method: \'GET\',
                data: {country_code: code, is_state: 1},
                success: function ($result) {
                    $j(\'select[name=city]\').children().remove();
                    $j(\'select[name=city]\').append($result);
                }

            }
        );
    }

    $j(\'select[name=city]\').change(function () {
        change_city($j(\'select[name=city]\').val());
    });

    function change_city(cityCode) {
        var url = $j(\'#span_hidden_simi\').text();
        var countryCode = $j(\'select[name=country]\').val() ;
        $j.ajax(
            {
                url: url,
                data: {is_state: 0, country_code: countryCode, city_code: cityCode},
                success: function (result) {
                    $j(\'select[name=state]\').children().remove();
                    $j(\'select[name=state]\').append(result);
                }
            }
        );
    }
</script>';

            $this->getResponse()->setBody($block->toHtml().$script);
        }
    }

}
