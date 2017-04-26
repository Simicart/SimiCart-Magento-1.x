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
    protected $_allow_filter_core = false;
    protected $_helperProduct;
    protected $_sortOrders = array();
    public $detail_info;

    /**
     * override
     */
    public function setBuilderQuery()
    {
        $data = $this->getData();
        $parameters = $data['params'];
        $this->_helperProduct = Mage::helper('simiconnector/products');
        $this->_helperProduct->setData($data);

        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = $this->_helperProduct->getProduct($data['resourceid']);
        } else {
            if (isset($parameters[self::FILTER])) {
                $filter = $parameters[self::FILTER];
                if (isset($filter['cat_id'])) {
                    $this->setFilterByCategoryId($filter['cat_id']);
                } elseif (isset($filter['q'])) {
                    $this->setFilterByQuery();
                } elseif (isset($filter['related_to_id'])) {
                    $this->setFilterByRelated($filter['related_to_id']);
                } else {
                    $this->setFilterByCategoryId(Mage::app()->getStore()->getRootCategoryId());
                    // $this->_allow_filter_core = true;
                }
            } else {
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
            'orders' => $this->_sortOrders,
        );
    }

    /**
     * @return collection
     * override
     */
    protected function filter()
    {        
		if (!$this->FILTER_RESULT)
            return;
        $data = $this->_data;
        $parameters = $data['params'];
        if ($this->_allow_filter_core) {
            $query = $this->builderQuery;
            $this->_whereFilter($query, $parameters);

        }

        if (isset($parameters['dir']) && isset($parameters['order']))
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
        if (isset($parameters['fields']) && $parameters['fields']) {
            $fields = explode(',', $parameters['fields']);
        }

        $check_limit = 0;
        $check_offset = 0;

        if (isset($parameters['image_width'])){
            $image_width = $parameters['image_width'];
            $image_height = $parameters['image_height'];
        } else {
            $image_width = 600;
            $image_height = 600;
        }
            
        foreach ($collection as $entity) {
            if (++$check_offset <= $offset) {
                continue;
            }
            if (++$check_limit > $limit)
                break;
            $info_detail = $entity->toArray($fields);
            $all_ids[] = $entity->getId();
            $images = array();
            $imagelink = $this->_helperProduct->getImageProduct($entity, null, $image_width, $image_height);
            //$sizes = getimagesize($imagelink);
            $images[] = array(
                'url' => $imagelink,
                'position' => 1,
//                'image_width' => $sizes[0],
//                'image_height' => $sizes[1],
            );
            $ratings = Mage::helper('simiconnector/review')->getRatingStar($entity->getId());
            $total_rating = Mage::helper('simiconnector/review')->getTotalRate($ratings);
            $avg = Mage::helper('simiconnector/review')->getAvgRate($ratings, $total_rating);

            $info_detail['images'] = $images;
            $info_detail['app_prices'] = Mage::helper('simiconnector/price')->formatPriceFromProduct($entity);
            $info_detail['app_reviews'] = array(
                'rate' => $avg,
                'number' => $ratings[5],
            );
            $info_detail['product_label'] = Mage::helper('simiconnector/productlabel')->getProductLabel($entity);
            $info[] = $info_detail;
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
        if (isset($parameters['fields']) && $parameters['fields']) {
            $fields = explode(',', $parameters['fields']);
        }
        $info = $entity->toArray($fields);
        $media_gallery = $entity->getMediaGallery();
        $images = array();

        if (isset($parameters['image_width'])){
            $image_width = $parameters['image_width'];
            $image_height = $parameters['image_height'];
        } else {
            $image_width = 600;
            $image_height = 600;
        }
        
        foreach ($media_gallery['images'] as $image) {
            // Zend_debug::dump($image['disabled']);
            if ($image['disabled'] == 0) {
                $imagelink = $this->_helperProduct->getImageProduct($entity, $image['file'], $image_width, $image_height);
                //$sizes = getimagesize($imagelink);
                $images[] = array(
                    'url' => $imagelink,
                    'position' => $image['position'],
//                    'image_width' => $sizes[0],
//                    'image_height' => $sizes[1],
                );
            }
        }
        if (count($images) == 0) {
            $imagelink = $this->_helperProduct->getImageProduct($entity, null, $image_width, $image_height);
            //$sizes = getimagesize($imagelink);
            $images[] = array(
                'url' => $imagelink,
                'position' => 1,
//                'image_width' => $sizes[0],
//                'image_height' => $sizes[1],
            );
        }
        if (!Mage::registry('product') && $entity->getId()) {
            Mage::register('product', $entity);
        }

        $block_att = Mage::getBlockSingleton('catalog/product_view_attributes');
        $_additional = $block_att->getAdditionalData();

        $ratings = Mage::helper('simiconnector/review')->getRatingStar($entity->getId());
        $total_rating = Mage::helper('simiconnector/review')->getTotalRate($ratings);
        $avg = Mage::helper('simiconnector/review')->getAvgRate($ratings, $total_rating);

        $info['description'] = Mage::helper('catalog/output')->productAttribute($entity, $entity->getDescription(), 'description');
        $info['additional'] = $_additional;
        $info['images'] = $images;
        $info['app_prices'] = Mage::helper('simiconnector/price')->formatPriceFromProduct($entity, true);
        $info['app_tier_prices'] = Mage::helper('simiconnector/tierprice')->formatTierPrice($entity);
        $info['app_reviews'] = array(
            'rate' => $avg,
            'number' => $ratings[5],
            '5_star_number' => $ratings[4],
            '4_star_number' => $ratings[3],
            '3_star_number' => $ratings[2],
            '2_star_number' => $ratings[1],
            '1_star_number' => $ratings[0],
            'form_add_reviews' => Mage::helper('simiconnector/review')->getReviewToAdd(),
        );

        $info['app_options'] = Mage::helper('simiconnector/options')->getOptions($entity);
        $info['wishlist_item_id'] = Mage::helper('simiconnector/wishlist')->getWishlistItemId($entity);
        $info['product_label'] = Mage::helper('simiconnector/productlabel')->getProductLabel($entity);
        $info['product_video'] = Mage::helper('simiconnector/simivideo')->getProductVideo($entity);
        $this->detail_info = $this->getDetail($info);
        Mage::dispatchEvent('simi_simiconnector_model_api_products_show_after', array('object' => $this, 'data' => $this->detail_info));
        return $this->detail_info;
    }

    public function setFilterByCategoryId($cat_id)
    {
        $this->_helperProduct->setCategoryProducts($cat_id);
        $this->_layer = $this->_helperProduct->getLayers();
        $this->builderQuery = $this->_helperProduct->getBuilderQuery();
        $this->_sortOrders = $this->_helperProduct->getStoreQrders();
    }

    public function setFilterByQuery()
    {
        $this->_helperProduct->setLayers(1);
        $this->_layer = $this->_helperProduct->getLayers();
        $this->builderQuery = $this->_helperProduct->getBuilderQuery();
        $this->_sortOrders = $this->_helperProduct->getStoreQrders();
    }

    public function setFilterByRelated($product_id)
    {
        $this->_helperProduct->setRelatedProduct($product_id);
        $this->_layer = $this->_helperProduct->getLayers();
        $this->builderQuery = $this->_helperProduct->getBuilderQuery();
        $this->_sortOrders = $this->_helperProduct->getStoreQrders();
    }

}
