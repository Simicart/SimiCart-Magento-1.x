<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homeproductlists extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';
    protected $showProductList = false;

    public function setShowProductList($showProductList) {
        $this->showProductList = $showProductList;
    }
    
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
        $imagesize = getimagesize($listModel->getData('list_image'));
        $result['homeproductlist']['width'] = $imagesize[0];
        $result['homeproductlist']['height'] = $imagesize[1];
        $typeArray = Mage::helper('simiconnector/productlist')->getListTypeId();
        $result['homeproductlist']['type_name'] = $typeArray[$listModel->getData('list_type')];
        $productCollection = Mage::helper('simiconnector/productlist')->getProductCollection($listModel);
        $productListAPIModel = Mage::getModel('simiconnector/api_products');
        $productListAPIModel->setData($this->getData());
        $productListAPIModel->setBuilderQuery();
        $productListAPIModel->builderQuery = $productCollection;
        $listAPI = $productListAPIModel->index();
        $result['homeproductlist']['product_array'] = $listAPI;
        return $result;
    }

    public function index() {
        $result = parent::index();
        foreach ($result['homeproductlists'] as $index => $item) {
            $imagesize = getimagesize($item['list_image']);
            $item['width'] = $imagesize[0];
            $item['height'] = $imagesize[1];
            if ($this->showProductList) {
                $this->builderQuery = Mage::getModel('simiconnector/productlist')->load($item['productlist_id']);
                $item['product_array'] = $this->show();
            }
            $result['homeproductlists'][$index] = $item;
        }
        return $result;
    }

}
