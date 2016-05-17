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
 * Connector Model
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Model_Cms extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/cms');
    }

    public function delete() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
        $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                ->addFieldToFilter('content_type', $typeID)
                ->addFieldToFilter('item_id', $this->getId());
        foreach ($visibleStoreViews as $visibilityItem)
            $visibilityItem->delete();
        return parent::delete();
    }

}
