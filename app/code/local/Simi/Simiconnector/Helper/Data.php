<?php

class Simi_Simiconnector_Helper_Data extends Mage_Core_Helper_Abstract {

    public $_iphone = 'ios';
    public $_ipad = 'ipad';
    public $_android = 'android';

    public function getDataDesgin() {
        return array(
            array(
                'theme_color' => '#FFA238',
                'theme_logo' => '',
                'device_id' => 1,
                'app_name' => 'N/A'
            ),
            array(
                'theme_color' => '#FFA238',
                'theme_logo' => '',
                'device_id' => 2,
                'app_name' => 'N/A'
            ),
            array(
                'theme_color' => '#FFA238',
                'theme_logo' => '',
                'device_id' => 3,
                'app_name' => 'N/A'
            ),
        );
    }

    public function getDevice() {
        return array(
            1 => 'Iphone',
            2 => 'Ipad',
            3 => 'Android',
        );
    }

    public function getDeviceIdByName($name) {
        $id = 1;
        switch ($name) {
            case $this->_iphone:
                $id = 1;
                break;
            case $this->_ipad:
                $id = 2;
                break;
            case $this->_android:
                $id = 3;
                break;
        }
        return $id;
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
            default :
                $typeId = 0;
                break;
        }
        return $typeId;
    }

}
