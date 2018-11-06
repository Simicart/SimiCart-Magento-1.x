<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homeproductlists extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'sort_order';
    public $SHOW_PRODUCT_ARRAY = TRUE;

    public function setBuilderQuery() 
    {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/productlist')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() 
    {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $listCollection = Mage::getModel('simiconnector/productlist')->getCollection()->addFieldToFilter('list_status', '1');
        $listCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        return $listCollection;
    }

    public function show() 
    {
        $result = parent::show();
        $result['homeproductlist'] = $this->_addInfo($result['homeproductlist']);
        return $result;
    }

    public function index() 
    {
        $result = parent::index();
        foreach ($result['homeproductlists'] as $index => $item) {
            $result['homeproductlists'][$index] = $this->_addInfo($item);
        }

        return $result;
    }

    private function _addInfo($dataArray) 
    {
        $listModel = Mage::getModel('simiconnector/productlist')->load($dataArray['productlist_id']);
        $typeArray = Mage::helper('simiconnector/productlist')->getListTypeId();
        $dataArray['type_name'] = $typeArray[$listModel->getData('list_type')];
        if ($this->SHOW_PRODUCT_ARRAY) {
            $productCollection = Mage::helper('simiconnector/productlist')->getProductCollection($listModel);
            $productListAPIModel = Mage::getModel('simiconnector/api_products');
            $productListAPIModelData = $this->getData();
            unset($productListAPIModelData['resourceid']);
            $productListAPIModel->setData($productListAPIModelData);
            $productListAPIModel->setFilterByHomeList();
            $productListAPIModel->FILTER_RESULT = false;
            $productListAPIModel->builderQuery = $productCollection;
            $productListAPIModel->pluralKey = 'products';
            $listAPI = $productListAPIModel->index();
            $dataArray['product_array'] = $listAPI;
        }

        return $dataArray;
    }

}
