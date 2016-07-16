<?php

class Simi_Simiconnector_Adminhtml_Simiconnector_SimicategoryController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('simiconnector/simicategory')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Categories Manager'), Mage::helper('adminhtml')->__('Category Manager'));
        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('simiconnector/simicategory')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('simicategory_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/simicategory');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Category Manager'), Mage::helper('adminhtml')->__('Category Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Category News'), Mage::helper('adminhtml')->__('Category News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_simicategory_edit'))
                    ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_simicategory_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Category does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['simicategory_filename']['name']) && $_FILES['simicategory_filename']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('simicategory_filename');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['simicategory_filename']['name']);
                    $website = $data['website_id'];
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'simicategory' . DS . $website;
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $result = $uploader->save($path, $_FILES['simicategory_filename']['name']);
                    $data['simicategory_filename'] = Mage::getBaseUrl('media') . 'simi/simiconnector/simicategory/' . $result['file'];
                } catch (Exception $e) {
                    $data['simicategory_filename'] = Mage::getBaseUrl('media') . 'simi/simiconnector/simicategory/' . $_FILES['simicategory_filename']['name'];
                }
            }

            if (is_array($data['simicategory_filename'])) {
                if ($data['simicategory_filename']['delete'] == 1) {
                    $data['simicategory_filename'] = "";
                } else {
                    $data['simicategory_filename'] = $data['simicategory_filename']['value'];
                }
            }

            if (isset($_FILES['simicategory_filename_tablet']['name']) && $_FILES['simicategory_filename_tablet']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('simicategory_filename_tablet');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['simicategory_filename_tablet']['name']);
                    $website = $data['website_id'];
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'simicategory' . DS . $website;
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $result = $uploader->save($path, $_FILES['simicategory_filename_tablet']['name']);
                    $data['simicategory_filename_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/simicategory/' . $result['file'];
                } catch (Exception $e) {
                    $data['simicategory_filename_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/simicategory/' . $_FILES['simicategory_filename_tablet']['name'];
                }
            }

            if (is_array($data['simicategory_filename_tablet'])) {
                if ($data['simicategory_filename_tablet']['delete'] == 1) {
                    $data['simicategory_filename_tablet'] = "";
                } else {
                    $data['simicategory_filename_tablet'] = $data['simicategory_filename_tablet']['value'];
                }
            }

            if (isset($data['category_id']) && $data['category_id']) {
                $category_name = Mage::getModel('catalog/category')->load($data['category_id'])->getName();
                $data['simicategory_name'] = $category_name;
            }

            if (!$data['matrix_width_percent_tablet'])
                $data['matrix_width_percent_tablet'] = $data['matrix_width_percent'];
            if (!$data['matrix_height_percent_tablet'])
                $data['matrix_height_percent_tablet'] = $data['matrix_height_percent'];

            $model = Mage::getModel('simiconnector/simicategory');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {

                $model->save();
                Mage::helper('simiconnector/productlist')->updateMatrixRowHeight($data['matrix_row'], $data['matrix_height_percent'], $data['matrix_height_percent_tablet'] );
                 Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Category was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($data['storeview_id'] && is_array($data['storeview_id'])) {
                    $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
                    $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                            ->addFieldToFilter('content_type', $typeID)
                            ->addFieldToFilter('item_id', $model->getId());
                    foreach ($visibleStoreViews as $visibilityItem)
                        $visibilityItem->delete();
                    foreach ($data['storeview_id'] as $storeViewId) {
                        $visibilityItem = Mage::getModel('simiconnector/visibility');
                        $visibilityItem->setData('content_type', $typeID);
                        $visibilityItem->setData('item_id', $model->getId());
                        $visibilityItem->setData('store_view_id', $storeViewId);
                        $visibilityItem->save();
                    }
                }
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/simicategory');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $simicategoryIds = $this->getRequest()->getParam('simicategory');
        if (!is_array($simicategoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($simicategoryIds as $simicategoryId) {
                    $simicategory = Mage::getModel('simiconnector/simicategory')->load($simicategoryId);
                    $simicategory->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($simicategoryIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $simicategoryIds = $this->getRequest()->getParam('simicategory');
        if (!is_array($simicategoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($simicategoryIds as $simicategoryId) {
                    $simicategory = Mage::getSingleton('simiconnector/simicategory')
                            ->load($simicategoryId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($simicategoryIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'simicategory.csv';
        $content = $this->getLayout()->createBlock('simiconnector/adminhtml_simicategory_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'simicategory.xml';
        $content = $this->getLayout()->createBlock('simiconnector/adminhtml_simicategory_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function chooserMainCategoriesAction() {
        $request = $this->getRequest();
        $id = $request->getParam('selected', array());
        $block = $this->getLayout()->createBlock('simiconnector/adminhtml_banner_edit_tab_categories', 'maincontent_category', array('js_form_object' => $request->getParam('form')))
                ->setCategoryIds($id);
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

}
