<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homes extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';
    protected $showProductList = false;

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/banner')->load('-1');
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
        /*
         * Get Banners
         */
        $banners = Mage::getModel('simiconnector/api_homebanners');
        $banners->builderQuery = $banners->getCollection();
        $banners->setPluralKey('homebanners');
        $banners = $banners->index();

        /*
         * Get Categories
         */
        $categories = Mage::getModel('simiconnector/api_homecategories');
        $categories->builderQuery = $categories->getCollection();
        $categories->setPluralKey('homecategories');
        $categories = $categories->index();

        /*
         * Get Product List
         */
        $productlists = Mage::getModel('simiconnector/api_homeproductlists');
        $productlists->builderQuery = $productlists->getCollection();
        $productlists->setPluralKey('homeproductlists');
        $productlists->setData($this->getData());
        $productlists = $productlists->index();


        $information['home'] = array(
            'homebanners' => $banners,
            'homecategories' => $categories,
            'homeproductlists' => $productlists,
        );
        return $information;
    }

}
