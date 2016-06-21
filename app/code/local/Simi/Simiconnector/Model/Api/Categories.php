<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/19/2016
 * Time: 4:47 PM
 */
class Simi_Simiconnector_Model_Api_Categories extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'position';
    protected $_visible_array;

    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $data['resourceid'] = Mage::app()->getStore()->getRootCategoryId();
        }
        if (Mage::getStoreConfig('simiconnector/general/categories_in_app'))
            $this->_visible_array = explode(',', Mage::getStoreConfig('simiconnector/general/categories_in_app'));
        $this->builderQuery = Mage::getModel('catalog/category')->getCollection()->addFieldToFilter('parent_id', $data['resourceid'])->addAttributeToSelect('*');
        if ($this->_visible_array)
            $this->builderQuery->addFieldToFilter('entity_id', array('in' => $this->_visible_array));
    }

    public function index() {
        $result = parent::index();
        foreach ($result['categories'] as $index => $catData) {
            $childCollection = Mage::getModel('catalog/category')->getCollection()->addFieldToFilter('parent_id', $catData['entity_id']);
            if ($this->_visible_array)
                $childCollection->addFieldToFilter('entity_id', array('in' => $this->_visible_array));
            if ($childCollection->count() > 0)
                $result['categories'][$index]['has_children'] = TRUE;
            else
                $result['categories'][$index]['has_children'] = FALSE;
        }
        return $result;
    }

    public function show() {
        return $this->index();
    }

}
