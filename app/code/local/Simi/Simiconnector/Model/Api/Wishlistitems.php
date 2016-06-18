<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Wishlistitems extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'wishlist_item_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId() && ($customer->getId() != '')) {
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
        } else
            throw new Exception(Mage::helper('customer')->__('Please login First.'), 4);
        if ($data['resourceid']) {
            //$this->builderQuery = Mage::getModel('core/store_group')->load($data['resourceid']);
        } else {
            $this->builderQuery = $wishlist->getItemCollection();
        }
    }

    /*
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
     */
}
