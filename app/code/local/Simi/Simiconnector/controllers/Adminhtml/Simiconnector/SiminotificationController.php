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
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'notification' . DS . 'images';
                    // $result = $uploader->save($path, $_FILES['image_url']['name'] );
                    $result = $uploader->save($path, md5(time()) . '.png');
                    $imageUrl = 'simi/simiconnector/notification/images/' . md5(time()) . '.png';
                } catch (Exception $e) {
                    $imageUrl = 'simi/simiconnector/notification/images/' . md5(time()) . '.png';
                }
            }
            // Zend_debug::dump($data);die();

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
            $this->getResponse()->setBody($block->toHtml());
        }
    }

}
