<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 2:59 PM
 */
abstract class Simi_Simiconnector_Model_Api_Migrate_Abstract extends Simi_Simiconnector_Model_Api_Abstract {
    
    public $FILTER_RESULT = false;
    const DEFAULT_LIMIT = PHP_INT_MAX;
    
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
        if ($limit) {
            $collection->setPageSize($offset + $limit);
        }
        //$all_ids = array();
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
            if ($limit) {
                if (++$check_offset <= $offset) {
                    continue;
                }
                if (++$check_limit > $limit)
                    break;
            }
            $info[] = $entity->toArray($fields);
            //$all_ids[] = $entity->getId();
        }
        return $this->getList($info, $all_ids, $total, $limit, $offset);
    }
    
    public function getList($info, $all_ids, $total, $page_size, $from)
    {
        return array(
            //'all_ids' => $all_ids,
            $this->getPluralKey() => $this->motifyFields($info),
            'total' => $total,
            'page_size' => $page_size,
            'from' => $from,
        );
    }

    
    protected  function renewCustomerSesssion($data){
        return;
    }
}