<?php

class Simi_Simiconnector_Model_Simibarcode extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/simibarcode');
    }

    /*
    public function checkCode($data) {
        $code = $data->code;
        $type = $data->type;
        $arrayReturn = array();
        $information = $this->statusError(array(Mage::helper('simibarcode')->__('No Product matching the code')));
        if (isset($code) && $code != '') {
            if ($type == '1') {
                $qrcode = Mage::getModel('simibarcode/simibarcode')->load($code, 'qrcode');
                if ($qrcode->getId() && $qrcode->getBarcodeStatus() == '1') {
                    // if($code == 'King'){
                    $productId = $qrcode->getProductEntityId();
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($product->getStatus() == '1') {
                        $information = $this->statusSuccess();
                        $arrayReturn[] = array('product_id' => $productId);
                        $information['data'] = $arrayReturn;
                    }
                }
            } else {
                $barcode = Mage::getModel('simibarcode/simibarcode')->load($code, 'barcode');
                if ($barcode->getId() && $barcode->getBarcodeStatus() == '1') {
                    $productId = $barcode->getProductEntityId();
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($product->getStatus() == '1') {
                        $information = $this->statusSuccess();
                        $arrayReturn[] = array('product_id' => $productId);
                        $information['data'] = $arrayReturn;
                    }
                }
            }
        }
        return $information;
    }
     * 
     */
}
