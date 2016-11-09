<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 11/9/16
 * Time: 1:27 PM
 */
class Simi_Simiconnector_Helper_Tierprice extends Mage_Core_Helper_Abstract
{
    private $_product;

    public function setProduct($product) {
        $this->_product = $product;
    }

    public function processTierPrices($product, &$tierPrices, $includeIndex = true) {
        $weeeAmount = Mage::helper('weee')->getAmountForDisplay($product);
        $weeeAmountInclTax = $weeeAmount;
        if (version_compare(Mage::getVersion(), '1.9.0.0', '>=') === true) {
            $weeeAmountInclTax = Mage::helper('weee')->getAmountForDisplayInclTaxes($product);
        }

        $store = Mage::app()->getStore();
        foreach ($tierPrices as $index => &$tier) {
            $html = $store->formatPrice($store->convertPrice(
                Mage::helper('tax')->getPrice($product, $tier['website_price'], true) + $weeeAmountInclTax), false);
            $tier['formated_price_incl_weee'] = $html;
            $html = $store->formatPrice($store->convertPrice(
                Mage::helper('tax')->getPrice($product, $tier['website_price']) + $weeeAmount), false);
            $tier['formated_price_incl_weee_only'] = $html;
            $tier['formated_weee'] = $store->formatPrice($store->convertPrice($weeeAmount), false);
        }
        return $this;
    }

