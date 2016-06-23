<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homes extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        
    }

    public function index() {
        return $this->show();
    }

    public function show() {
        $data = $this->getData();
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
        $categories->setData($this->getData());
        $categories->builderQuery = $categories->getCollection();
        $categories->setPluralKey('homecategories');
        $categories = $categories->index();

        /*
         * Get Product List
         */
        $productlists = Mage::getModel('simiconnector/api_homeproductlists');
        $productlists->builderQuery = $productlists->getCollection();
        if ($data['resourceid'] == 'lite') {
            $productlists->SHOW_PRODUCT_ARRAY = FALSE;
        }
        $productlists->setPluralKey('homeproductlists');
        $productlists->setData($this->getData());
        $productlists = $productlists->index();


        $information = array('home' => array(
                'homebanners' => $banners,
                'homecategories' => $categories,
                'homeproductlists' => $productlists,
        ));
        return $information;
    }

}
