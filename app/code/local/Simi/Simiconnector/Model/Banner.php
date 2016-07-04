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
class Simi_Simiconnector_Model_Banner extends Mage_Core_Model_Abstract {

    protected $_website_id = null;

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/banner');
    }

    public function getBannerList() {
        $website_id = Mage::app()->getStore()->getWebsiteId();
        $list = array();
        $collection = $this->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('website_id', array('in' => array($website_id, 0)));

        foreach ($collection as $item) {
            $path = Mage::getBaseUrl('media') . 'simi/simiconnector/banner' . '/' . $item->getWebsiteId() . '/' . $item->getBannerName();
            $categoryName = '';
            $categoryChildrenCount = '';
            if ($item->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($item->getCategoryId());
                $categoryName = $category->getName();
                $categoryChildrenCount = $category->getChildrenCount();
                if ($categoryChildrenCount > 0)
                    $categoryChildrenCount = 1;
                else
                    $categoryChildrenCount = 0;
            }
            $list[] = array(
                'image_path' => $path,
                'url' => $item->getBannerUrl(),
                'type' => $item->getType(),
                'categoryID' => $item->getCategoryId(),
                'categoryName' => $categoryName,
                'productID' => $item->getProductId(),
                'has_child' => $categoryChildrenCount,
            );
        }
        return $list;
    }

    public function toOptionArray() {
        $platform = array(
            '1' => Mage::helper('simiconnector')->__('Product In-app'),
            '2' => Mage::helper('simiconnector')->__('Category In-app'),
            '3' => Mage::helper('simiconnector')->__('Website Page'),
        );
        return $platform;
    }

    public function delete() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
        $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                ->addFieldToFilter('content_type', $typeID)
                ->addFieldToFilter('item_id', $this->getId());
        foreach ($visibleStoreViews as $visibilityItem)
            $visibilityItem->delete();
        return parent::delete();
    }

}
