<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/25/16
 * Time: 9:07 AM
 */
class Simi_Simiconnector_Helper_Options_Grouped extends Mage_Core_Helper_Abstract
{
    public function getPrice($product, $price, $includingTax = null)
    {
        if (!is_null($includingTax)) {
            $price = Mage::helper('tax')->getPrice($product, $price, true);
        } else {
            $price = Mage::helper('tax')->getPrice($product, $price);
        }
        return $price;
    }

    function getOptions($product){
        $info = array();
        $taxHelper = Mage::helper('tax');
        //Mage_Catalog_Block_Product_View_Type_Grouped
        $_associatedProducts = $product->getTypeInstance(true)
            ->getAssociatedProducts($product);
        $_hasAssociatedProducts = count($_associatedProducts) > 0;
        if ($_hasAssociatedProducts){
            foreach ($_associatedProducts as $_item){
                $op = array(
                    'id' => $_item->getId(),
                    'name' => $_item->getName(),
                    'is_salable' => $_item->isSaleable() ? "1":"0",
                    'qty' => is_null($_item->getData('qty')) ? "0" : $_item->getData('qty'),
                    'position' => is_null($_item->getData('position'))? "0": $_item->getData('position'),
                );

                $final_price = $_item->getFinalPrice();
                $price = $_item->getPrice();
                if($final_price < $price){
                    $op['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $op['regular_price'] = $price;
                    $op['has_special_price'] = 1;
                    $op['special_price_label'] = Mage::helper('catalog')->__('Special Price');
                    $_priceInclTax = Mage::helper('core')->currency($this->getPrice($product, $final_price, true), false, false);
                    $_priceExclTax = Mage::helper('core')->currency($this->getPrice($product, $final_price), false, false);
                }else{
                    $op['has_special_price'] = 0;
                    $_priceInclTax = Mage::helper('core')->currency($this->getPrice($product, $price, true), false, false);
                    $_priceExclTax = Mage::helper('core')->currency($this->getPrice($product, $price), false, false);

                }

                $op['show_ex_in_price'] = 0;
                if ($taxHelper->displayPriceIncludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($op, $_priceInclTax);
                } elseif ($taxHelper->displayPriceExcludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($op, $_priceExclTax);
                } elseif ($taxHelper->displayBothPrices()) {
                    $op['show_ex_in_price'] = 1;
                    Mage::helper('simiconnector/price')->setBothTaxPrice($op, $_priceExclTax, $_priceInclTax);
                } else {
                    Mage::helper('simiconnector/price')->setTaxPrice($op, $_priceInclTax);
                }
                $info[] = $op;
            }
        }
        $options = array();
        $options['grouped_options'] = $info;
        return $options;
        //return $options;
    }
}