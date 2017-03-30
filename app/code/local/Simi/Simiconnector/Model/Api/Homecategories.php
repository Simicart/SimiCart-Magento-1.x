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
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('homecategory');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $simicategoryCollection = Mage::getModel('simiconnector/simicategory')->getCollection()->addFieldToFilter('status', '1');
        $simicategoryCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.simicategory_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        return $simicategoryCollection;
    }

    public function index() {
        $result = parent::index();
        $data = $this->getData();

        foreach ($result['homecategories'] as $index => $item) {
            $imageBaseDir = explode('/simi/', $item['simicategory_filename']);
            $imagesize = @getimagesize(Mage::getBaseDir('media') . '/simi/' . $imageBaseDir[1]);
            $item['width'] = $imagesize[0];
            $item['height'] = $imagesize[1];
            if (!$item['simicategory_filename_tablet'])
                $item['simicategory_filename_tablet'] = $item['simicategory_filename'];
            if ($item['simicategory_filename_tablet']) {
                $imageBaseDir = explode('/simi/', $item['simicategory_filename_tablet']);
                $imagesize = @getimagesize(Mage::getBaseDir('media') . '/simi/' . $imageBaseDir[1]);
                $item['width_tablet'] = $imagesize[0];
                $item['height_tablet'] = $imagesize[1];
            }
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
        $category = Mage::getModel('catalog/category')->load($catId);
        if (is_array($category->getChildrenCategories())) {
            $childArray = $category->getChildrenCategories();
            $idArray = array();
            foreach ($childArray as $childArrayItem) {
                $idArray[] = $childArrayItem->getId();
            }
            return Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id', array('in' => $idArray));
        }
        return $category->getChildrenCategories()->addAttributeToSelect('*');
    }

}
