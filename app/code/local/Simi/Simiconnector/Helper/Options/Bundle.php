<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/25/16
 * Time: 9:07 AM
 */
class Simi_Simiconnector_Helper_Options_Bundle extends Mage_Core_Helper_Abstract
{
    function getOptions($product){
        //Mage_Bundle_Block_Catalog_Product_View_Type_Bundle
        $block = Mage::getBlockSingleton('simiconnector/catalog_bundle');
        $block->setProduct($product);
        $options = array();
        $configurable_options = Mage::helper('core')->jsonDecode($block->getJsonConfig());
        $options['bundle_options'] = $configurable_options;
        return $options;
    }
}