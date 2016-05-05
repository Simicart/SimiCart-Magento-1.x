<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Products extends Simi_Simiconnector_Model_Api_Abstract
{
    public function setBuilderQuery($query)
    {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('catalog/product')->load($data['resourceid']);
        } else {
            $this->builderQuery = Mage::getResourceModel('catalog/product_collection');
            $this->builderQuery->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
            $this->builderQuery->addFinalPrice();
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->builderQuery);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->builderQuery);
            $this->builderQuery->addUrlRewrite(0);
        }

    }
}