<?php

class Simi_Simiconnector_Model_Api_Migrate_Customers extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('customer/customer')
                    ->getCollection();
        } else {
            $this->builderQuery = Mage::getModel('customer/customer')->load($data['resourceid']);
        }
    }
}
