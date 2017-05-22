<?php

class Simi_Simiconnector_Model_Api_Migrate_Categories extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
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

    public function index()
    {
        $result = parent::index();
        $storeIds = $this->getAllStoreIds();
        foreach ($result['migrate_categories'] as $index=>$category) {
            $nameArray = array();
            foreach ($storeIds as $storeId) {
                $nameByStore = Mage::getModel('catalog/category')
                    ->setStoreId($storeId)->load($category['entity_id'])->getName();
                if ($nameByStore) {
                    $nameArray[] = array($storeId, $nameByStore);
                }
            }
            if (count($nameArray)) {
                $result['migrate_categories'][$index]['json_name'] = json_encode($nameArray);
            }
        }
        return $result;
    }

    public function getAllStoreIds()
    {
        return Mage::getModel('core/store')
            ->getCollection()->getAllIds();
    }
}
