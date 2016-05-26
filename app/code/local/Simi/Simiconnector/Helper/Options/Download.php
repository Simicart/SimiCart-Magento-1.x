<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/26/16
 * Time: 8:42 AM
 */
class Simi_Simiconnector_Helper_Options_Download extends Mage_Core_Helper_Abstract
{
    public function getOptions($product)
    {
        $info = array();
        $taxHelper = Mage::helper('tax');
        $block = Mage::getBlockSingleton('downloadable/catalog_product_links');
        $block->setProduct($product);
        //Mage_Catalog_Block_Product_View_Type_Grouped
        $_links = $block->getLinks();
        $_linksPurchasedSeparately = $block->getLinksPurchasedSeparately();
        $_isRequired = $block->getLinkSelectionRequired();
        if ($product->isSaleable() && $block->hasLinks()){
            $item = array(
                'title' => $block->getLinksTitle(),
                'type' => 'checkbox',
                'position' => '0',
                'links_purchased_separately' => $_linksPurchasedSeparately,
                'isRequired' => $_isRequired,
            );

            foreach ($_links as $_link) {
                $value = array(
                    'id' => $_link->getId(),
                    'title' => $block->escapeHtml($_link->getTitle()),
                );

                $price = $_link->getPrice();
                $_priceInclTax = Mage::helper('core')->currency($this->getPrice($product, $price, true), false, false);
                $_priceExclTax = Mage::helper('core')->currency($this->getPrice($product, $price), false, false);

                if ($taxHelper->displayPriceIncludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($value, $_priceInclTax);
                } elseif ($taxHelper->displayPriceExcludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($value, $_priceExclTax);
                } elseif ($taxHelper->displayBothPrices()) {
                    Mage::helper('simiconnector/price')->setBothTaxPrice($value, $_priceExclTax, $_priceInclTax);
                } else {
                    Mage::helper('simiconnector/price')->setTaxPrice($value, $_priceInclTax);
                }
                $item['value'][] = $value;
            }
            $info[] = $item;
        }
        $options = array();
        $options['download_sample'] = $this->getSampleData($product);
        $options['download_options'] = $info;
        if(!is_null($product->getOptions()) && count($product->getOptions())){
            $custom_options = Mage::helper('simiconnector/options_simple')->getOptions($product);
            $options['custom_options'] = $custom_options['custom_options'];
        }
        return $options;
    }

    public function getPrice($product, $price, $includingTax = null)
    {
        if (!is_null($includingTax)) {
            $price = Mage::helper('tax')->getPrice($product, $price, true);
        } else {
            $price = Mage::helper('tax')->getPrice($product, $price);
        }
        return $price;
    }

    public function getSampleData($product){
        $info = array();
        $block = Mage::getBlockSingleton('downloadable/catalog_product_samples');
        $block->setProduct($product);
        if ($block->hasSamples()){
            $_samples = $block->getSamples();
            $item = array(
                'title' => $block->getSamplesTitle(),
            );
            foreach ($_samples as $_sample){
                $value = array(
                    'url' => $block->getSampleUrl($_sample),
                    'title' => $block->escapeHtml($_sample->getTitle()),
                );
                $item['value'][] = $value;
                $info[] = $item;
            }
        }
        return $info;
    }
}