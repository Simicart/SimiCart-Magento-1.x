<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 2:59 PM
 */
abstract class Simi_Simiconnector_Model_Api_Abstract
{
    const DEFAULT_DIR = 'asc';
    const DEFAULT_LIMIT = 15;
    const DIR = 'dir';
    const ORDER = 'order';
    const PAGE = 'page';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const FILTER = 'filter';
    const ALL_IDS = 'all_ids';
    const LIMIT_COUNT = 200;

    protected $_DEFAULT_ORDER = 'entity_id';
     
    protected $_helper;
    /**
     * Singular key.
     *
     * @var string
     */
    protected $singularKey;
    /**
     * Plural key.
     *
     * @var string
     */
    protected $pluralKey;
    /**
     *
     */
    /**
     * @var collection Magento
     */
    protected $builderQuery = null;

    protected $_data;

    public function __construct()
    {
        $this->_helper = Mage::helper('simiconnector');
    }

    abstract public function setBuilderQuery($query);

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get singular key
     * @return string
     */
    public function getSingularKey()
    {
        return $this->singularKey;
    }

    /**
     * Set singular query
     * @return $this
     */
    public function setSingularKey($singularKey)
    {
        $this->singularKey = substr($singularKey, 0, -1);
        return $this;
    }

    /**
     * Get singular key
     * @return string
     */
    public function getPluralKey()
    {
        return $this->pluralKey;
    }

    /**
     * Set singular query
     * @return $this
     */
    public function setPluralKey($pluralKey)
    {
        $this->pluralKey = $pluralKey;
        return $this;
    }
    //start
    public function store()
    {
        return $this->getDetail(array());
    }

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

            $info[] = $entity->toArray($fields);
            $all_ids[] = $entity->getId();
        }
        return $this->getList($info, $all_ids, $total, $limit, $offset);
    }

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
        return $this->getDetail($info);
    }

    public function update()
    {
        return $this->getDetail(array());
    }

    public function destroy()
    {
        return $this->getDetail(array());
    }

    //end
    public function getBuilderQuery()
    {
        return $this->builderQuery;
    }

    public function callApi($data)
    {
        $this->setData($data);
        $this->setBuilderQuery(null);
        $this->setPluralKey($data['resource']);
        $this->setSingularKey($data['resource']);
        if ($data['is_method'] == 1) {
            if (isset($data['resourceid']) && $data['resourceid'] != '') {
                return $this->show($data['resourceid']);
            } else {
                return $this->index();
            }
        } elseif ($data['is_method'] == 2) {
            return $this->store();
        } elseif ($data['is_method'] == 3) {
            return $this->update($data['resourceid']);
        } elseif ($data['is_method'] == 4) {
            return $this->destroy($data['resourceid']);
        }
    }

    public function getList($info, $all_ids, $total, $page_size, $from)
    {
        return array(
            'all_ids' => $all_ids,
            $this->getPluralKey() => $info,
            'total' => $total,
            'page_size' => $page_size,
            'from' => $from,
        );
    }

    public function getDetail($info)
    {
        return array($this->getSingularKey() => $info);
    }

    protected function filter()
    {
        $data = $this->_data;
        $parameters = $data['params'];
        $query = $this->builderQuery;
        $this->_whereFilter($query, $parameters);
        $this->_order($parameters);

        return $query;
    }

    protected function _order($parameters)
    {
        $query = $this->builderQuery;
        $order = isset($parameters[self::ORDER]) ? $parameters[self::ORDER] : $this->_DEFAULT_ORDER;
        $order = str_replace('|', '.', $order);
        $dir = isset($parameters[self::DIR]) ? $parameters[self::DIR] : self::DEFAULT_DIR;
        $query->setOrder($order, $dir);
    }

    protected function _whereFilter(&$query, $parameters)
    {
        if (isset($parameters[self::FILTER])) {
            foreach ($parameters[self::FILTER] as $key => $value) {
                if ($key == 'or') {
                    $filters = array();
                    foreach ($value as $k => $v) {
                        $filters[] = $this->_addCondition($k, $v, true);
                    }
                    if (count($filters)) $query->addAttributeToFilter($filters);
                } else {
                    $filter = $this->_addCondition($key, $value);
                    $query->addAttributeToFilter($key, $filter);
                }
            }
        }
    }

    protected function _addCondition($key, $value, $isOr = false)
    {
        $key = str_replace('|', '.', $key);
        if (is_array($value)) {
            foreach ($value as $operator => $v) {
                if ($operator == 'in' || $operator == 'nin') {
                    return $isOr ? array('attribute' => $key, $operator => explode(',', $v)) : array($operator => explode(',', $v));
                } else {
                    return $isOr ? array('attribute' => $key, $operator => $v) : array($operator => $v);
                }
            }
        } else {
            if (strlen($value) > 0) {
                return $isOr ? array('attribute' => $key, 'eq' => $value) : array('eq' => $value);
            }
        }
    }
}