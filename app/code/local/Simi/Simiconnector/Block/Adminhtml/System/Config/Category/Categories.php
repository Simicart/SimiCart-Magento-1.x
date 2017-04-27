<?php

class Simi_Simiconnector_Block_Adminhtml_System_Config_Category_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories {

    protected $_categoryIds;
    protected $_selectedNodes = null;

    public function __construct() {
        parent::__construct();
        $this->_withProductCount = false;
        $this->setTemplate('simiconnector/categories.phtml');
    }

    public function getCategoryCollection() {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->addAttributeToSelect('name')
                    ->addAttributeToSelect('is_active')
                    ->setProductStoreId($storeId)
                    ->setLoadProductCount($this->_withProductCount)
                    ->setStoreId($storeId);

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly() {
        return false; //$this->getProduct()->getCategoriesReadonly();
    }

    protected function getCategoryIds() {
        return explode(',', $this->getIdsString()); //$this->getProduct()->getCategoryIds();
    }

    public function getIdsString() {
        if ($storecode = Mage::app()->getRequest()->getParam('store')) {
                $storeviewModel = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code', $storecode)->getFirstItem();
                return Mage::getStoreConfig("simiconnector/general/categories_in_app", $storeviewModel->getId());
        }
        return Mage::getStoreConfig("simiconnector/general/categories_in_app"); //Mage::registry('simiconnector_categories');//Mage::registry('bannerslider_data')->getCategories();//implode(',', $this->getCategoryIds());
    }

    public function getLoadTreeUrl($expanded = null) {
        $params = array('_current' => true, 'id' => null, 'store' => null);
        if ((is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded()) || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('adminhtml/simiconnector_config/categoriesJson', $params);
    }

}
