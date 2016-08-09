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

    public function toOptionArray() {
        $platform = array(
            '1' => Mage::helper('simiconnector')->__('Left Menu'),
            '2' => Mage::helper('simiconnector')->__('Category In-app')
        );
        return $platform;
    }

    public function getCmsForCategory($catId) {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $cmsCollection = Mage::getModel('simiconnector/cms')->getCollection()->addFieldToFilter('type', '2')->setOrder('sort_order','ASC');
        $cmsCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.cms_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        foreach ($cmsCollection as $cms) {
            foreach (explode(',', str_replace(' ', '', $cms->getData('category_id'))) as $categoryId){
                if ($categoryId == $catId) 
                    return $cms->toArray();
            }
        }
    }
    public function getCategoryCMSPages(){
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $cmsCollection = Mage::getModel('simiconnector/cms')->getCollection()->addFieldToFilter('type', '2')->setOrder('sort_order','ASC');
        $cmsCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.cms_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        $cmsArray = array();
        foreach ($cmsCollection as $cms) {
            $cmsArray[] = $cms->toArray();
        }
        return $cmsArray;
    }

}
