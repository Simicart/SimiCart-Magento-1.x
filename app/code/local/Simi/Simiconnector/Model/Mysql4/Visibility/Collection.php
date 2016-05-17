<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Connector Resource Collection
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Model_Mysql4_Visibility_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/visibility');
    }

}
