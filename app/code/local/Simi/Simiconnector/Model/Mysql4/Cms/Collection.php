<?php

/**

 */
class Simi_Simiconnector_Model_Mysql4_Cms_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('simiconnector/cms');
    }

    public function setOrder($entity, $order) {
        if ($entity == 'entity_id') {
            $entity = 'cms_id';
        }
        return parent::setOrder($entity, $order);
    }

}
