<?php

class Simi_Simiconnector_Model_Api_Migrate_Stores extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
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
