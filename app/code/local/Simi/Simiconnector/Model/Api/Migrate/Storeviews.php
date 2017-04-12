<?php

class Simi_Simiconnector_Model_Api_Migrate_Storeviews extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('core/store')
                    ->getCollection();
        } else {
            $this->builderQuery = Mage::getModel('core/store')->load($data['resourceid']);
        }
    }
}
