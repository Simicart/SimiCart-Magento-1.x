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
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Adminhtml_Simiconnector_ProductlistController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Simi_Connector_Adminhtml_BannerController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('simiconnector/productlist')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Custom Product List Manager'), Mage::helper('adminhtml')->__('Product List Manager'));
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
        $model = Mage::getModel('simiconnector/productlist')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('productlist_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/productlist');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Notice'), Mage::helper('adminhtml')->__('Item Notice'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_productlist_edit'))
                    ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_productlist_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')->__('Item does not exist'));
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
            if (isset($_FILES['productlist_image_o']['name']) && $_FILES['productlist_image_o']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('productlist_image_o');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['productlist_image_o']['name']);
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'productlist';
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $file_name = explode(".", $_FILES['productlist_image_o']['name']);
                    $fName = $file_name[0] . "@2x." . $file_name[1];
                    $fName = str_replace(" ", "_", $fName);
                    $result = $uploader->save($path, $fName);
                    rename($path . DS . $result['file'], $path . DS . $fName);
                    $data['list_image'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlist/' . $fName;
                } catch (Exception $e) {
                    $data['list_image'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlist/' . $_FILES['productlist_image_o']['name'];
                }
            }

            if (isset($data['productlist_image_o']['delete']) && $data['productlist_image_o']['delete'] == 1) {
                Mage::helper('simiconnector')->deleteFile($data['productlist_image_o']['value']);
                $data['list_image'] = '';
            }

            if (isset($_FILES['productlist_image_tablet_o']['name']) && $_FILES['productlist_image_tablet_o']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('productlist_image_tablet_o');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['productlist_image_tablet_o']['name']);
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'productlist';
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $file_name = explode(".", $_FILES['productlist_image_tablet_o']['name']);
                    $fName = $file_name[0] . "@2x." . $file_name[1];
                    $fName = str_replace(" ", "_", $fName);
                    $result = $uploader->save($path, $fName);
                    rename($path . DS . $result['file'], $path . DS . $fName);
                    $data['list_image_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlist/' . $fName;
                } catch (Exception $e) {
                    $data['list_image_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlist/' . $_FILES['productlist_image_tablet_o']['name'];
                }
            }

            if (isset($data['productlist_image_tablet_o']['delete']) && $data['productlist_image_tablet_o']['delete'] == 1) {
                Mage::helper('simiconnector')->deleteFile($data['productlist_image_tablet_o']['value']);
                $data['list_image_tablet'] = '';
            }

            if (!$data['matrix_width_percent_tablet'])
                $data['matrix_width_percent_tablet'] = $data['matrix_width_percent'];
            if (!$data['matrix_height_percent_tablet'])
                $data['matrix_height_percent_tablet'] = $data['matrix_height_percent'];
            
            $model = Mage::getModel('simiconnector/productlist');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::helper('simiconnector/productlist')->updateMatrixRowHeight($data['matrix_row'], $data['matrix_height_percent'], $data['matrix_height_percent_tablet'] );
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Product List was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($data['storeview_id'] && is_array($data['storeview_id'])) {
                    $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
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

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/productlist');
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

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $bannerIds = $this->getRequest()->getParam('simiconnector');

        if (!is_array($bannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $notice = Mage::getModel('simiconnector/productlist')->load($bannerId);
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

    public function chooserMainProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'simiconnector/adminhtml_productlist_edit_tab_products', 'promo_widget_chooser_sku', array('js_form_object' => $request->getParam('form'),
        ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function getMockupAction() {
        $storeviewid = $this->getRequest()->getParam('storeview_id');
        echo Mage::helper('simiconnector/productlist')->getMatrixLayoutMockup($storeviewid);
        exit();
    }

}
