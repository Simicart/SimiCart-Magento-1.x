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
 * Banner Adminhtml Controller
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Adminhtml_Simiconnector_BannerController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Simi_Connector_Adminhtml_BannerController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('simiconnector/banner')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Banners Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
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
        $model = Mage::getModel('simiconnector/banner')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data))
                $model->setData($data);

            Mage::register('banner_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/banner');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Banner'), Mage::helper('adminhtml')->__('Item banner'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_banner_edit_tabs'));

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
            /*
             * Banner Image
             */
            if (isset($_FILES['banner_name_co']['name']) && $_FILES['banner_name_co']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('banner_name_co');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['banner_name_co']['name']);                    
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'banner' ;
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $result = $uploader->save($path, $_FILES['banner_name_co']['name']);
                    try {
                        chmod($path.'/'.$result['file'], 0777); 
                    } catch (Exception $e) {

                    }
                    $data['banner_name'] = Mage::getBaseUrl('media') . 'simi/simiconnector/banner/' . $result['file'];
                } catch (Exception $e) {
                    $data['banner_name'] = Mage::getBaseUrl('media') . 'simi/simiconnector/banner/' . $_FILES['banner_name_co']['name'];
                }
            } 
            if (isset($data['banner_name_co']['delete']) && $data['banner_name_co']['delete'] == 1) {                
                Mage::helper('simiconnector')->deleteBanner($data['banner_name_co']['value']);
                $data['banner_name'] = '';
            } 
            
            
            /*
             * Tablet Banner Image
             */
            
            if (isset($_FILES['banner_name_tablet_co']['name']) && $_FILES['banner_name_tablet_co']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('banner_name_tablet_co');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['banner_name_tablet_co']['name']);                    
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'banner' ;
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $result = $uploader->save($path, $_FILES['banner_name_tablet_co']['name']);
                    try {
                        chmod($path.'/'.$result['file'], 0777); 
                    } catch (Exception $e) {

                    }
                    $data['banner_name_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/banner/' . $result['file'];
                } catch (Exception $e) {
                    $data['banner_name_tablet'] = Mage::getBaseUrl('media') . 'simi/simiconnector/banner/' . $_FILES['banner_name_tablet_co']['name'];
                }
            } 
            if (isset($data['banner_name_tablet_co']['delete']) && $data['banner_name_tablet_co']['delete'] == 1) {                
                Mage::helper('simiconnector')->deleteBanner($data['banner_name_tablet_co']['value']);
                $data['banner_name_tablet'] = '';
            }  
            
            
            if(isset($data['type']) && $data['type'] != 2 && $data['type'] != 3){
                $data['type'] = 1;
            }   
            $model = Mage::getModel('simiconnector/banner');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Banner was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($data['storeview_id'] && is_array($data['storeview_id'])) {
                    $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
                    $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                            ->addFieldToFilter('content_type', $typeID)
                            ->addFieldToFilter('item_id', $model->getId());
                    foreach ($visibleStoreViews as $visibilityItem)
                        $visibilityItem->delete();
                    foreach ($data['storeview_id'] as $storeViewId){
                        $visibilityItem = Mage::getModel('simiconnector/visibility');
                        $visibilityItem->setData('content_type',$typeID);                        
                        $visibilityItem->setData('item_id',$model->getId());
                        $visibilityItem->setData('store_view_id',$storeViewId);
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
                $model = Mage::getModel('simiconnector/banner');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
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
                    $madapter = Mage::getModel('simiconnector/banner')->load($bannerId);
                    $madapter->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($bannerIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $bannerIds = $this->getRequest()->getParam('simiconnector');
        if (!is_array($bannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $bannerId = Mage::getSingleton('simiconnector/banner')
                            ->load($bannerId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($bannerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'banner.csv';
        $content = $this->getLayout()->createBlock('simiconnector/adminhtml_banner_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'banner.xml';
        $content = $this->getLayout()->createBlock('simiconnector/adminhtml_banner_grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('simiconnector');
    }

    public function chooserMainCategoriesAction(){
        $request = $this->getRequest();
        $id = $request->getParam('selected', array());
        $block = $this->getLayout()->createBlock('simiconnector/adminhtml_banner_edit_tab_categories','maincontent_category', array('js_form_object' => $request->getParam('form')))
                ->setCategoryIds($id);
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    public function chooserMainProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'simiconnector/adminhtml_banner_edit_tab_products', 'promo_widget_chooser_sku', array('js_form_object' => $request->getParam('form'),
                ));
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

}