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
            // Zend_debug::dump($_FILES['image_url']['name']);die();
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
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'notification' . DS . 'images';
                    // $result = $uploader->save($path, $_FILES['image_url']['name'] );
                    $result = $uploader->save($path, md5(time()) . '.png');
                    $imageUrl = 'simi/simicart/notification/images/' . md5(time()) . '.png';
                } catch (Exception $e) {
                    $imageUrl = 'simi/simicart/notification/images/' . md5(time()) . '.png';
                }
            }
            // Zend_debug::dump($data);die();

            $data['created_time'] = now();
            $model = Mage::getModel('simiconnector/siminotification');
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
                $model->save();
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
                $list = getimagesize($data['image_url']);
                $data['width'] = $list[0];
                $data['height'] = $list[1];
                $resultSend = $this->sendNotice($data);
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

    public function sendNotice($data) {
        $trans = $this->send($data);
        // update notification history
        $history = Mage::getModel('simiconnector/history');
        if (!$trans)
            $data['status'] = 0;
        else
            $data['status'] = 1;
        $collectionDevice = $data['collection_device'];
        foreach ($collectionDevice as $item) {
            if (($data['website_id'] == null) || (($item->getWebsiteId()) && ($data['website_id'] == $item->getWebsiteId())))
                $data['devices_pushed'].= $item->getId() . ',';
        }
        $history->setData($data);
        $history->save();
        return $trans;
    }

    public function send(&$data) {
        if ($data['category_id']) {
            $categoryId = $data['category_id'];
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $categoryChildrenCount = $category->getChildrenCount();
            $categoryName = $category->getName();
            $data['category_name'] = $categoryName;
            if ($categoryChildrenCount > 0)
                $categoryChildrenCount = 1;
            else
                $categoryChildrenCount = 0;
            $data['has_child'] = $categoryChildrenCount;
            if (!$data['has_child']) {
                $data['has_child'] = '';
            }
        }
        if ($data['product_id']) {
            $productId = $data['product_id'];
            $productName = Mage::getModel('catalog/product')->load($productId)->getName();
            $data['product_name'] = $productName;
        }
        $website = $data['website_id'];
        $collectionDevice = Mage::getModel('simiconnector/device')->getCollection();
        $collectionDevice2 = Mage::getModel('simiconnector/device')->getCollection();

        if ($data['country'] != "0") {
            $country_id = trim($data['country']);
            $collectionDevice->addFieldToFilter('country', array('like' => '%' . $data['country'] . '%'));
            $collectionDevice2->addFieldToFilter('country', array('like' => '%' . $data['country'] . '%'));
        }
        if (isset($data['state']) && ($data['state'] != null)) {
            $city = trim($city);
            $collectionDevice->addFieldToFilter('state', array('like' => '%' . $data['state'] . '%'));
            $collectionDevice2->addFieldToFilter('state', array('like' => '%' . $data['state'] . '%'));
        }
        if (isset($data['address']) && ($data['address'] != null)) {
            $collectionDevice->addFieldToFilter('address', $data['address']);
            $collectionDevice2->addFieldToFilter('address', $data['address']);
        }
        if (isset($data['city']) && ($data['city'] != null)) {
            $collectionDevice->addFieldToFilter('city', array('like' => '%' . $data['city'] . '%'));
            $collectionDevice2->addFieldToFilter('city', array('like' => '%' . $data['city'] . '%'));
        }
        if (isset($data['zipcode']) && ($data['zipcode'] != null)) {
            $collectionDevice->addFieldToFilter('zipcode', array('like' => '%' . $data['zipcode'] . '%'));
            $collectionDevice2->addFieldToFilter('zipcode', array('like' => '%' . $data['zipcode'] . '%'));
        }
        switch ($data['notice_sanbox']) {
            case '1': $sendLive = 0;
                $sendTest = 1;
                $collectionDevice->addFieldToFilter('is_demo', 1);
                break;
            case '2': $sendLive = 1;
                $sendTest = 0;
                $collectionDevice->addFieldToFilter('is_demo', 0);
                break;
            default: $sendLive = 1;
                $sendTest = 1;
        }
        $data['collection_device'] = $collectionDevice;

        if ((int) $data['device_id'] != 0) {
            if ((int) $data['device_id'] == 2) {
                //send android
                $collectionDevice->addFieldToFilter('plaform_id', array('eq' => 3));
                return $this->sendAndroid($collectionDevice, $data);
            } else {
                //send IOS
                $collectionDevice->addFieldToFilter('plaform_id', array('neq' => 3));
                return $this->sendIOS($collectionDevice, $data);
            }
        } else {
            //send all
            $collection = $collectionDevice->addFieldToFilter('website_id', array('eq' => $website));
            $collectionAndroid = $collectionDevice2->addFieldToFilter('website_id', array('eq' => $website));

            $collection->addFieldToFilter('plaform_id', array('neq' => 3));
            $collectionAndroid->addFieldToFilter('plaform_id', array('eq' => 3));

            $resultIOS = $this->sendIOS($collection, $data);
            $resultAndroid = $this->sendAndroid($collectionAndroid, $data);
            if ($resultIOS || $resultAndroid)
                return true;
            else
                return false;
        }
    }

    public function sendIOS($collectionDevice, $data) {
        $ch = Mage::helper('simiconnector')->getDirPEMfile();
        $dir = Mage::helper('simiconnector')->getDirPEMPassfile();
        $message = $data['notice_content'];
        $body['aps'] = array(
            'alert' => $data['notice_title'],
            'sound' => 'default',
            'badge' => 1,
            'title' => $data['notice_title'],
            'message' => $message,
            'url' => $data['notice_url'],
            'type' => $data['type'],
            'productID' => $data['product_id'],
            'categoryID' => $data['category_id'],
            'categoryName' => $data['category_name'],
            'has_child' => $data['has_child'],
            'imageUrl' => $data['image_url'],
            'height' => $data['height'],
            'width' => $data['width'],
            'show_popup' => $data['show_popup'],
        );
        $payload = json_encode($body);
        $totalDevice = 0;
        if ($data['notice_sanbox'] == '0') { //send the old way
            foreach ($collectionDevice as $item) {
                $ctx = stream_context_create();
                stream_context_set_option($ctx, 'ssl', 'local_cert', $ch);
                //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
                $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
                if (!$fp) {
                    Mage::getSingleton('adminhtml/session')->addError("Failed to connect:" . $err . $errstr . PHP_EOL . "(IOS)");
                    return;
                }
                $deviceToken = $item->getDeviceToken();
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
                // Send it to the server
                $result = fwrite($fp, $msg, strlen($msg));
                if (!$result) {
                    Mage::getSingleton('adminhtml/session')->addError('Message not delivered (IOS)' . PHP_EOL);
                    return false;
                }
                $totalDevice++;
                fclose($fp);
            }
        } else {
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $ch);
            //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
            if (!$fp) {
                Mage::getSingleton('adminhtml/session')->addError("Failed to connect:" . $err . $errstr . PHP_EOL . "(IOS)");
                return;
            }
            foreach ($collectionDevice as $item) {
                $deviceToken = $item->getDeviceToken();
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
                // Send it to the server
                $result = fwrite($fp, $msg, strlen($msg));
                if (!$result) {
                    Mage::getSingleton('adminhtml/session')->addError('Message not delivered (IOS)' . PHP_EOL);
                    return false;
                }
                $totalDevice++;
            }
            fclose($fp);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message successfully delivered to %s devices (IOS)', $totalDevice));
        return true;
    }

    public function repeatSendAnddroid($total, $collectionDevice, $message) {
        $size = $total;
        while (true) {
            $from_user = 0;
            $check = $total - 999;
            if ($check <= 0) {
                //send to  (total+from_user) user from_user
                $is = $this->sendTurnAnroid($collectionDevice, $from_user, $from_user + $total, $message);
                if ($is == false) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Message not delivered (Android)'));
                    return false;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message successfully delivered to %s devices (Android)', $size));
                return true;
            } else {
                //send to 100 user from_user
                $is = $this->sendTurnAnroid($collectionDevice, $from_user, $from_user + 999, $message);
                if ($is == false) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Message not delivered (Android)'));
                    return false;
                }
                $total = $check;
                $from_user += 999;
            }
        }
    }

    public function sendTurnAnroid($collectionDevice, $from, $to, $message) {
        $registrationIDs = array();
        for ($i = $from; $i <= $to; $i++) {
            $item = $collectionDevice[$i];
            $registrationIDs[] = $item['device_token'];
        }

        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registrationIDs,
            'data' => array("message" => $message),
        );

        $api_key = Mage::getStoreConfig('simiconnector/android_key');
        $headers = array(
            'Authorization: key=' . $api_key,
            'Content-Type: application/json');

        $result = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            
        }

        $re = json_decode($result);

        if ($re == NULL || $re->success == 0) {
            return false;
        }
        return true;
    }

    public function sendAndroid($collectionDevice, $data) {
        $total = count($collectionDevice);
        $this->checkIndex($data);
        $message = array(
            'message' => $data['notice_content'],
            'url' => $data['notice_url'],
            'title' => $data['notice_title'],
            'type' => $data['type'],
            'productID' => $data['product_id'],
            'categoryID' => $data['category_id'],
            'categoryName' => $data['category_name'],
            'has_child' => $data['has_child'],
            'imageUrl' => $data['image_url'],
            'height' => $data['height'],
            'width' => $data['width'],
            'show_popup' => $data['show_popup'],
        );

        $this->repeatSendAnddroid($total, $collectionDevice->getData(), $message);
        return true;
    }

    public function checkIndex(&$data) {
        if (!isset($data['type'])) {
            $data['type'] = '';
        }
        if (!isset($data['product_id'])) {
            $data['product_id'] = '';
        }
        if (!isset($data['category_id'])) {
            $data['category_id'] = '';
        }
        if (!isset($data['category_name'])) {
            $data['category_name'] = '';
        }
        if (!isset($data['has_child'])) {
            $data['has_child'] = '';
        }
        if (!isset($data['image_url'])) {
            $data['image_url'] = '';
        }
        if (!isset($data['height'])) {
            $data['height'] = '';
        }
        if (!isset($data['width'])) {
            $data['width'] = '';
        }
        if (!isset($data['show_popup'])) {
            $data['show_popup'] = '';
        }
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('simiconnector');
    }

    // public function downloadFileAction() {
    //     $imageUrl = $this->getRequest()->getParam('image_url');
    //     if ($imageUrl) {
    //         $filename = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'notification' . DS . 'images' . DS . $app_icon . '.zip';
    //         $this->_prepareDownloadResponse('app_icon_' . $app_icon . '.zip', file_get_contents($filename));
    //         return;
    //     }
    // }

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

    /*
     * Get Device to Push Notification
     */

    public function chooseDevicesAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'simiconnector/adminhtml_siminotification_edit_tab_devices', 'promo_widget_chooser_device_id', array('js_form_object' => $request->getParam('form'),
        ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

}
