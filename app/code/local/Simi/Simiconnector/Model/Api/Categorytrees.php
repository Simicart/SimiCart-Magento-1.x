<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/19/2016
 * Time: 4:47 PM
 */
class Simi_Simiconnector_Model_Api_Categorytrees extends Simi_Simiconnector_Model_Api_Abstract
{
    protected $_DEFAULT_ORDER = 'position';
    public $visible_array;
    public $_result = array();
    public $_rootlevel = 0;

    public function setBuilderQuery()
    {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $data['resourceid'] = Mage::app()->getStore()->getRootCategoryId();
        }
        if (Mage::getStoreConfig('simiconnector/general/categories_in_app')) {
            $this->visible_array = explode(',', Mage::getStoreConfig('simiconnector/general/categories_in_app'));
        }
        $category = Mage::getModel('catalog/category')->load($data['resourceid']);
        $this->_result = array();
        $this->_rootlevel = $category->getData('level');
        $this->getChildCatArray($category->getData('level'), $this->_result, $category->getData('entity_id'));
    }

    public function index()
    {
        return ['categorytrees'=>$this->_result];
    }

    public function show()
    {
        return $this->index();
    }

    public $categoryArray;
    public function getChildCatArray($level = 0, &$optionArray = [], $parent_id = 0)
    {
        if (!$this->categoryArray) {
            if ($this->visible_array) {
                $this->categoryArray = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addFieldToFilter('entity_id', ['in' => $this->visible_array])
                    ->addAttributeToSelect('*')
                    ->setOrder('position', 'asc')
                    ->getData();
            } else {
                $this->categoryArray = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->setOrder('position', 'asc')
                    ->getData();
            }
        }
        $beforeString = '';
        for ($i=0; $i< $level; $i++) {
            $beforeString .= '  --  ';
        }
        $level+=1;
        foreach ($this->categoryArray as $category) {
            if (($category['level'] != $level)|| (($this->_rootlevel + 4) <= $category['level'])) {
                continue;
            }
            if (($parent_id == 0) || (($parent_id!=0) && ($category['parent_id'] == $parent_id))) {
                $categoryModel = Mage::getModel('catalog/category')->load($category['entity_id']);
                $category = array_merge($category, $categoryModel->getData());
                if ($image_url = $categoryModel->getImageUrl()) {
                    $category['image_url'] = $image_url;
                }
                if ($image = $categoryModel->getThumbnail()) {
                    $category['thumbnail_url'] = Mage::getBaseUrl('media').'catalog/category/'.$image;
                }

                $category['name'] = $categoryModel->getData('name');
                $this->getChildCatArray($level, $category['child_cats'], $category['entity_id']);
                $optionArray[] = $category;
            }
        }
        return $optionArray;
    }
}
