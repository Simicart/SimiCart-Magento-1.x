<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homes extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';
    protected $_SHOW_PRODUCT_LIST = false;

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/banner')->load('-1');
            if ($data['resourceid'] == 'full') {
                $this->_SHOW_PRODUCT_LIST = true;
            }
        } else {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
            $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
            $bannerCollection = Mage::getModel('simiconnector/banner')->getCollection();
            $bannerCollection->getSelect()
                    ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.banner_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
            $this->builderQuery = $bannerCollection;
        }
    }

    public function show() {
        $information = parent::show();
        $this->builderQuery = Mage::getModel('simiconnector/api_homebanners')->getCollection();
        $this->setPluralKey('homebanners');
        $banners = parent::index();
        $this->builderQuery = Mage::getModel('simiconnector/api_homecategories')->getCollection();
        $this->setPluralKey('homecategories');
        $categories = parent::index();
        $this->builderQuery = Mage::getModel('simiconnector/api_homeproductlists')->getCollection();
        $this->setPluralKey('homeproductlists');
        $productlists = parent::index();
        if ($this->_SHOW_PRODUCT_LIST) {
            foreach ($productlists['homeproductlists'] as $key => $listItem) {
                $productlist = Mage::getModel('simiconnector/api_homeproductlists');
                $productlist->setData($this->getData());
                $productlist->builderQuery = Mage::getModel('simiconnector/productlist')->load($listItem['productlist_id']);
                $productlists['homeproductlists'][$key]['list'] = $productlist->show();
            }
        }
        $information['home'] = array(
            'homebanners' => $banners,
            'homecategories' => $categories,
            'homeproductlists' => $productlists,
        );
        return $information;
    }

}
