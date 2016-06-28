<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homebanners extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/banner')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $bannerCollection = Mage::getModel('simiconnector/banner')->getCollection();
        $bannerCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.banner_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());

        return $bannerCollection;
    }

    public function index() {
        $result = parent::index();
        foreach ($result['homebanners'] as $index => $item) {
            $imagesize = getimagesize($item['banner_name']);
            $item['width'] = $imagesize[0];
            $item['height'] = $imagesize[1];
            if ($item['banner_name_tablet']) {
                $imagesize = getimagesize($item['banner_name_tablet']);
                $item['width_tablet'] = $imagesize[0];
                $item['height_tablet'] = $imagesize[1];
            }
            if ($item['type'] == 2) {
                $categoryModel = Mage::getModel('catalog/category')->load($item['category_id']);
                $item['has_children'] = $categoryModel->hasChildren();
                $item['cat_name'] = $categoryModel->getName();
            }
            $result['homebanners'][$index] = $item;
        }
        return $result;
    }

}
