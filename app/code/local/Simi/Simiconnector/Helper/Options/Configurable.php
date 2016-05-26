<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/25/16
 * Time: 9:07 AM
 */
class Simi_Simiconnector_Helper_Options_Configurable extends Mage_Core_Helper_Abstract
{
    function getOptions($product){
        $block = Mage::getBlockSingleton('catalog/product_view_type_configurable');
        $block->setProduct($product);
        $options = array();
        $configurable_options = Mage::helper('core')->jsonDecode($block->getJsonConfig());
        $options['configurable_options'] = $configurable_options;
        if(!is_null($product->getOptions()) && count($product->getOptions())){
           $custom_options = Mage::helper('simiconnector/options_simple')->getOptions($product);
            $options['custom_options'] = $custom_options['custom_options'];
        }
        return $options;
    }
}