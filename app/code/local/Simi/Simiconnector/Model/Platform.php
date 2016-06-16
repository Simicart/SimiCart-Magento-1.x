<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Siminotification
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Siminotification Model
 * 
 * @category    
 * @package     Siminotification
 * @author      Developer
 */
class Simi_Simiconnector_Model_Platform extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/platform');
    }

    public function toOptionArray() {
        $platform = array(
            Mage::helper('simiconnector')->__('All'),
            Mage::helper('simiconnector')->__('IOS'),
            Mage::helper('simiconnector')->__('Android'),
        );
        return $platform;
    }

}
