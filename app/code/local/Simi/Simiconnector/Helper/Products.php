<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/28/16
 * Time: 4:40 PM
 */
class Simi_Simiconnector_Helper_Products extends Mage_Core_Helper_Abstract
{

    protected $_layer = array();
    protected $builderQuery;
    protected $_data = array();
    protected $_sortOrders = array();

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function getLayers()
    {
        return $this->_layer;
    }

    /**
     * @return product collection.
     *
     */
    public function getBuilderQuery()
    {
        return $this->builderQuery;
    }

    public function getProduct($product_id)
    {
        $this->builderQuery = Mage::getModel('catalog/product')->load($product_id);
        if(!$this->builderQuery->getId()) throw new Exception($this ->__('Resource cannot callable.'), 6);
        return $this->builderQuery;
    }

    /**
     *
     */
    public function setCategoryProducts($category)
    {
        $category = Mage::getModel('catalog/category')->load($category);
        $this->setLayers(0, $category);
        return $this;
    }

    /**
     * Related Product
     * @param int $product_id
     */
    public function setRelatedProduct($product_id)
    {
        $product = Mage::getModel('catalog/product')->load($product_id);
        $this->builderQuery = $product->getRelatedProductCollection();
        $this->setAttributeProducts();
        return $this;
    }

    /**
     * @param int $is_search
     * @param int $category
     * set Layer and collection on Products
     */
    public function setLayers($is_search = 0, $category = 0)
    {
        $data = $this->getData();
        try {
            Mage::register('current_category', $category);
        }
        catch (Exception $e) {
        }
        $controller = $data['controller'];
        $parameters = $data['params'];
        if (isset($parameters[Simi_Simiconnector_Model_Api_Abstract::FILTER])) {
            $filter = $parameters[Simi_Simiconnector_Model_Api_Abstract::FILTER];
            if ($is_search == 1) {
                $controller->getRequest()->setParam('q', (string)$filter['q']);
            }
            if(isset($filter['q']) && $filter['q'] != ''){
                $controller->getRequest()->setParam('q', (string)$filter['q']);
                $is_search = 1;
            }
            if($category && ($category->getId() || ($category!=0))){
                $controller->getRequest()->setParam('cat', (string)$category->getId());
            }
            if (isset($filter['layer'])) {
                $filter_layer = $filter['layer'];
                $params = array();
                foreach ($filter_layer as $key => $value) {
                    $params[(string)$key] = (string)$value;
//                    if($key == "cat"){
//                        $category = Mage::getModel('catalog/category')->load($value);
//                    }
                }
                $controller->getRequest()->setParams($params);
            }
        }
        $layout = $controller->getLayout();
        if ($is_search == 0) {
            $block = $layout->createBlock('catalog/layer_view');
            //setCurrentCate
            //$block->getLayer()->setCurrentCategory($category);
            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);
            //if (!$settings->getPageLayout()) {
            if ($block->canShowBlock() && $category->getData('is_anchor')) {
                    $layers = $this->getItemsShopBy($block);
                    $this->_layer = $layers;
            }
            //}

            //update collection
            $block_list = $layout->createBlock('catalog/product_list');
            $block_toolbar = $layout->createBlock('catalog/product_list_toolbar');
            $block_list->setChild('product_list_toolbar', $block_toolbar);

            $this->builderQuery = $block_list->getLoadedProductCollection();
            $this->setAttributeProducts();
            $this->setStoreOrders($block_list, $block_toolbar);

        } else {
            $query = Mage::helper('catalogsearch')->getQuery();
            $query->setStoreId(Mage::app()->getStore()->getId());
            if ($query->getQueryText() != '') {
                if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->setId(0)
                        ->setIsActive(1)
                        ->setIsProcessed(1);
                } else {
                    if ($query->getId()) {
                        $query->setPopularity($query->getPopularity() + 1);
                    } else {
                        $query->setPopularity(1);
                    }

                    if ($query->getRedirect()) {
                        $query->save();
                    } else {
                        $query->prepare();
                    }
                }

                Mage::helper('catalogsearch')->checkNotes();

                $block = $layout->createBlock('catalogsearch/layer');
                if ($block->canShowBlock()) {
                    $layers = $this->getItemsShopBy($block);
                    $this->_layer = $layers;
                }
                //update collection
                // $block_result = $layout->createBlock('catalogsearch/result');
                $block_list = $layout->createBlock('catalog/product_list');
                $block_toolbar = $layout->createBlock('catalog/product_list_toolbar');

                $block_list->setChild('product_list_toolbar', $block_toolbar);

                $this->builderQuery = $block_list->getLoadedProductCollection();
                $this->setAttributeProducts(1);
                $this->setStoreOrders($block_list, $block_toolbar, 1);

                if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                    $query->save();
                }
            }
        }
    }

    public function setStoreOrders($block_list, $block_toolbar, $is_search = 0)
    {
        if (!$block_toolbar->isExpanded()) return;
        $sort_orders = array();

        if ($sort = $block_list->getSortBy()) {
            $block_toolbar->setDefaultOrder($sort);
        }
        if ($dir = $block_list->getDefaultDirection()) {
            $block_toolbar->setDefaultDirection($dir);
        }
        $availableOrders = $block_toolbar->getAvailableOrders();
        if ($is_search == 1) {
            unset($availableOrders['position']);
            $availableOrders = array_merge(array(
                'relevance' => $this->__('Relevance')
            ), $availableOrders);

            $block_toolbar->setAvailableOrders($availableOrders)
                ->setDefaultDirection('desc')
                ->setSortBy('relevance');
        }

        foreach ($availableOrders as $_key => $_order) {
            if ($block_toolbar->isOrderCurrent($_key)) {
                if ($block_toolbar->getCurrentDirection() == 'desc') {
                    $sort_orders[] = array(
                        'key' => $_key,
                        'value' => $_order,
                        'direction' => 'asc',
                        'default' => '0'
                    );

                    $sort_orders[] = array(
                        'key' => $_key,
                        'value' => $_order,
                        'direction' => 'desc',
                        'default' => '1'
                    );
                    $this->builderQuery->setOrder($_key, 'desc');
                } else {
                    $sort_orders[] = array(
                        'key' => $_key,
                        'value' => $_order,
                        'direction' => 'asc',
                        'default' => '1'
                    );
                    $sort_orders[] = array(
                        'key' => $_key,
                        'value' => $_order,
                        'direction' => 'desc',
                        'default' => '0'
                    );
                    $this->builderQuery->setOrder($_key, 'asc');
                }
            } else {
                $sort_orders[] = array(
                    'key' => $_key,
                    'value' => $_order,
                    'direction' => 'asc',
                    'default' => '0'
                );

                $sort_orders[] = array(
                    'key' => $_key,
                    'value' => $_order,
                    'direction' => 'desc',
                    'default' => '0'
                );
            }
        }
        $this->_sortOrders = $sort_orders;
    }

    public function getStoreQrders()
    {
        return $this->_sortOrders;
    }

    protected function setAttributeProducts($is_search = 0)
    {
        $storeId = Mage::app()->getStore()->getId();
        $this->builderQuery->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        $this->builderQuery->setStoreId($storeId);
        $this->builderQuery->addFinalPrice();
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->builderQuery);
        if ($is_search == 0) {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->builderQuery);
        } else {
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->builderQuery);
        }
        $this->builderQuery->addUrlRewrite(0);
    }

    protected function getItemsShopBy($block)
    {
        $_children = $block->getChild();
        $refineArray = array();
        foreach ($_children as $index => $_child) {
            if ($index == 'layer_state') {
                // $itemArray = array();
                foreach ($_child->getActiveFilters() as $item) {
                    $itemValues = array();
                    $itemValues = $item->getValue();
                    if (is_array($itemValues)) {
                        $itemValues = implode('-', $itemValues);
                    }

                    if ($item->getFilter() && ($item->getFilter()->getRequestVar() != null)) {
                        $refineArray['layer_state'][] = array(
                            'attribute' => $item->getFilter()->getRequestVar(),
                            'title' => $item->getName(),
                            'label' => (string)strip_tags($item->getLabel()), //filter request var and correlative name
                            'value' => $itemValues,
                        ); //value of each option
                    }
                }
                // $refineArray[] = $itemArray;
            } else {
                $items = $_child->getItems();
                $itemArray = array();
                foreach ($items as $index => $item) {
                    $filter = array();
                    if ($index == 0) {
                        foreach ($items as $index => $item) {
                            $filter[] = array(
                                'value' => $item->getValue(), //value of each option
                                'label' => strip_tags($item->getLabel()),
                            );
                        }

                        if ($item->getFilter() && ($item->getFilter()->getRequestVar() != null)) {
                            $refineArray['layer_filter'][] = array(
                                'attribute' => $item->getFilter()->getRequestVar(),
                                'title' => $item->getName(), //filter request var and correlative name
                                'filter' => $filter,
                            );
                        }
                    }
                }
            }
        }
        return $refineArray;
    }

    public function getImageProduct($product, $file = null, $width, $height)
    {
        if (!is_null($width) && !is_null($height)) {
            if ($file) {
                return Mage::helper('catalog/image')->init($product, 'thumbnail', $file)->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize($width, null)->__toString();
            }
            return Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize($width, null)->__toString();
        }
        if ($file) {
            return Mage::helper('catalog/image')->init($product, 'thumbnail', $file)->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(600, 600)->__toString();
        }
        return Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(600, 600)->__toString();
    }

}
