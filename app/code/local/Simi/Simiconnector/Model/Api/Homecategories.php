<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homecategories extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';
    protected $_visible_array;

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
        $data = $this->getData();

        foreach ($result['homecategories'] as $index => $item) {
            $imagesize = getimagesize($item['simicategory_filename']);
            $item['width'] = $imagesize[0];
            $item['height'] = $imagesize[1];
            $categoryModel = Mage::getModel('catalog/category')->load($item['category_id']);
            $item['cat_name'] = $categoryModel->getName();
            $childCollection = $this->getVisibleChildren($item['category_id']);
            if ($childCollection->count() > 0) {
                $item['has_children'] = TRUE;
                if ($data['params']['get_child_cat']) {
                    $childArray = array();
                    foreach ($childCollection as $childCat) {
                        $childInfo = $childCat->toArray();
                        $grandchildCollection = $this->getVisibleChildren($childCat->getId());
                        if ($grandchildCollection->count() > 0)
                            $childInfo['has_children'] = TRUE;
                        else
                            $childInfo['has_children'] = FALSE;
                        $childArray[] = $childInfo;
                    }
                    $item['children'] = $childArray;
                }
            } else {
                $item['has_children'] = FALSE;
            }
            $result['homecategories'][$index] = $item;
        }
        return $result;
    }

    /*
     * @param Cat ID
     * Return Child Cat collection
     */

    public function getVisibleChildren($catId) {
        $childCollection = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('parent_id', $catId);
        if ($this->_visible_array)
            $childCollection->addFieldToFilter('entity_id', array('in' => $this->_visible_array));
        return $childCollection;
    }

}
