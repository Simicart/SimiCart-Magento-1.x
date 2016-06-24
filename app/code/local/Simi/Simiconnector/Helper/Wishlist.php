<?php

class Simi_Simiconnector_Helper_Wishlist extends Mage_Core_Helper_Abstract {

    /*
     * Get Wishlist Item Id
     * 
     * @param Product Model
     */
    public function getWishlistItemId($product) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId() && ($customer->getId() != '')) {
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
            foreach ($wishlist->getItemCollection() as $item) {
                if ($item->getProduct()->getId() != $product->getId())
                    continue;
                $productOptions = $this->getCustomOptions($product);
                if ($product->getTypeId() == 'configurable') {
                    $productOptions = array_merge($productOptions, $this->getConfigurableOptions($product));
                }
                if ($product->getTypeId() == 'downloadable') {
                    $productOptions = array_merge($productOptions, $this->getDownloadableOptions($product));
                }
                foreach ($productOptions as $productOption) {
                    if ($productOption['is_required'] == 'YES') {
                        return;
                    }
                }
                return $item->getId();
            }
        }
    }

    /*
     * @param:
     * $item - Wishlist Item
     */

    public function checkIfSelectedAllRequiredOptions($item, $options = null) {
        $selected = false;
        $product = $item->getProduct();
        $allowedType = array('simple', 'downloadable', 'configurable');
        if (in_array($product->getTypeId(), $allowedType)) {
            $selected = true;
            $selectedoptions = $this->getOptionsSelectedFromItem($item, $product);
            $productOptions = $this->getCustomOptions($product);
            if ($product->getTypeId() == 'configurable') {
                $productOptions = array_merge($productOptions, $this->getConfigurableOptions($product));
            }
            if ($product->getTypeId() == 'downloadable') {
                $productOptions = array_merge($productOptions, $this->getDownloadableOptions($product));
            }
            foreach ($productOptions as $productOption) {
                if ($productOption['is_required'] == 'YES') {
                    $selected = false;
                    foreach ($selectedoptions as $option) {
                        if (($option['option_title'] == $productOption['option_title']) && ($option['option_value']) && ($option['option_value'] != ''))
                            $selected = true;
                    }
                }
            }
        }
        return $selected;
    }

    public function getOptionsSelectedFromItem($item, $product) {
        $options = array();
        if (version_compare(Mage::getVersion(), '1.5.0.0', '>=') === true) {
            $helper = Mage::helper('catalog/product_configuration');
            if ($product->getTypeId() == "simple") {
                $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getCustomOptions($item));
            } elseif ($product->getTypeId() == "configurable") {
                $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getConfigurableOptions($item));
            } elseif ($product->getTypeId() == "bundle") {
                $options = Mage::helper('simiconnector/checkout')->getOptions($item);
            }
        } else {
            if ($product->getTypeId() != "bundle") {
                $options = Mage::helper('simiconnector/checkout')->getUsedProductOption($item);
            } else {
                $options = Mage::helper('simiconnector/checkout')->getOptions($item);
            }
        }
        return $options;
    }

    /*
     * Configurable Options
     */

    public function getConfigurableOptions($_product) {
        $options = array();
        $currentProduct = $_product;
        $products = $this->getAllowProducts($_product);
        $attributes = $_product->getTypeInstance(true)->getConfigurableAttributes($_product);

        $information = array();
        foreach ($products as $product) {
            $productId = $product->getId();
            foreach ($attributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }
                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getId();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!isset($options[$attributeId][$value['value_index']])) {
                        continue;
                    }
                    $productsIndex = array();
                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    }
                    $infor = array(
                        'option_id' => $value['value_index'],
                        'option_value' => $value['label'],
                        'option_title' => $attribute->getLabel(),
                        'is_required' => 'YES'
                    );
                    $information[] = $infor;
                }
            }
        }
        return $information;
    }

    public function getAllowProducts($_product) {
        $products = array();
        $skipSaleableCheck = true;
        if (version_compare(Mage::getVersion(), '1.7.0.0', '>=') === true) {
            $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        }
        $allProducts = $_product->getTypeInstance(true)
                ->getUsedProducts(null, $_product);
        foreach ($allProducts as $product) {
            if ($product->isSaleable() || $skipSaleableCheck) {
                $products[] = $product;
            }
        }
        return $products;
    }

    /*
     * Custom Options
     */

    public function getCustomOptions($product) {
        $information = array();
        foreach ($product->getOptions() as $_option) {

            if ($_option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                foreach ($_option->getValues() as $value) {
                    $info = array(
                        'option_id' => $value->getId(),
                        'option_value' => $value->getTitle(),
                        'option_title' => $_option->getTitle(),
                        'is_required' => $_option->getIsRequire() == 1 ? 'YES' : 'No',
                    );
                    $information[] = $info;
                }
            } else if ($_option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_TEXT) {
                $info = array(
                    'option_title' => $_option->getTitle(),
                    'is_required' => $_option->getIsRequire() == 1 ? 'YES' : 'No',
                );
                $information[] = $info;
            } else if ($_option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE) {
                $info = array(
                    'option_title' => $_option->getTitle(),
                    'is_required' => $_option->getIsRequire() == 1 ? 'YES' : 'No',
                );

                $information[] = $info;
            }
        }
        return $information;
    }

    /*
     * Downloadable Options
     */

    public function getDownloadableOptions($product) {
        $info = array();
        $block = Mage::getBlockSingleton('downloadable/catalog_product_links');
        $block->setProduct($product);
        $_links = $block->getLinks();
        $_isRequired = $block->getLinkSelectionRequired();
        if ($_isRequired == '1')
            $_isRequired = 'YES';
        else
            $_isRequired = 'No';
        if ($product->isSaleable() && $block->hasLinks()) {
            $item = array(
                'title' => $block->getLinksTitle(),
                'type' => 'checkbox',
                'position' => '0',
                'is_required' => $_isRequired,
            );
            $info[] = $item;
        }
        return $info;
    }

}
