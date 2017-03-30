<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Stores extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'group_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('core/store_group')->load($data['resourceid']);
        } else {
            $this->builderQuery = $collection = Mage::getModel('core/store_group')->getCollection()->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());
        }
    }

    public function index() {
        $result = parent::index();
        foreach ($result['stores'] as $index => $store) {
            $storeViewAPIModel = Mage::getModel('simiconnector/api_storeviews');
            $storeViewAPIModel->setData($this->getData());
            $storeViewAPIModel->builderQuery = Mage::getModel('core/store')->getCollection()->addFieldToFilter('group_id', $store['group_id']);
            $storeViewAPIModel->pluralKey = 'storeviews';
            $result['stores'][$index]['storeviews'] = $storeViewAPIModel->index();
        }
        return $result;
    }

}
