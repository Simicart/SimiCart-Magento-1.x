<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/25/16
 * Time: 8:59 AM
 */
class Simi_Simiconnector_Helper_Options extends Mage_Core_Helper_Abstract
{
    public function helper($helper)
    {
        return Mage::helper('simiconnector/options_' . $helper);
    }

    public function getOptions($product)
    {
        $type = $product->getTypeId();
        switch ($type) {
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                return $this->helper('simple')->getOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
                return $this->helper('bundle')->getOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE :
                return $this->helper('configurable')->getOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED :
                return $this->helper('grouped')->getOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL :
                return $this->helper('simple')->getOptions($product);
                break;
            case "downloadable" :
                return $this->helper('download')->getOptions($product);
                break;
        }
    }
}