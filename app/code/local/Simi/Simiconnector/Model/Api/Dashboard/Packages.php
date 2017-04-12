<?php

class Simi_Simiconnector_Model_Api_Dashboard_Packages extends Simi_Simiconnector_Model_Api_Dashboard_Abstract {
    public function setBuilderQuery() {
        $data = $this->getData();
        if (!$data['resourceid']) {
            throw new Exception($this->__('No Package Sent'), 4);
        } else {
            $data = $this->getData();
            if ($data['resourceid'] == 'all') {
                return;
            }
            throw new Exception($this->__('Package Invalid'), 4);
        }
    }
    
    
    public function show() {
        $result = array();
        
        $storetModel = Mage::getSingleton('simiconnector/api_dashboard_stores');
        $storetModel->setBuilderQuery();
        $storetModel->pluralKey = 'dashboard_stores';
        $result['stores'] = $storetModel->index();
        
        $storeviewtModel = Mage::getSingleton('simiconnector/api_dashboard_storeviews');
        $storeviewtModel->setBuilderQuery();
        $storeviewtModel->pluralKey = 'dashboard_storeviews';
        $result['storeviews'] = $storeviewtModel->index();
        
        $productModel = Mage::getSingleton('simiconnector/api_dashboard_products');
        $productModel->setBuilderQuery();
        $productModel->pluralKey = 'dashboard_products';
        $result['products'] = $productModel->index();
        
        $categoryModel = Mage::getSingleton('simiconnector/api_dashboard_categories');
        $categoryModel->setBuilderQuery();
        $categoryModel->pluralKey = 'dashboard_categories';
        $result['categories'] = $categoryModel->index();
        
        $customerModel = Mage::getSingleton('simiconnector/api_dashboard_customers');
        $customerModel->setBuilderQuery();
        $customerModel->pluralKey = 'dashboard_customers';
        $result['customers'] = $customerModel->index();
        
        return array('dashboard_package'=>$result);
    }
}
