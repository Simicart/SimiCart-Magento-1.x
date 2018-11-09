<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homes extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() 
    {
        
    }

    public function index() 
    {
        return $this->show();
    }

    public function show() 
    {
        $data = $this->getData();
        $storeId = Mage::app()->getStore()->getId();
        //get cache
        if (isset($data['resourceid']) && ($data['resourceid']=='lite')) {
            if(isset($data['params']['get_child_cat']) && $data['params']['get_child_cat'] == '1') {
                $filePath = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache" . DS . $storeId . DS . "home_child_cat_cached.json";
            } else {
                $filePath = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache" . DS . $storeId . DS . "home_cached.json";
            }
            if (file_exists($filePath)) {
                $homeJson = file_get_contents($filePath);
                if ($homeJson) {
                    return array('home' => json_decode($homeJson, true));
                }
            }
        }
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
        //save cache
        if (isset($data['resourceid']) && ($data['resourceid']=='lite')) {
            $path = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache" . DS . $storeId;
            if (!is_dir($path)) {
                try {
                    mkdir($path, 0777, TRUE);
                } catch (Exception $e) {

                }
            }
            if(isset($data['params']['get_child_cat']) && $data['params']['get_child_cat'] == '1') {
                $filePath = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache" . DS . $storeId . DS . "home_child_cat_cached.json";
            } else {
                $filePath = Mage::getBaseDir('media') . DS . 'simi' . DS . 'simiconnector' . DS . "cache" . DS . $storeId . DS . "home_cached.json";
            }

            if (!file_exists($filePath)) {
                $file = @fopen($filePath, 'w+');
                $data_json = json_encode($information['home']);
                file_put_contents($filePath, $data_json);
            }
        }
        return $information;
    }

}
