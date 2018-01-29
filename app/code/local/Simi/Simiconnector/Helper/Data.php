<?php

class Simi_Simiconnector_Helper_Data extends Mage_Core_Helper_Abstract
{

    public $_iphone = 'ios';
    public $_ipad = 'ipad';
    public $_android = 'android';


    public function getDevice() 
    {
        return array(
            1 => 'Iphone',
            2 => 'Ipad',
            3 => 'Android',
        );
    }


    public function getNameDeviceById($id) 
    {
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

    public function deleteFile($path) 
    {
        try {
            unlink($path);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getWebsites() 
    {
        $websites = Mage::getModel('core/website')->getCollection();
        return $websites;
    }

    public function deleteBanner($value) 
    {
        try {
            unlink($value);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getVisibilityTypeId($contentTypeName) 
    {
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
    
    public function flushStaticCache() {
        $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache";
        if (is_dir($path)) {
            $this->_removeFolder($path);
        }
    }

    private function _removeFolder($folder){
        if (is_dir($folder))
            $dir_handle = opendir($folder);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($folder."/".$file))
                    unlink($folder."/".$file);
                else
                    $this->_removeFolder($folder.'/'.$file);
            }
        }
        closedir($dir_handle);
        rmdir($folder);
        return true;
    }
}
