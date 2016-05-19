<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Stores extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'group_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('core/store_group')->load($data['resourceid']);
        } else {
            $this->builderQuery = $collection = Mage::getModel('core/store_group')->getCollection();
        }
    }

}
