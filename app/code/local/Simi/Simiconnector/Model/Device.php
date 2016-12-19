<?php

/**

 */
class Simi_Simiconnector_Model_Device extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/device');
    }

    public function detectMobile() {
        $user_agent = '';
        if ($_SERVER["HTTP_USER_AGENT"]) {
            $user_agent = $_SERVER["HTTP_USER_AGENT"];
        }
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

    public function saveDevice($data) {

        $deviceData = $data['contents'];
        if (!$deviceData->device_token)
            throw new Exception(Mage::helper('simiconnector')->__('No Device Token Sent'), 4);
        $device_id = $deviceData->plaform_id;
        if (!$device_id)
            $device_id = $this->detectMobile();
        $latitude = $deviceData->latitude;
        $longitude = $deviceData->longitude;
        $addresses = Mage::helper('simiconnector/address')->getLocationInfo($latitude, $longitude);
        if ($addresses) {
            $this->setData('address', $addresses['address']);
            $this->setData('city', $addresses['city']);
            $this->setData('state', $addresses['state']);
            $this->setData('country', $addresses['country']);
            $this->setData('zipcode', $addresses['zipcode']);
        }
        $this->setData('device_token', $deviceData->device_token);
        $this->setData('plaform_id', $device_id);
        $this->setData('storeview_id', Mage::app()->getStore()->getStoreId());
        $this->setData('latitude', $deviceData->latitude);
        $this->setData('longitude', $deviceData->longitude);
        $this->setData('created_time', now());
        $this->setData('user_email', $deviceData->user_email);
        $this->setData('app_id', $deviceData->app_id);
        $this->setData('device_ip', $_SERVER['REMOTE_ADDR']);
        $this->setData('device_user_agent', $_SERVER['HTTP_USER_AGENT']);
        $this->setData('build_version', $deviceData->build_version);
        if (is_null($deviceData->is_demo)) {
            $this->setData('is_demo', 3);
        } else
            $this->setData('is_demo', $deviceData->is_demo);
        $existed_device = $this->getCollection()->addFieldToFilter('device_token', $deviceData->device_token)->getFirstItem();
        if ($existed_device->getId()) {
            $this->setId($existed_device->getId());
        }
        $this->save();
    }

}
