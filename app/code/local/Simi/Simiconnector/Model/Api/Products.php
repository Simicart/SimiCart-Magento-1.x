<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Products extends Simi_Simiconnector_Model_Api_Abstract
{
    protected $_layer = array();

    /**
     * override
     */
    public function setBuilderQuery()
    {
        $data = $this->getData();
        $parameters = $data['params'];
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('catalog/product')->load($data['resourceid']);
        } else {
            if(isset($parameters[self::FILTER])) {
                $filter = $parameters[self::FILTER];
                if(isset($filter['cat_id'])){
                    $this->setFilterByCategoryId($filter['cat_id']);
                }elseif(isset($filter['q'])){
                    $this->setFilterByQuery();
                }
            }else{
                //all products
                $this->setFilterByCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }
        }
    }

    /**
     * @param $info
     * @param $all_ids
     * @param $total
     * @param $page_size
     * @param $from
     * @return array
     * override
     */
    public function getList($info, $all_ids, $total, $page_size, $from)
    {
        return array(
            'all_ids' => $all_ids,
            $this->getPluralKey() => $info,
            'total' => $total,
            'page_size' => $page_size,
            'from' => $from,
            'layers' => $this->_layer,
        );
    }

    /**
     * @return collection
     * override
     */
    protected function filter()
    {
        $data = $this->_data;
        $parameters = $data['params'];
        $this->_order($parameters);
        return null;
    }

    /**
     * @return array
     * @throws Exception
     * override
     */
    public function index()
    {
        $collection = $this->builderQuery;
        $this->filter();
        $data = $this->getData();
        $parameters = $data['params'];
        $page = 1;
        if (isset($parameters[self::PAGE]) && $parameters[self::PAGE]) {
            $page = $parameters[self::PAGE];
        }

        $limit = self::DEFAULT_LIMIT;
        if (isset($parameters[self::LIMIT]) && $parameters[self::LIMIT]) {
            $limit = $parameters[self::LIMIT];
        }

        $offset = $limit * ($page - 1);
        if (isset($parameters[self::OFFSET]) && $parameters[self::OFFSET]) {
            $offset = $parameters[self::OFFSET];
        }
        $collection->setPageSize($offset + $limit);

        $all_ids = array();
        $info = array();
        $total = $collection->getSize();

        if ($offset > $total)
            throw new Exception($this->_helper->__('Invalid method.'), 4);

        $fields = array();
        if(isset($parameters['fields']) && $parameters['fields']){
            $fields = explode(',', $parameters['fields']);
        }

        $check_limit = 0;
        $check_offset = 0;

        foreach ($collection as $entity) {
            if (++$check_offset <= $offset) {
                continue;
            }
            if (++$check_limit > $limit)
                break;
            $info_detail = $entity->toArray($fields);

            $images = array();
            $images[] = array(
                'url' => $this->getImageProduct($entity, null, $parameters['image_width'], $parameters['image_height']),
                'position' => 1,
            );
            $info_detail['images'] = $images;
            $info[] = $info_detail;

            $all_ids[] = $entity->getId();
        }
        return $this->getList($info, $all_ids, $total, $limit, $offset);
    }

    /**
     * @return array
     * override
     */
    public function show()
    {
        $entity = $this->builderQuery;
        $data = $this->getData();
        $parameters = $data['params'];
        $fields = array();
        if(isset($parameters['fields']) && $parameters['fields']){
            $fields = explode(',', $parameters['fields']);
        }
        $info = $entity->toArray($fields);
        $media_gallery = $entity->getMediaGallery();
        $images = array();

        foreach ($media_gallery['images'] as $image) {
            // Zend_debug::dump($image['disabled']);
            if ($image['disabled'] == 0){
                $images[] = array(
                    'url' =>  $this->getImageProduct($entity, $image['file'], $parameters['image_width'], $parameters['image_height']),
                    'position' => $image['position'],
                );
            }
        }
        if (count($images) == 0) {
            $images[] = array(
                'url' => $this->getImageProduct($entity, null, $parameters['image_width'], $parameters['image_height']),
                'position' => 1,
            );
        }
        $info['images'] = $images;
        return $this->getDetail($info);
    }

    public function setFilterByCategoryId($cat_id){
        $category = Mage::getModel('catalog/category')->load($cat_id);
        if($category->getData('include_in_menu') == 0){
            $this->builderQuery = $category->getProductCollection();
            $this->setAttributeProducts();
        }else{
            $this->setLayers(0, $category);
        }
    }

    public function setFilterByQuery(){
        $this->setLayers(1);
    }

    public function setAttributeProducts($is_search=0){
        $storeId = Mage::app()->getStore()->getId();
        $this->builderQuery->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
        $this->builderQuery->setStoreId($storeId);
        $this->builderQuery->addFinalPrice();
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->builderQuery);
        if($is_search == 0){
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->builderQuery);
        }else{
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->builderQuery);
        }
        $this->builderQuery->addUrlRewrite(0);
    }

    public function setLayers($is_search=0, $category=0){
        $data = $this->getData();
        $controller = $data['controller'];
        $parameters = $data['params'];
        if(isset($parameters[self::FILTER])) {
            $filter = $parameters[self::FILTER];
            if($is_search == 1){
                $controller->getRequest()->setParam('q', (string)$filter['q']);
            }
            if(isset($filter['layer'])){
                $filter_layer = $filter['layer'];
                $params = array();
                foreach($filter_layer as $key=>$value){
                    $params[(string) $key] = (string) $value;
                }
                $controller->getRequest()->setParams($params);
            }
        }
        $layout = $controller->getLayout();
        if($is_search == 0){
            $block = $layout->createBlock('catalog/layer_view');
            //setCurrentCate
            $block->getLayer()->setCurrentCategory($category);
            $layers = $this->getItemsShopBy($block);
            $this->_layer = $layers;
            //update collection
            $this->builderQuery = $block->getLayer()->getProductCollection();
            $this->setAttributeProducts();
        }else{
            $block = $layout->createBlock('catalogsearch/layer');
            $layers = $this->getItemsShopBy($block);
            $this->_layer = $layers;
            //update collection
            $this->builderQuery = $block->getLayer()->getProductCollection();
            $this->setAttributeProducts(1);
        }
    }

    public function getItemsShopBy($block){
        $_children = $block->getChild();
        $refineArray = array();
        foreach ($_children as $index => $_child) {
            if ($index == 'layer_state') {
                // $itemArray = array();
                foreach ($_child->getActiveFilters() as $item) {
                    $itemValues = array();
                    $itemValues = $item->getValue();
                    if(is_array($itemValues)){
                        $itemValues = implode('-', $itemValues);
                    }

                    if($item->getFilter()->getRequestVar() != null){
                        $refineArray['layer_state'][] = array(
                            'attribute' => $item->getFilter()->getRequestVar(),
                            'title' => $item->getName(),
                            'label' => (string) strip_tags($item->getLabel()), //filter request var and correlative name
                            'value' => $itemValues,
                        ); //value of each option
                    }
                }
                // $refineArray[] = $itemArray;
            }else{
                $items = $_child->getItems();
                $itemArray = array();
                foreach ($items as $index => $item) {
                    $filter = array();
                    if ($index == 0) {
                        foreach ($items as $index => $item){
                            $filter[] = array(
                                'value' => $item->getValue(), //value of each option
                                'label' => strip_tags($item->getLabel()),
                            );
                        }

                        if($item->getFilter()->getRequestVar() != null) {
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

    public function getImageProduct($product, $file = null, $width, $height) {
        if (!is_null($width) && !is_null($height)) {
            if ($file) {
                return Mage::helper('catalog/image')->init($product, 'thumbnail', $file)->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize($width, $height)->__toString();
            }
            return Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize($width, $height)->__toString();
        }
        if ($file) {
            return Mage::helper('catalog/image')->init($product, 'thumbnail', $file)->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(600, 600)->__toString();
        }
        return Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(600, 600)->__toString();
    }
}