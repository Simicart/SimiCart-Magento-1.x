<?php

class Simi_Simiconnector_Model_Api_Dashboard_Stores extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('core/store_group')
                    ->getCollection();
        } else {
            $this->builderQuery = Mage::getModel('core/store_group')->load($data['resourceid']);
        }
    }
}
