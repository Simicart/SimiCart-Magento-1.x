<?php

class Simi_Simiconnector_Block_Adminhtml_System_Config_Category_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{

    protected $_categoryIds;
    protected $_selectedNodes = null;

    public function __construct() 
    {
        parent::__construct();
        $this->_withProductCount = false;
        $this->setTemplate('simiconnector/categories.phtml');
    }

    public function getCategoryCollection() 
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        if (!is_null($storeId) && !is_numeric($storeId)) {
            $store = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code', $storeId)->getFirstItem();
            if($store->getId()) {
                $storeId = $store->getId();
                $this->getRequest()->setParam('store', $storeId);
            }
        }

        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection->setStoreId($storeId)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount);

            $this->setData('category_collection', $collection);
        }

        return $collection;
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly() 
    {
        return false; //$this->getProduct()->getCategoriesReadonly();
    }

    protected function getCategoryIds() 
    {
        return explode(',', $this->getIdsString()); //$this->getProduct()->getCategoryIds();
    }

    public function getIdsString()
    {
        if ($storecode = Mage::app()->getRequest()->getParam('store')) {
            $storeviewModel = Mage::getModel('core/store')->load($storecode);
            if (!$storeviewModel->getId()) {
                $storeviewModel = Mage::getModel('core/store')->getCollection()->addFieldToFilter('code', $storecode)->getFirstItem();
            }

            return Mage::getStoreConfig("simiconnector/general/categories_in_app", $storeviewModel->getId());
        }

        if($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()) {
            $storecode = Mage::app()->getRequest()->getParam('store', '');
            if(!$storecode){
                return Mage::app()->getWebsite($websiteId)->getConfig('simiconnector/general/categories_in_app');
            }
        }

        return Mage::getStoreConfig("simiconnector/general/categories_in_app");
    }

    public function getLoadTreeUrl($expanded = null) 
    {
        $params = array('_current' => true, 'id' => null, 'store' => null);
        if ((is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded()) || $expanded == true) {
            $params['expand_all'] = true;
        }

        return $this->getUrl('adminhtml/simiconnector_config/categoriesJson', $params);
    }

    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }

        $root = Mage::registry('root');
        if (is_null($root)) {
            if($storeCode = $this->getRequest()->getParam('store')){
                $storeviewModel = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('code', $storeCode)
                    ->getFirstItem();
                if($storeviewModel->getId()){
                    $storeId = $storeviewModel->getId();
                }
            }elseif ($website = $this->getRequest()->getParam('website')){
                $websiteModel = Mage::getModel('core/website')->getCollection()
                    ->addFieldToFilter('code', $website)
                    ->getFirstItem();
                $storeId = $websiteModel->getDefaultStore()->getId();
            }else{
                if ($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()) {
                    $storeId = Mage::app()->getWebsite($websiteId)->getDefaultStore()->getId();
                }
            }

            if (isset($storeId) && $storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
            }

            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly()) {
                    $root->setDisabled(true);
                }
            }
            elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            Mage::register('root', $root);
        }

        return $root;
    }
}
