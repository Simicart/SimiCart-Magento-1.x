<?php

class Simi_Simiconnector_Model_Api_Migrate_Categorytrees extends Simi_Simiconnector_Model_Api_Migrate_Abstract {
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
        $returnedTree = array();
        foreach ($result['migrate_categorytrees'] as $tree) {
            $paths = explode("/", $tree['path']);
            $script = "\$returnedTree";
            foreach($paths as $path) {
                $script.="[".$path."]";
            }
            $script.= " = \$tree;";
            eval($script);
        }
        $result['migrate_categorytrees'] = $returnedTree;
        $result['total'] = count($returnedTree);
        return $result;
    }
}
