<?php

/**
 * 
Custom Product list on Home Screen
 */
class Simi_Simiconnector_Model_Productlist extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/productlist');
    }

    public function delete() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                ->addFieldToFilter('content_type', $typeID)
                ->addFieldToFilter('item_id', $this->getId());
        foreach ($visibleStoreViews as $visibilityItem)
            $visibilityItem->delete();
        return parent::delete();
    }

}
