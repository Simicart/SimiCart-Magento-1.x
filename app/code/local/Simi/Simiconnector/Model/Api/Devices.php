<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Devices extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'device_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/device')->load($data['resourceid']);
        } else {
            $this->builderQuery = Mage::getModel('simiconnector/device')->getCollection();
        }
    }

    public function store() {
        $data = $this->getData();
        $device = Mage::getModel('simiconnector/device');
        $device->saveDevice($data);
        $this->builderQuery = $device;
        return $this->show();
    }

}
