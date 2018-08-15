<?php

class Simi_Simiconnector_Model_Api_Urldicts extends Simi_Simiconnector_Model_Api_Abstract {

    public function setBuilderQuery(){
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $requestPath = $data['params']['url'];
            $requestPath = explode('?', $requestPath);
            $requestPath = $requestPath[0];
            $urlModel = Mage::getResourceModel('catalog/url');
            $this->builderQuery = $urlModel->getRewriteByRequestPath($requestPath, Mage::app()->getStore()->getId());
            if (!$this->builderQuery)
                throw new Exception($this->_helper->__('No URL Rewrite Found'), 4);
        }
    }
    public function show() {
        $result = parent::show();
        $data = $this->getData();
        if(isset($result['urldict']['product_id']) && $result['urldict']['product_id']) {
            $apiModel = Mage::getModel('simiconnector/api_products');
            $data['resourceid'] = $result['urldict']['product_id'];
            $apiModel->singularKey = 'product';
            $apiModel->setData($data);
            $apiModel->setBuilderQuery();
            $result['urldict']['simi_product_data'] = $apiModel->show();
        } else if(isset($result['urldict']['category_id']) && $result['urldict']['category_id']) {
            if (isset($data['params']['get_child_cat']) && $data['params']['get_child_cat']) {
                $apiModel = Mage::getModel('simiconnector/api_categories');
                $result['urldict']['simi_catetory_name'] = Mage::getModel('catalog/category')
                    ->load($result['urldict']['category_id'])
                    ->getName();
                $data['resourceid'] = $result['urldict']['category_id'];
                $apiModel->pluralKey = 'categories';
                $apiModel->singularKey = 'category';
                $apiModel->setData($data);
                $apiModel->setBuilderQuery();
                $result['urldict']['simi_category_child'] = $apiModel->show();
            }

            $productListModel = Mage::getModel('simiconnector/api_products');
            unset($data['resourceid']);
            $data['params'][self::FILTER] = array('cat_id'=>$result['urldict']['category_id']);
            $data['params']['image_width'] = isset($data['params']['image_width'])?$data['params']['image_width']:180;
            $data['params']['image_height'] = isset($data['params']['image_height'])?$data['params']['image_height']:180;
            $data['params']['limit'] = 12;
            $productListModel->pluralKey = 'products';
            $productListModel->singularKey = 'product';
            $productListModel->setData($data);
            $productListModel->setBuilderQuery();
            $result['urldict']['simi_category_products'] = $productListModel->index();
        }
        return $result;
    }
}