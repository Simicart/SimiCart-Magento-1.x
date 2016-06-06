<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homecategories extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setSingularKey($singularKey) {
        $this->singularKey = 'Homecategory';
        return $this;
    }

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $simicategoryCollection = Mage::getModel('simiconnector/simicategory')->getCollection();
        $simicategoryCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.simicategory_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        return $simicategoryCollection;
    }

    public function index() {
        $result = parent::index();
        foreach ($result['homecategories'] as $index => $item) {
            $imagesize = getimagesize($item['simicategory_filename']);
            $item['width'] = $imagesize[0];
            $item['height'] = $imagesize[1];
            $categoryModel = Mage::getModel('catalog/category')->load($item['category_id']);
            $item['has_children'] = $categoryModel->hasChildren();
            $item['cat_name'] = $categoryModel->getName();
            $result['homecategories'][$index] = $item;
        }
        return $result;
    }

}
