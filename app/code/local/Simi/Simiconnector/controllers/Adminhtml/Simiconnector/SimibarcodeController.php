<?php

class Simi_Simiconnector_Adminhtml_Simiconnector_SimibarcodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Simi_Simibarcode_Adminhtml_SimibarcodeController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('simiconnector/simibarcode')
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
        $simibarcodeId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('simiconnector/simibarcode')->load($simibarcodeId);
        $this->_title($this->__('Barcode'));
        if (!$simibarcodeId) {
            $this->_title($this->__('Add New Barcode'));
        } else {
            $this->_title($this->__('Edit Barcode'));
        }
        if ($model->getId() || $simibarcodeId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('simibarcode_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('simiconnector/simibarcode');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Manage Barcodes'), Mage::helper('adminhtml')->__('Manage Barcodes')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Add New Custom Barcode'), Mage::helper('adminhtml')->__('Add New Custom Barcode')
            );
            if (!$simibarcodeId) {
                $this->getLayout()->getBlock('head')
                        ->addCss('css/simi/simibarcode/hiddenleftslide.css');
            }

            $this->_addContent($this->getLayout()->createBlock('simiconnector/adminhtml_simibarcode_edit'))
                    ->_addLeft($this->getLayout()->createBlock('simiconnector/adminhtml_simibarcode_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('simiconnector')
                            ->__('Barcode does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * products action
     */
    public function productsAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_status');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode_auto');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('barcode');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qrcode');

        $this->renderLayout();
    }

    /**
     * products Grid action
     */
    public function productsGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('barcode.edit.tab.products')
                ->setProducts($this->getRequest()->getPost('barcode_products', null));
        $this->renderLayout();
    }

    /**
     * check barcode dupplicate
     */
    public function checkDupplicate($barcode) {
        $code = Mage::helper('simiconnector/simibarcode')->generateCode(Mage::getStoreConfig('simiconnector/barcode/pattern'));
        if (in_array($code, $barcode)) {
            $code = $this->checkDupplicate($barcode);
        }
        return $code;
    }

    /**
     * check QRcode dupplicate
     */
    public function checkDupplicateQrcode($qrcode) {
        $code = Mage::helper('simiconnector/simibarcode')->generateCode(Mage::getStoreConfig('simiconnector/barcode/qrcode_pattern'));
        if (in_array($code, $qrcode)) {
            $code = $this->checkDupplicate($qrcode);
        }
        return $code;
    }

    /**
     * save item action
     */
    public function saveAction() {
        if ($post = $this->getRequest()->getPost()) {
            $model = Mage::getModel('simiconnector/simibarcode')->load($this->getRequest()->getParam('id'));
            try {
                if ($model->getId()) {
                    $model->setData('barcode_status', $post['barcode_status'])
                            ->setData('barcode', $post['barcode'])
                            ->setData('qrcode', $post['qrcode'])
                            ->save()
                    ;
                    if ($this->getRequest()->getParam('back')) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Barcode "%s" was successfully edited.', $model->getBarcode()));
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Barcode "%s" was successfully edited.', $model->getBarcode()));

                    $this->_redirect('*/*');
                    return;
                }
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');

                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;

                $tablename = 'simiconnector/simibarcode';

                $results = Mage::helper('simiconnector/simibarcode')->getAllColumOfTable($tablename);

                $columns = array();
                $string = '';
                $type = '';

                foreach ($results as $result) {
                    $fields = explode('_', $result);
                    if ($fields[0] == 'barcode' || $fields[0] == 'qrcode')
                        continue;
                    foreach ($fields as $id => $field) {
                        if ($id == 0)
                            $type = $field;
                        if ($id == 1) {
                            $string = $field;
                        }
                        if ($id > 1)
                            $string = $string . '_' . $field;
                    }
                    $columns[] = array($type => $string);
                    $string = '';
                    $type = '';
                }


                if (isset($post['barcode_products'])) {
                    $products = array();
                    $productsExplodes = explode('&', urldecode($post['barcode_products']));

                    if (count($productsExplodes) <= 900) {
                        parse_str(urldecode($post['barcode_products']), $products);
                    } else {
                        foreach ($productsExplodes as $productsExplode) {
                            $product = '';
                            parse_str($supplierProductsExplode, $product);
                            $products = $products + $product;
                        }
                    }

                    if (count($products)) {
                        $productIds = '';
                        $count = 0;
                        $j = 0;
                        $barcode = array();
                        $qrcode = array();
                        foreach ($products as $pId => $enCoded) {
                            $codeArr = array();
                            parse_str(base64_decode($enCoded), $codeArr);
                            //auto generate barcode
                            if ($codeArr['barcode'] == '') {
                                //check barcode dupplicate
                                $codeArr['barcode'] = $this->checkDupplicate($barcode);
                                $barcode[] = $codeArr['barcode'];
                            } else {
                                //generate barcode by hand
                                //check barcode already exist
                                if (!$model->getId()) {
                                    $checkBarcodeExist = Mage::getModel('simiconnector/simibarcode')->load($codeArr['barcode'], 'barcode');
                                    if ($checkBarcodeExist->getId()) {
                                        Mage::getSingleton('adminhtml/session')->addError(
                                                Mage::helper('simiconnector')->__('The barcode "%s" was already exist!', $codeArr['barcode'])
                                        );
                                        Mage::getSingleton('adminhtml/session')->setFormData($post);
                                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                        return;
                                    }
                                }
                                //check barcode dupplicate
                                if (in_array($codeArr['barcode'], $barcode)) {
                                    Mage::getSingleton('adminhtml/session')->addError(
                                            Mage::helper('simiconnector')->__('The barcode "%s" was already duplicate!', $codeArr['barcode'])
                                    );
                                    Mage::getSingleton('adminhtml/session')->setFormData($post);
                                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                    return;
                                } else {
                                    $barcode[] = $codeArr['barcode'];
                                }
                            }
                            //auto generate QRcode
                            if ($codeArr['qrcode'] == '') {
                                //check barcode dupplicate
                                $codeArr['qrcode'] = $this->checkDupplicateQrcode($qrcode);
                                $qrcode[] = $codeArr['qrcode'];
                            } else {
                                //generate barcode by hand
                                //check barcode already exist
                                if (!$model->getId()) {
                                    $checkQrcodeExist = Mage::getModel('simiconnector/simibarcode')->load($codeArr['qrcode'], 'qrcode');
                                    if ($checkQrcodeExist->getId()) {
                                        Mage::getSingleton('adminhtml/session')->addError(
                                                Mage::helper('simiconnector')->__('The QRcode "%s" was already exist!', $codeArr['qrcode'])
                                        );
                                        Mage::getSingleton('adminhtml/session')->setFormData($post);
                                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                        return;
                                    }
                                }
                                //check QRcode dupplicate
                                if (in_array($codeArr['qrcode'], $qrcode)) {
                                    Mage::getSingleton('adminhtml/session')->addError(
                                            Mage::helper('simiconnector')->__('The QRcode "%s" was already duplicate!', $codeArr['qrcode'])
                                    );
                                    Mage::getSingleton('adminhtml/session')->setFormData($post);
                                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                                    return;
                                } else {
                                    $qrcode[] = $codeArr['qrcode'];
                                }
                            }
                            $sqlNews[$j] = array(
                                'barcode' => $codeArr['barcode'],
                                'qrcode' => $codeArr['qrcode'],
                                'barcode_status' => 1,
                                    // 'barcode_status' => $codeArr['barcode_status'],
                            );

                            foreach ($columns as $id => $column) {
                                $i = 0;
                                $columnName = '';

                                foreach ($column as $_id => $key) {
                                    if ($i == 0)
                                        $columnName = $_id . '_' . $key;
                                    if ($i > 0)
                                        $columnName = $columnName . '_' . $key;

                                    $i++;
                                }

                                if ($_id != 'custom') {

                                    $return = Mage::helper('simiconnector/simibarcode')->getValueForBarcode($_id, $key, $pId, $codeArr);
                                    if (is_array($return)) {
                                        foreach ($return as $_columns) {
                                            foreach ($_columns as $_column => $value) {
                                                if (!isset($sqlNews[$_id . '_' . $_column])) {
                                                    $sqlNews[$j][$_id . '_' . $_column] = $value;
                                                } else {
                                                    $sqlNews[$j][$_id . '_' . $_column] .= ',' . $value;
                                                }
                                            }
                                        }
                                    } else {
                                        $sqlNews[$j][$columnName] = $return;
                                    }
                                } else {
                                    if (isset($codeArr[$columnName]))
                                        $sqlNews[$j][$columnName] = $codeArr[$columnName];
                                }
                            }
                            $sqlNews[$j]['created_date'] = now();
                            if (count($sqlNews) == 1000) {
                                $writeConnection->insertMultiple($resource->getTableName('simiconnector/simibarcode'), $sqlNews);
                                $sqlNews = array();
                            }

                            $j++;
                        }
                    }
                }

                if (!empty($sqlNews)) {
                    $writeConnection->insertMultiple($resource->getTableName('simiconnector/simibarcode'), $sqlNews);
                }

                Mage::getModel('admin/session')->setData('barcode_product_import', null);

                if ($this->getRequest()->getParam('back')) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Barcode was successfully saved.'));
                    $this->_redirect('*/*/new');
                    return;
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('simiconnector')->__('Barcode was successfully saved.'));

                $this->_redirect('*/*');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($post);
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('simiconnector')->__('Unable to find barcode to save!')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('simiconnector/simibarcode');
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
        $simibarcodeIds = $this->getRequest()->getParam('simibarcode');
        if (!is_array($simibarcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($simibarcodeIds as $simibarcodeId) {
                    $simibarcode = Mage::getModel('simiconnector/simibarcode')->load($simibarcodeId);
                    $simibarcode->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($simibarcodeIds)));
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
        $simibarcodeIds = $this->getRequest()->getParam('simibarcode');
        if (!is_array($simibarcodeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($simibarcodeIds as $simibarcodeId) {
                    $simibarcode = Mage::getSingleton('simiconnector/simibarcode')
                            ->load($simibarcodeId)
                            ->setBarcodeStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($simibarcodeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('simiconnector');
    }

    /**
     * export grid item to XML type
     */
    public function getImportCsvAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {

            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $product = array();
                $products = array();
                $fields = array();
                $count = 0;
                $helper = Mage::helper('simiconnector/simibarcode');

                if (count($dataFile))
                    foreach ($dataFile as $col => $row) {
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {

                            if (count($row))
                                foreach ($row as $index => $cell) {

                                    if (isset($fields[$index])) {
                                        $product[$fields[$index]] = $cell;
                                    }
                                }

                            $productId = Mage::getModel('catalog/product')->getIdBySku($product['SKU']);
                            $product['PRODUCT_ID'] = $productId;

                            if ($productId) {
                                $products[] = $product;
                            }
                        }
                    }

                $helper->importProduct($products);
            } catch (Exception $e) {
                
            }
        }
    }
    
    public function barcodeAction(){
        $code = $this->getRequest()->getParam('code');
        Mage::helper('simiconnector/simibarcode')->createBarcode(null, $code, "100", "horizontal", "code128", false);
    }

}
