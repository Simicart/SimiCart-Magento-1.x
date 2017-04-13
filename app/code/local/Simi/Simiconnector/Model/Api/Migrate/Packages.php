<?php

class Simi_Simiconnector_Model_Api_Migrate_Packages extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            throw new Exception($this->_helper->__('No Package Sent'), 4);
        } else {
            $data = $this->getData();
            if ($data['resourceid'] == 'all') {
                return;
            }
            throw new Exception($this->_helper->__('Package Invalid'), 4);
        }
    }
    
    
    public function show() {
        $result = array();
        
        $storetModel = Mage::getSingleton('simiconnector/api_migrate_stores');
        $storetModel->setBuilderQuery();
        $storetModel->pluralKey = 'migrate_stores';
        $result['stores'] = $storetModel->index();
        
        $storeviewtModel = Mage::getSingleton('simiconnector/api_migrate_storeviews');
        $storeviewtModel->setBuilderQuery();
        $storeviewtModel->pluralKey = 'migrate_storeviews';
        $result['storeviews'] = $storeviewtModel->index();
        
        $productModel = Mage::getSingleton('simiconnector/api_migrate_products');
        $productModel->setBuilderQuery();
        $productModel->pluralKey = 'migrate_products';
        $result['products'] = $productModel->index();
        
        $categoryModel = Mage::getSingleton('simiconnector/api_migrate_categories');
        $categoryModel->setBuilderQuery();
        $categoryModel->pluralKey = 'migrate_categories';
        $result['categories'] = $categoryModel->index();
        
        $customerModel = Mage::getSingleton('simiconnector/api_migrate_customers');
        $customerModel->setBuilderQuery();
        $customerModel->pluralKey = 'migrate_customers';
        $result['customers'] = $customerModel->index();
        
        return array('migrate_package'=>$result);
    }
}
