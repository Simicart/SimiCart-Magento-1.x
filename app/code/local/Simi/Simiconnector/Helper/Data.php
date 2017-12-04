<?php

class Simi_Simiconnector_Helper_Data extends Mage_Core_Helper_Abstract {

    public $_iphone = 'ios';
    public $_ipad = 'ipad';
    public $_android = 'android';


    public function getDevice() {
        return array(
            1 => 'Iphone',
            2 => 'Ipad',
            3 => 'Android',
        );
    }


    public function getNameDeviceById($id) {
        $name = '';
        switch ($id) {
            case 1:
                $name = $this->_iphone;
                break;
            case 2:
                $name = $this->_iphone;
                break;
            case 3:
                $name = $this->_android;
                break;
            default :
                $name = $this->_iphone;
                break;
        }
        return $name;
    }

    public function deleteFile($path) {
        try {
            unlink($path);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getWebsites() {
        $websites = Mage::getModel('core/website')->getCollection();
        return $websites;
    }

    public function deleteBanner($value) {
        try {
            unlink($value);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getVisibilityTypeId($contentTypeName) {
        switch ($contentTypeName) {
            case 'cms':
                $typeId = 1;
                break;
            case 'banner':
                $typeId = 2;
                break;
            case 'homecategory':
                $typeId = 3;
                break;
            case 'productlist':
                $typeId = 4;
                break;
            case 'storelocator':
                $typeId = 5;
                break;
            default :
                $typeId = 0;
                break;
        }
        return $typeId;
    }

}
