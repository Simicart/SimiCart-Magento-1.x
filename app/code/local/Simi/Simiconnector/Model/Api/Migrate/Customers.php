<?php

class Simi_Simiconnector_Model_Api_Migrate_Customers extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('customer/customer')
                    ->getCollection()
                    ->addAttributeToSelect('firstname')
                    ->addAttributeToSelect('lastname')
                    ->addAttributeToSelect('middlename')
                    ->addAttributeToSelect('suffix')
                    ->addAttributeToSelect('is_active')
                    ->addAttributeToSelect('email');
        } else {
            $this->builderQuery = Mage::getModel('customer/customer')->load($data['resourceid']);
        }
    }
}
