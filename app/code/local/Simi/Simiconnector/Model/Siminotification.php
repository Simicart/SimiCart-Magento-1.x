<?php

/**
 * 

 */
class Simi_Simiconnector_Model_Siminotification extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/siminotification');
    }

    public function toOptionArray() {
        $platform = array(
            '1' => Mage::helper('simiconnector')->__('Product In-app'),
            '2' => Mage::helper('simiconnector')->__('Category In-app'),
            '3' => Mage::helper('simiconnector')->__('Website Page'),
        );
        return $platform;
    }

}
