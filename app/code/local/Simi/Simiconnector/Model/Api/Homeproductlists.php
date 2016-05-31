<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homeproductlists extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/productlist')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $listCollection = Mage::getModel('simiconnector/productlist')->getCollection();
        $listCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());

        return $listCollection;
    }

    public function show() {
        $result = parent::show();
        $listModel = $this->builderQuery;
        $productCollection = Mage::helper('simiconnector/productlist')->getProductCollection($listModel);
        $productListAPIModel = Mage::getModel('simiconnector/api_products');
        $productListAPIModel->setData($this->getData());
        $productListAPIModel->setBuilderQuery();
        $productListAPIModel->builderQuery = $productCollection;
        $listAPI = $productListAPIModel->index();
        $typeArray = Mage::helper('simiconnector/productlist')->getListTypeId();
        $result['homeproductlist']['type_name'] = $typeArray[$listModel->getData('list_type')];
        $result['homeproductlist']['product_array'] = $listAPI;
        return $result;
    }   

}
