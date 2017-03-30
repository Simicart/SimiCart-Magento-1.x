<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Homeproductlists extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';
    public $SHOW_PRODUCT_ARRAY = TRUE;

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/productlist')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->getCollection();
        }
    }

    public function getCollection() {
        $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('productlist');
        $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
        $listCollection = Mage::getModel('simiconnector/productlist')->getCollection()->addFieldToFilter('list_status','1');
        $listCollection->getSelect()
                ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.productlist_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
        return $listCollection;
    }

    public function show() {
        $result = parent::show();
        $result['homeproductlist'] = $this->_addInfo($result['homeproductlist']);
        return $result;
    }

    public function index() {
        $result = parent::index();
        foreach ($result['homeproductlists'] as $index => $item) {
            $result['homeproductlists'][$index] = $this->_addInfo($item);
        }
        return $result;
    }

    private function _addInfo($dataArray) {
        $listModel = Mage::getModel('simiconnector/productlist')->load($dataArray['productlist_id']);
        $imageBaseDir = explode('/simi/', $listModel->getData('list_image'));
        $imagesize = @getimagesize(Mage::getBaseDir('media').'/simi/'.$imageBaseDir[1]);
        $dataArray['width'] = $imagesize[0];
        $dataArray['height'] = $imagesize[1];
        
        if (!$dataArray['list_image_tablet'])
                $dataArray['list_image_tablet'] = $dataArray['list_image'];
        
        if ($dataArray['list_image_tablet']) {
            $imageBaseDir = explode('/simi/', $dataArray['list_image_tablet']);
            $imagesize = @getimagesize(Mage::getBaseDir('media').'/simi/'.$imageBaseDir[1]);
            $dataArray['width_tablet'] = $imagesize[0];
            $dataArray['height_tablet'] = $imagesize[1];
        }
        $typeArray = Mage::helper('simiconnector/productlist')->getListTypeId();
        $dataArray['type_name'] = $typeArray[$listModel->getData('list_type')];
        if ($this->SHOW_PRODUCT_ARRAY) {
            $productCollection = Mage::helper('simiconnector/productlist')->getProductCollection($listModel);
            $productListAPIModel = Mage::getModel('simiconnector/api_products');
            $productListAPIModelData = $this->getData();
            unset($productListAPIModelData['resourceid']);
            $productListAPIModel->setData($productListAPIModelData);
            $productListAPIModel->setBuilderQuery();			
            $productListAPIModel->FILTER_RESULT = false;
            $productListAPIModel->builderQuery = $productCollection;
            $productListAPIModel->pluralKey = 'products';
            $listAPI = $productListAPIModel->index();
            $dataArray['product_array'] = $listAPI;
        }
        return $dataArray;
    }

}
