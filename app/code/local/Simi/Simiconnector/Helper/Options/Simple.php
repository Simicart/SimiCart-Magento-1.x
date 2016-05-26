<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/25/16
 * Time: 9:01 AM
 */
class Simi_Simiconnector_Helper_Options_Simple extends Mage_Core_Helper_Abstract
{
    public function getOptions($product)
    {
        $info = array();
        $taxHelper = Mage::helper('tax');
        $options = Mage::helper('core')->decorateArray($product->getOptions());
        foreach ($options as $option) {
            $item = array();
            $item['id'] = $option->getId();
            $item['title'] = $option->getTitle();
            $item['type'] = $option->getType();
            $item['position'] = $option->getSortOrder();
            $item['isRequired'] = $option->getIsRequire();
            if($option->getType() == "file"){
                $item['file_extension'] = $option->getFileExtension();
            }
            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                foreach ($option->getValues() as $value) {
                    /* @var $value Mage_Catalog_Model_Product_Option_Value */
                    $item_value = array(
                        'id' => $value->getId(),
                        'title' => $value->getTitle(),
                    );
                    $price = $value->getPrice(true);
                    $_priceInclTax = Mage::helper('core')->currency($this->getPrice($product, $price, true), false, false);
                    $_priceExclTax = Mage::helper('core')->currency($this->getPrice($product, $price), false, false);

                    if ($taxHelper->displayPriceIncludingTax()) {
                        Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceInclTax);
                    } elseif ($taxHelper->displayPriceExcludingTax()) {
                        Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceExclTax);
                    } elseif ($taxHelper->displayBothPrices()) {
                        Mage::helper('simiconnector/price')->setBothTaxPrice($item_value, $_priceExclTax, $_priceInclTax);
                    } else {
                        Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceInclTax);
                    }

                    $item['values'][] = $item_value;
                }
            } else {
                $item_value = array();
                $price = $option->getPrice(true);
                $_priceInclTax = Mage::helper('core')->currency($this->getPrice($product, $price, true), false, false);
                $_priceExclTax = Mage::helper('core')->currency($this->getPrice($product, $price), false, false);

                if ($taxHelper->displayPriceIncludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceInclTax);
                } elseif ($taxHelper->displayPriceExcludingTax()) {
                    Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceExclTax);
                } elseif ($taxHelper->displayBothPrices()) {
                    Mage::helper('simiconnector/price')->setBothTaxPrice($item_value, $_priceExclTax, $_priceInclTax);
                } else {
                    Mage::helper('simiconnector/price')->setTaxPrice($item_value, $_priceInclTax);
                }
                $item['values'][] = $item_value;
            }

            $info[] = $item;
        }
        $options = array();
        $options['custom_options'] = $info;
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

}