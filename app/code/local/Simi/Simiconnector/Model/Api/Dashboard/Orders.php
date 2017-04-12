<?php

class Simi_Simiconnector_Model_Api_Dashboard_Orders extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('sales/order')
                    ->getCollection();
        } else {
            $this->builderQuery = Mage::getModel('sales/order')->load($data['resourceid']);
        }
    }
}
