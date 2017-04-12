<?php

class Simi_Simiconnector_Model_Api_Dashboard_Products extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('name');
        } else {
            $this->builderQuery = Mage::getModel('catalog/product')->load($data['resourceid']);
        }
    }
}
