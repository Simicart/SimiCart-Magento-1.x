<?php

/**

 */
class Simi_Simiconnector_Model_Mysql4_Device extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('simiconnector/device', 'device_id');
    }

}
