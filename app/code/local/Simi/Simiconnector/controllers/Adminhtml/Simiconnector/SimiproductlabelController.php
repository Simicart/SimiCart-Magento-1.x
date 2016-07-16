<?php

class Simi_Simiconnector_Adminhtml_Simiconnector_SimiproductlabelController extends Mage_Adminhtml_Controller_Action {

    /**
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('simiconnector/simiproductlabel')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Labels'), Mage::helper('adminhtml')->__('Labels')
        );
        return $this;
    }

    public function editAction() {
        $labelId = $this->getRequest()->getParam('label_id');
        $model = Mage::getModel('simiconnector/simiproductlabel')->load($labelId);
        if ($model->getId() || $labelId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('simiproductlabel_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/simiproductlabel');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Label Manager'), Mage::helper('adminhtml')->__('Label Manager')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_simiproductlabel_edit'))
                    ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_simiproductlabel_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('simiconnector')->__('Label does not exist')
            );
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
            
            if (isset($_FILES['image_name_co']['name']) && $_FILES['image_name_co']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image_name_co');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    str_replace(" ", "_", $_FILES['image_name_co']['name']);
                    $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . 'productlabel';
                    if (!is_dir($path)) {
                        try {
                            mkdir($path, 0777, TRUE);
                        } catch (Exception $e) {
                            
                        }
                    }
                    $result = $uploader->save($path, $_FILES['image_name_co']['name']);
                    try {
                        chmod($path . '/' . $result['file'], 0777);
                    } catch (Exception $e) {
                        
                    }
                    $data['image'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlabel/' . $result['file'];
                } catch (Exception $e) {
                    $data['image'] = Mage::getBaseUrl('media') . 'simi/simiconnector/productlabel/' . $_FILES['image_name_co']['name'];
                }                
            }
            if (isset($data['image_name_co']['delete']) && $data['image_name_co']['delete'] == 1) {
                try {
                    unlink($data['image_name_co']['value']);
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
                $data['image'] = '';
            }
            
            $model = Mage::getModel('simiconnector/simiproductlabel');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('label_id'));
            
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('simiconnector')->__('Label was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('label_id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('label_id' => $this->getRequest()->getParam('label_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('simiconnector')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('label_id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/simiproductlabel');
                $model->setId($this->getRequest()->getParam('label_id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Label was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('label_id' => $this->getRequest()->getParam('label_id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $labelIds = $this->getRequest()->getParam('label_id');
        if (!is_array($labelIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($labelIds as $labelId) {
                    $label = Mage::getModel('simiconnector/simiproductlabel')->load($labelId);
                    $label->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($labelIds))
                );
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
        $labelIds = $this->getRequest()->getParam('label_id');
        if (!is_array($labelIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($labelIds as $labelId) {
                    Mage::getSingleton('simiconnector/simiproductlabel')
                            ->load($labelId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($labelIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('connector');
    }

    public function chooserMainProductsAction() {
        $request = $this->getRequest();
        $block = $this->getLayout()->createBlock(
                'simiconnector/adminhtml_simiproductlabel_edit_tab_products', 'simiproductlabel_widget_chooser_sku', array('js_form_object' => $request->getParam('form'),
        ));
        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

}
