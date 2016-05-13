<?php

/**

 */
class Simi_Simiconnector_Model_History extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/history');
    }

    public function toOptionArray() {
        return Mage::getResourceModel('customer/group_collection')->load()
                        ->toOptionArray();
    }

}
