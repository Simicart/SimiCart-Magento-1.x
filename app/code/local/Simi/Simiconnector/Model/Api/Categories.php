<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/19/2016
 * Time: 4:47 PM
 */
class Simi_Simiconnector_Model_Api_Categories extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'position';
    protected $_visible_array;

    public function setBuilderQuery()
    {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $data['resourceid'] = Mage::app()->getStore()->getRootCategoryId();
        }

        if (Mage::getStoreConfig('simiconnector/general/categories_in_app'))
            $this->_visible_array = explode(',', Mage::getStoreConfig('simiconnector/general/categories_in_app'));

        $category = Mage::getModel('catalog/category')->load($data['resourceid']);
        if (is_array($category->getChildrenCategories())) {
            $idArray =explode(',', $category->getChildren());
            if ($this->_visible_array)
                $idArray = array_intersect($idArray, $this->_visible_array);
            $this->builderQuery = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id', array('in' => $idArray));
        }else {
            $this->builderQuery = $this->_getChildrenCategories($category)->addAttributeToSelect('*');
        }
    }

    public function index()
    {
        $data = $this->getData();
        $result = parent::index();
        foreach ($result['categories'] as $index => $catData) {
            $childCollection = Mage::getModel('catalog/category')->getCollection()
                ->addFieldToFilter('parent_id', $catData['entity_id'])
                ->addAttributeToFilter('is_active', 1);

            if (isset($data['params']['get_child_cat']) && $data['params']['get_child_cat']) {
                $childCollection->addAttributeToSelect('*');
                $cateModel = Mage::getModel('catalog/category')->load($catData['entity_id']);
                $cate_image_url = $cateModel->getImageUrl();
                if(!$cate_image_url){
                    $cate_image_url = Mage::getBaseUrl('media').'catalog/category/'.$cateModel->getThumbnail();
                }
                $result['categories'][$index]['thumbnail'] = $cate_image_url;
            }

            if ($this->_visible_array)
                $childCollection->addFieldToFilter('entity_id', array('in' => $this->_visible_array));
            if ($childCollection->count() > 0)
            {
                $result['categories'][$index]['has_children'] = TRUE;
                if (isset($data['params']['get_child_cat']) && $data['params']['get_child_cat']) {

                    $get_child_cat_level = $data['params']['get_child_cat'];

                    $childArray = array();
                    foreach ($childCollection as $childCat) {
                        $childInfo = $childCat->toArray();
                        $grandchildCollection = $this->getVisibleChildren($childCat->getId());
                        if ($grandchildCollection->count() > 0){

                            if($get_child_cat_level == 2){
                                $grandChildInfo = array();
                                foreach ($grandchildCollection as $grandChildCat){
                                    $grandChildInfo[] = $grandChildCat->toArray();
                                }
                                $childInfo['children'] = $grandChildInfo;
                            }

                            $childInfo['has_children'] = TRUE;
                        }
                        else{
                            $childInfo['has_children'] = FALSE;
                        }

                        $childArray[] = $childInfo;
                    }

                    $result['categories'][$index]['children'] = $childArray;

                }

            }
            else
            {
                $result['categories'][$index]['has_children'] = FALSE;
            }

        }

        return $result;
    }

    public function getVisibleChildren($catId)
    {
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

    public function show()
    {
        return $this->index();
    }

    public function _getChildrenCategories($category)
    {
        $idFilter = $category->getChildren();

        if($this->_visible_array){
            $ids = explode(',', $idFilter);
            $idFilter = array_intersect($ids, $this->_visible_array);
            $idFilter = implode(',', $idFilter);
        }

        $collection = $this->_getChildrenCategoriesBase($category);
        $collection->addAttributeToFilter('is_active', 1)
            ->addIdFilter($idFilter)
            ->load();

        return $collection;
    }

    private function _getChildrenCategoriesBase($category)
    {
        $collection = $category->getCollection();
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->setOrder('position', Varien_Db_Select::SQL_ASC)
            ->joinUrlRewrite();

        return $collection;
    }
}
