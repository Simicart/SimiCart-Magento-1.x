<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homebanners extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() 
    {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/banner')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() 
    {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('banner');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $bannerCollection = Mage::getModel('simiconnector/banner')->getCollection()->addFieldToFilter('status', '1');
        $bannerCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.banner_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());

        return $bannerCollection;
    }

    public function index() 
    {

        

        $result = parent::index();
        foreach ($result['homebanners'] as $index => $item) {
            if (!$item['banner_name_tablet'])
                $item['banner_name_tablet'] = $item['banner_name'];

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
