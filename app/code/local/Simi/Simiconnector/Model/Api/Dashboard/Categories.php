<?php

class Simi_Simiconnector_Model_Api_Dashboard_Categories extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            $this->builderQuery = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('url_path')
                    ->addAttributeToSelect('name');
        } else {
            $this->builderQuery = Mage::getModel('catalog/category')->load($data['resourceid']);
        }
    }
}
