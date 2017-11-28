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

        //Get Image swatch
        //Mage::register('product',$product);
        $options = array();
        $configurable_options = Mage::helper('core')->jsonDecode($block->getJsonConfig());
        if ($_attrValues = $product->getListSwatchAttrValues()) {
            $jsProduct = Mage::getBlockSingleton('configurableswatches/catalog_media_js_product');
            $mediaFallbacks =  $jsProduct->getProductImageFallbacks();
            $mediaFalback = array();
            foreach ($mediaFallbacks as $key => $value){
               if($key == $product->getId()){
                   $mediaFalback = $value['image_fallback'];
                   break;// break loop
               }
            }
            $mediaFalback = json_decode($mediaFalback,true);

            $_dimHelper = Mage::helper('configurableswatches/swatchdimensions');
            $_swatchInnerWidth = $_dimHelper->getInnerWidth(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LISTING);
            $_swatchInnerHeight = $_dimHelper->getInnerHeight(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_LISTING);

            foreach ($configurable_options['attributes'] as $_index => $attribute){
                foreach ($attribute['options'] as $index => $option){
                    $_swatchUrl = Mage::helper('configurableswatches/productimg')->getSwatchUrl($product, $option['label'], $_swatchInnerWidth, $_swatchInnerHeight, $_swatchType);
                    if(!empty($_swatchUrl)){
                        $option['option_image'] = $_swatchUrl;
                    }
                    $option['base_image'] = $this->getBaseImage($product,$option,$mediaFalback);
                    $attribute['options'][$index] = $option;
                }
                $configurable_options['attributes'][$_index]= $attribute;
            }
        }
        $options['configurable_options'] = $configurable_options;
        if(!is_null($product->getOptions()) && count($product->getOptions())){
            $custom_options = Mage::helper('simiconnector/options_simple')->getOptions($product);
            $options['custom_options'] = $custom_options['custom_options'];
        }
        return $options;
    }

    public function getBaseImage($product, $option,$mediaFallBack){
        $optionLabel = Mage_ConfigurableSwatches_Helper_Data::normalizeKey($option['label']);
        if(empty($mediaFallBack)){
            return "";
        }
        //Case 1: get from label
        $optionLabels = isset($mediaFallBack['option_labels']) ? $mediaFallBack['option_labels'] : false;
        if($optionLabels){
                $currentLabel =$optionLabels[$optionLabel];
                if($currentLabel && (isset($currentLabel['configurable_product']['base_image']) &&
                        $currentLabel['configurable_product']['base_image'])){

                    return $currentLabel['configurable_product']['base_image'];
                }
        }

        //case 2: second, get any product which is compatible with currently selected option(s)
        $compatibleProducts = $optionLabels[$optionLabel]['products'];

        if(empty($compatibleProducts)){
            return "";
        }

        foreach ($optionLabels as $key => $value){
            $image = $value['configurable_product']['base_image'];
            $products = $value['products'];
            if($image){
                $isCompatibleProduct = sizeof(array_intersect($product,$compatibleProducts)) > 0;
                if($isCompatibleProduct){
                    return $image;
                }
            }
        }

        //third, get image off of child product which is compatible
        $childSwatchImage = "";
        $childProductImages = $mediaFallBack['base_image'];
        foreach ($compatibleProducts as $productId){
            if((isset($childProductImages[$productId]) && $childProductImages[$productId])){
                $childSwatchImage = $childProductImages[$productId];
                break;
            }
        }

        if ($childSwatchImage) {
            return $childSwatchImage;
        }
        if((isset($childProductImages[$product->getId()]) && $childProductImages[$product->getId()])){
            return $childProductImages[$product->getId()];
        }
        return "";
    }

}