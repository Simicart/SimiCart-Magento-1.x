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

    public function detectMobile($user_agent = '') {
        if (strstr($user_agent, 'iPhone') || strstr($user_agent, 'iPod')) {
            return 1;
        } elseif (strstr($user_agent, 'iPad')) {
            return 2;
        } elseif (strstr($user_agent, 'Android')) {
            return 3;
        } else {
            return 1;
        }
    }

    public function getTotalRate($rates) {
        $total = $rates[0] * 1 + $rates[1] * 2 + $rates[2] * 3 + $rates[3] * 4 + $rates[4] * 5;
        return $total;
    }

    public function getAvgRate($rates, $total) {
        if ($rates[5] != 0)
            $avg = $total / $rates[5];
        else
            $avg = 0;
        return $avg;
    }
    

    public function getFileLocaleByStoreId($store_id, $device_id) {
        $device_name = $this->getNameDeviceById($device_id);
        $website = Mage::app()->getStore()->getWebsiteId();
        $file = Mage::getBaseUrl('media') . 'simi/simicart/import/locale/' . $device_name . '/' . $website . '/' . $store_id . '/locale.plist';
        if (file_exists($this->getDirLocaleByStoreId($store_id, $device_id))) {
            return $file;
        }
        return '';
    }

    public function getDirLocaleByStoreId($store_id, $device_id) {
        $device_name = $this->getNameDeviceById($device_id);
        $website = Mage::app()->getStore()->getWebsiteId();
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'import' . DS . 'locale' . DS . $device_name . DS . $website . DS . $store_id . DS . 'locale.plist';
    }

    public function getDirLocalePlist($device_name, $store_id, $web_id) {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'import' . DS . 'locale' . DS . $device_name . DS . $web_id . DS . $store_id . DS . 'locale.plist';
    }

    public function getDirLocaleCsvByDevice($device_name) {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'import' . DS . 'locale' . DS . $device_name;
    }

    public function getDirLocaleCsvByWebsite($web) {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'import' . DS . 'locale' . DS . $web;
    }

    public function getFileLocaleCsvByDevice($device_name) {
        return Mage::getBaseUrl('media') . 'simi/simicart/import/locale/' . $device_name;
    }

    public function getFileLocaleCsvByWebsite($web) {
        return Mage::getBaseUrl('media') . 'simi/simicart/import/locale/' . $web;
    }

    public function getDirLogoImage($middle_path) {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'logo' . DS . $middle_path . DS . 'logo.png';
    }

    public function getLogoImage($middle_path) {
        return Mage::getBaseUrl('media') . 'simi/simicart/logo/' . $middle_path . '/logo.png';
    }

    public function saveLogo($web_id, $image) {
        if (isset($image) && $image != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('theme_logo');

                // Any extention would work
                $uploader->setAllowedExtensions(array('png'));
                $uploader->setAllowRenameFiles(true);
                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //	(file.jpg will go in something like /media/f/i/file.jpg)
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'logo' . DS . $web_id;
                $path_cache = $path . DS . 'logo.png';
                if (file_exists($path_cache)) {
                    $this->deleteLogo($web_id);
                }
                $uploader->save($path, 'logo.png');
                // $image_path = $path . DS . 'logo.png';
                // $imageObj = new Varien_Image($image_path);
                // $imageObj->constrainOnly(TRUE);
                // $imageObj->keepAspectRatio(TRUE);
                // $imageObj->keepFrame(FALSE);
                // $imageObj->resize(640, 180);
                // $imageObj->save($image_path);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function importLocale($web_id, $store_id, $device_id, $file) {
        $name_device = $this->getNameDeviceById($device_id);
        if (isset($file) && $file) {
            try {
                $uploader = new Varien_File_Uploader('file_locale');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = $this->getDirLocaleCsvByDevice($name_device);
                $path .= DS . $web_id . DS . $store_id;
                $path_cache = $path . DS . 'locale.csv';
                if (file_exists($path_cache)) {
                    $this->deleteFile($path_cache);
                }
                $uploader->save($path, 'locale.csv');
                $this->convertToPlist($name_device, $web_id, $store_id);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function deleteFile($path) {
        try {
            unlink($path);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function convertToPlist($name_device, $web_id, $store_id) {
        //$store_id = 1;
        $file = $this->getDirLocalePlist($name_device, $web_id, $store_id);
        $domimpl = new DOMImplementation();
        $dtd = $domimpl->createDocumentType('plist', '-//Apple Computer//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
        $doc = $domimpl->createDocument(null, "plist", $dtd);
        $doc->encoding = "UTF-8";
        $plist = $doc->documentElement;
        $plist->setAttribute('version', '1.0');
        $dist = $doc->createElement('dict');
        $plist->appendChild($dist);
        $importCsvFile = new Varien_File_Csv();
        $data_1 = $importCsvFile->getData($this->getDirLocaleCsvByDevice($name_device) . DS . $web_id . DS . $store_id . DS . 'locale.csv');
        try {
            foreach ($data_1 as $item) {
                $node_key = $doc->createElement("key", $item[0]);
                $node_string = $doc->createElement("string", $item[1]);
                $dist->appendChild($node_key);
                $dist->appendChild($node_string);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return;
        }
        $xml = $doc->saveXML();
        file_put_contents($file, $xml);
    }

    public function deleteLogo($web_id) {
        $path = $this->getDirLogoImage($web_id);
        try {
            unlink($path);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getDirPEMfile() {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'pem' . DS . 'ios' . DS . 'push.pem';
    }

    public function getDirPEMPassfile() {
        return Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'pem' . DS . 'ios' . DS . 'pass_pem.config';
    }

    public function getPEMfile() {
        return Mage::getBaseUrl('media') . 'simi/simicart/pem/ios/push.pem';
    }

    public function savePem($pem) {
        $path = $this->getDirPEMfile();
        if (file_exists($path)) {
            $this->deletePem($path);
        }
        if (isset($pem) && $pem) {
            try {
                $uploader = new Varien_File_Uploader('pem_file');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                // We set media as the upload dir
                $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simicart' . DS . 'pem' . DS . 'ios';
                $uploader->save($path, 'push.pem');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function deletePem($path) {
        try {
            unlink($path);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getConfigNotice($cofig, $device_id) {
        $name_device = $this->getNameDeviceById($device_id);
        $path = 'simiconnector/siminotification/' . $name_device . '/' . $cofig;
        return (string) Mage::getStoreConfig($path);
    }

    public function getConfigSettings($config) {
        return (string) Mage::getStoreConfig('simiconnector/general/' . $config);
    }

    public function saveDataApp() {
        $check = false;
        $websites = $this->getWebsites();
        foreach ($websites as $website) {
            $check = true;
            $data = $this->getDataDesgin();
            foreach ($data as $item) {
                $model = Mage::getModel('connector/app');
                $model->setData($item);
                $model->setWebsiteId($website->getId());
                $model->save();
            }
        }

        if (!$check) {
            $data = $this->getDataDesgin();
            foreach ($data as $item) {
                $model = Mage::getModel('connector/app');
                $model->setData($item);
                $model->save();
            }
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

    public function isZero($value) {
        if ($value == 0) {
            return true;
        }
        return false;
    }
}
