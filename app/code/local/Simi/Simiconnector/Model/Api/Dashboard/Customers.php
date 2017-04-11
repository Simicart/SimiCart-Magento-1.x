<?php

/**
 * Created by PhpStorm.
 * User: Scott
 * Date: 5/19/2016
 * Time: 4:47 PM
 */
class Simi_Simiconnector_Model_Api_Dashboard_Customers extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
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
