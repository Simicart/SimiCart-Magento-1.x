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

    /**
     * override
     */
    public function setBuilderQuery()
    {
        $data = $this->getData();
        $parameters = $data['params'];
        $this->_helperProduct = Mage::helper('simiconnector/products');
        $this->_helperProduct->setData($data);

        if ($data['resourceid']) {
            $this->builderQuery = $this->_helperProduct->getProduct($data['resourceid']);
        } else {
            if (isset($parameters[self::FILTER])) {
                $filter = $parameters[self::FILTER];
                if (isset($filter['cat_id'])) {
                    $this->setFilterByCategoryId($filter['cat_id']);
                } elseif (isset($filter['q'])) {
                    $this->setFilterByQuery();
                } else {
                    $this->setFilterByCategoryId(Mage::app()->getStore()->getRootCategoryId());
                    $this->_allow_filter_core = true;
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
        if ($this->_allow_filter_core) {
            $query = $this->builderQuery;
            $this->_whereFilter($query, $parameters);
        }
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

        foreach ($collection as $entity) {
            if (++$check_offset <= $offset) {
                continue;
            }
            if (++$check_limit > $limit)
                break;
            $info_detail = $entity->toArray($fields);

            $images = array();
            $images[] = array(
                'url' => $this->_helperProduct->getImageProduct($entity, null, $parameters['image_width'], $parameters['image_height']),
                'position' => 1,
            );
            $info_detail['images'] = $images;
            $info_detail['app_price'] = Mage::helper('simiconnector/price')->formatPriceFromProduct($entity);
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
        if (isset($parameters['fields']) && $parameters['fields']) {
            $fields = explode(',', $parameters['fields']);
        }
        $info = $entity->toArray($fields);
        $media_gallery = $entity->getMediaGallery();
        $images = array();

        foreach ($media_gallery['images'] as $image) {
            // Zend_debug::dump($image['disabled']);
            if ($image['disabled'] == 0) {
                $images[] = array(
                    'url' => $this->_helperProduct->getImageProduct($entity, $image['file'], $parameters['image_width'], $parameters['image_height']),
                    'position' => $image['position'],
                );
            }
        }
        if (count($images) == 0) {
            $images[] = array(
                'url' => $this->_helperProduct->getImageProduct($entity, null, $parameters['image_width'], $parameters['image_height']),
                'position' => 1,
            );
        }
        $info['images'] = $images;
        $info['app_prices'] = Mage::helper('simiconnector/price')->formatPriceFromProduct($entity, true);
        $info['app_options'] = Mage::helper('simiconnector/options')->getOptions($entity);
        return $this->getDetail($info);
    }

    public function setFilterByCategoryId($cat_id)
    {
        $this->_helperProduct->setCategoryProducts($cat_id);
        $this->_layer = $this->_helperProduct->getLayers();
        $this->builderQuery = $this->_helperProduct->getBuilderQuery();
    }

    public function setFilterByQuery()
    {
        $this->_helperProduct->setLayers(1);
        $this->_layer = $this->_helperProduct->getLayers();
        $this->builderQuery = $this->_helperProduct->getBuilderQuery();
    }
}