    public function formatTierPrice($product) {
        $data = array();
        $_product = $product;
        $this->_product = $product;
        $_tierPrices = $this->getTierPrices();

        /** @var $_catalogHelper Mage_Catalog_Helper_Data */
        $_catalogHelper = Mage::helper('catalog');

        if (Mage::helper('weee')->typeOfDisplay($_product, array(1, 2, 4))) {
            $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForDisplay($_product);
        }

        if (count($_tierPrices) > 0) {
            $stringHt = '';
//            if ($_product->isGrouped()) {
//                $_tierPrices = $this->getTierPrices($_product);
//            }
            $this->processTierPrices($_product, $_tierPrices);

            foreach ($_tierPrices as $_index => $_price) {
                // $_price['formated_price_incl_weee'] = Mage::app()->getStore()->formatPrice($_price['formated_price_incl_weee'], false, false);
                // $_price['formated_price_incl_weee_only'] = Mage::app()->getStore()->formatPrice($_price['formated_price_incl_weee_only'], false, false);
                // $_price['formated_weee'] = Mage::app()->getStore()->formatPrice($_price['formated_weee'], false, false);
                if ($_catalogHelper->canApplyMsrp($_product)) {
                    if ($_product->isGrouped()) {
                        $stringHt = Mage::helper('catalog')->__('Buy %1$s for', $_price['price_qty']);
                    } else {
                        $stringHt = Mage::helper('catalog')->__('Buy %1$s', $_price['price_qty']);
                    }
                } else {
                    if (Mage::helper('tax')->displayBothPrices()) {

                        if (Mage::helper('weee')->typeOfDisplay($_product, 0)) {
                            $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s (%3$s incl. tax) each', $_price['price_qty'], $_price['formated_price_incl_weee_only'], $_price['formated_price_incl_weee']);
                        } elseif (Mage::helper('weee')->typeOfDisplay($_product, 1)) {
                            $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            $stringHt .= " ";
                            if ($_weeeTaxAttributes) {
                                $stringHt .= Mage::helper('catalog')->__('%1$s incl tax.', $_price['formated_price_incl_weee']);
                                $separator = ' + ';
                                $stringHt .= " ";
                                $stringHt .= $separator;
                                foreach ($_weeeTaxAttributes as $_attribute) {
                                    $stringHt .= " ";
                                    $stringHt .= $_attribute->getName();
                                    $stringHt .= ':';
                                    $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                }
                            }
                            $stringHt .= " ";
                            $stringHt .= Mage::helper('catalog')->__('each');
                        } elseif (Mage::helper('weee')->typeOfDisplay($_product, 4)) {
                            $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            if ($_weeeTaxAttributes) {
                                $stringHt .= Mage::helper('catalog')->__('%1$s incl tax.', $_price['formated_price_incl_weee']);
                                $separator = ' + ';
                                $stringHt .= " ";
                                $stringHt .= $separator;
                                foreach ($_weeeTaxAttributes as $_attribute) {
                                    $stringHt .= " ";
                                    $stringHt .= $_attribute->getName();
                                    $stringHt .= ':';
                                    $stringHt .= Mage::helper('core')->currency($_attribute->getAmount() + $_attribute->getTaxAmount());
                                }
                            }
                            $stringHt .= " ";
                            $stringHt .= Mage::helper('catalog')->__('each');
                        } elseif (Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                            $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price']);
                            if ($_weeeTaxAttributes) {
                                foreach ($_weeeTaxAttributes as $_attribute) {
                                    $stringHt .= " ";
                                    $stringHt .= $_attribute->getName();
                                    $stringHt .= ':';
                                    $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                }
                                $stringHt .= " ";
                                $stringHt .= Mage::helper('catalog')->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee']);
                            }
                            $stringHt .= " ";
                            $stringHt .= Mage::helper('catalog')->__('each');
                        } else {
                            $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s (%3$s incl. tax) each', $_price['price_qty'], $_price['formated_price'], $_price['formated_price_incl_tax']);
                        }
                    } else {

                        if (Mage::helper('tax')->displayPriceIncludingTax()) {

                            if (Mage::helper('weee')->typeOfDisplay($_product, 0)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_weee']);
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 1)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee']);
                                if ($_weeeTaxAttributes) {
                                    $stringHt .= " ";
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= $separator;
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                        $separator = ' + ';
                                        $stringHt .= " ";
                                    }
                                }
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 4)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee']);
                                if ($_weeeTaxAttributes) {
                                    $stringHt .= " ";
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= $separator;
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount() + $_attribute->getTaxAmount());
                                        $separator = ' + ';
                                        $stringHt .= " ";
                                    }
                                }
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 2)) {

                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_tax']);
                                if ($_weeeTaxAttributes) {
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= " ";
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                    }
                                    $stringHt .= " ";
                                    $stringHt .= Mage::helper('catalog')->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee']);
                                }
                                $stringHt .= " ";
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } else {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_tax']);
                            }
                        } else {
                            if (Mage::helper('weee')->typeOfDisplay($_product, 0)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 1)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                                if ($_weeeTaxAttributes) {
                                    $stringHt .= " ";
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= $separator;
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                        $separator = ' + ';
                                        $stringHt .= " ";
                                    }
                                }
                                $stringHt .= " ";
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 4)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price_incl_weee_only']);
                                if ($_weeeTaxAttributes) {
                                    $stringHt .= " ";
                                    $separator = '';
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= $separator;
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount() + $_attribute->getTaxAmount());
                                        $separator = ' + ';
                                        $stringHt .= " ";
                                    }
                                }
                                $stringHt .= " ";
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } elseif (Mage::helper('weee')->typeOfDisplay($_product, 2)) {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s', $_price['price_qty'], $_price['formated_price']);
                                if ($_weeeTaxAttributes) {
                                    foreach ($_weeeTaxAttributes as $_attribute) {
                                        $stringHt .= " ";
                                        $stringHt .=$_attribute->getName();
                                        $stringHt .= Mage::helper('core')->currency($_attribute->getAmount());
                                    }
                                    $stringHt .= " ";
                                    $stringHt .= Mage::helper('catalog')->__('Total incl. Tax: %1$s', $_price['formated_price_incl_weee_only']);
                                }
                                $stringHt .= Mage::helper('catalog')->__('each');
                            } else {
                                $stringHt = Mage::helper('catalog')->__('Buy %1$s for %2$s each', $_price['price_qty'], $_price['formated_price']);
                            }
                        }
                    }


                    if (!$_product->isGrouped()) {
                        if (($_product->getPrice() == $_product->getFinalPrice() && $_product->getPrice() > $_price['price'])
                            || ($_product->getPrice() != $_product->getFinalPrice() && $_product->getFinalPrice() > $_price['price'])) {
                            $stringHt .= " ";
                            $stringHt .= Mage::helper('catalog')->__('and');
                            $stringHt .= " ";
                            $stringHt .= Mage::helper('catalog')->__('save');
                            $stringHt .= " ";
                            $stringHt .= $_price['savePercent'];
                            $stringHt .= '%';
                        }
                    }
                    $stringHt .= " ";
                    $stringHt .= $_catalogHelper->getMsrpPriceMessage($_product);
                }
                $data[] = $stringHt;
            }
        }

        return $data;
    }

    public function getTierPrices($product = null) {
        if ($product == NULL) {

            $product = $this->_product;
        }
        $prices = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {

                $price['price_qty'] = $price['price_qty'] * 1;

                $productPrice = $product->getPrice();
                if ($product->getPrice() != $product->getFinalPrice()) {
                    $productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();

                if ($productPrice > $groupPrice) {
                    $productPrice = $groupPrice;
                }

                if ($price['price'] < $productPrice || $product->getTypeId() == "bundle") {
                    $price['savePercent'] = ceil(100 - ((100 / $productPrice) * $price['price']));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['website_price']), false
                    );
                    $price['formated_price'] = Mage::app()->getStore()->formatPrice($tierPrice, false, false);
                    $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                        Mage::app()->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['website_price'], false)
                        ), false, false
                    );

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        Mage::app()->getLayout()->getBlock('product.info')->getPriceHtml($product);
                        $product->setPriceCalculation(true);
                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }
        return $res;
    }
}