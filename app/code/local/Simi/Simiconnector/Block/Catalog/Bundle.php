<?php

class Simi_Simiconnector_Block_Catalog_Bundle extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle
{
    /**
     * Returns JSON encoded config to be used in JS scripts
     *
     * @return string
     */
    public function getJsonConfig()
    {
        /** @var $taxHelper Mage_Tax_Helper_Data */
        $taxHelper = Mage::helper('tax');
        Mage::app()->getLocale()->getJsPriceFormat();
        $optionsArray = $this->getOptions();
        $options      = array();
        $selected     = array();
        $currentProduct = $this->getProduct();
        /* @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper   = Mage::helper('core');
        /* @var $bundlePriceModel Mage_Bundle_Model_Product_Price */
        $bundlePriceModel = Mage::getModel('bundle/product_price');

        $preConfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preConfiguredFlag) {
            $preConfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        $position = 0;
        foreach ($optionsArray as $_option) {
            /* @var $_option Mage_Bundle_Model_Option */
            if (!$_option->getSelections()) {
                continue;
            }

            $optionId = $_option->getId();
            $option = array (
                'selections' => array(),
                'title'      => $_option->getTitle(),
                'type'       => $_option->getType(),
                'isMulti'    => in_array($_option->getType(), array('multi', 'checkbox')),
                'position'   => $position++,
                'isRequired' => $_option->getRequired(),
            );

            $selectionCount = count($_option->getSelections());

            foreach ($_option->getSelections() as $_selection) {
                /* @var $_selection Mage_Catalog_Model_Product */
                $selectionId = $_selection->getSelectionId();
                $_qty = !($_selection->getSelectionQty() * 1) ? '1' : $_selection->getSelectionQty() * 1;
                // recalculate currency
                $tierPrices = $_selection->getTierPrice();
                foreach ($tierPrices as &$tierPriceInfo) {
                    $tierPriceInfo['price'] =
                        $bundlePriceModel->getLowestPrice($currentProduct, $tierPriceInfo['price']);
                    $tierPriceInfo['website_price'] =
                        $bundlePriceModel->getLowestPrice($currentProduct, $tierPriceInfo['website_price']);
                    $tierPriceInfo['price'] = $coreHelper->currency($tierPriceInfo['price'], false, false);
                    $tierPriceInfo['priceInclTax'] = $taxHelper->getPrice(
                        $_selection, $tierPriceInfo['price'], true,
                        null, null, null, null, null, false
                    );
                    $tierPriceInfo['priceExclTax'] = $taxHelper->getPrice(
                        $_selection, $tierPriceInfo['price'], false,
                        null, null, null, null, null, false
                    );
                }

                unset($tierPriceInfo); // break the reference with the last element

                $itemPrice = $bundlePriceModel->getSelectionFinalTotalPrice(
                    $currentProduct, $_selection,
                    $currentProduct->getQty(), $_selection->getQty(), false, false
                );

                $canApplyMAP = false;
                $_priceInclTax = $taxHelper->getPrice(
                    $_selection, $itemPrice, true,
                    null, null, null, null, null, false
                );
                $_priceExclTax = $taxHelper->getPrice(
                    $_selection, $itemPrice, false,
                    null, null, null, null, null, false
                );

                if ($currentProduct->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    $_priceInclTax = $taxHelper->getPrice(
                        $currentProduct, $itemPrice, true,
                        null, null, null, null, null, false
                    );
                    $_priceExclTax = $taxHelper->getPrice(
                        $currentProduct, $itemPrice, false,
                        null, null, null, null, null, false
                    );
                }

                $selection = array (
                    'qty'              => $_qty,
                    'customQty'        => $_selection->getSelectionCanChangeQty(),
                    'price'            => $coreHelper->currency($_selection->getFinalPrice(), false, false),
                    'priceInclTax'     => $coreHelper->currency($_priceInclTax, false, false),
                    'priceExclTax'     => $coreHelper->currency($_priceExclTax, false, false),
                    'priceValue'       => $coreHelper->currency($_selection->getSelectionPriceValue(), false, false),
                    'priceType'        => $_selection->getSelectionPriceType(),
                    'tierPrice'        => $tierPrices,
                    'name'             => $_selection->getName(),
                    'plusDisposition'  => 0,
                    'minusDisposition' => 0,
                    'canApplyMAP'      => $canApplyMAP,
                    'tierPriceHtml'    => $this->getTierPriceHtml($_selection, $currentProduct),
                );

                $responseObject = new Varien_Object();
                $args = array('response_object' => $responseObject, 'selection' => $_selection);
                Mage::dispatchEvent('bundle_product_view_config', $args);
                if (is_array($responseObject->getAdditionalOptions())) {
                    foreach ($responseObject->getAdditionalOptions() as $o => $v) {
                        $selection[$o] = $v;
                    }
                }

                $option['selections'][$selectionId] = $selection;

                if (($_selection->getIsDefault() || ($selectionCount == 1 && $_option->getRequired()))
                    && $_selection->isSalable()
                ) {
                    $selected[$optionId][] = $selectionId;
                }
            }

            $options[$optionId] = $option;

            // Add attribute default value (if set)
            if ($preConfiguredFlag) {
                $configValue = $preConfiguredValues->getData('bundle_option/' . $optionId);
                if ($configValue) {
                    $defaultValues[$optionId] = $configValue;
                }
            }
        }

        $config = array(
            'options'       => $options,
            'selected'      => $selected,
            'bundleId'      => $currentProduct->getId(),
            'priceFormat'   => Mage::app()->getLocale()->getJsPriceFormat(),
            'basePrice'     => $coreHelper->currency($currentProduct->getPrice(), false, false),
            'priceType'     => $currentProduct->getPriceType(),
            'specialPrice'  => $currentProduct->getSpecialPrice(),
            'includeTax'    => Mage::helper('tax')->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => Mage::helper('tax')->displayBothPrices(),
            'isFixedPrice'  => $this->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED,
            'isMAPAppliedDirectly' => Mage::helper('catalog')->canApplyMsrp($this->getProduct(), null, false)
        );

        if ($preConfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        return $coreHelper->jsonEncode($config);
    }
}