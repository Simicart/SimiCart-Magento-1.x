<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/20/16
 * Time: 8:52 AM
 */
class Simi_Simiconnector_Helper_Price extends Mage_Core_Helper_Abstract
{
    protected $_product = null;

    public function helper($helper)
    {
        return Mage::helper($helper);
    }

    public function getProductAttribute($attribute) {
        return $this->_product->getResource()->getAttribute($attribute);
    }

    public function formatPriceFromProduct($_product, $is_detail=false)
    {
        $priveV2 = array();
        $this->_product = $_product;

        $_coreHelper = $this->helper('core');
        $_weeeHelper = $this->helper('weee');
        $_taxHelper = $this->helper('tax');
        /* @var $_coreHelper Mage_Core_Helper_Data */
        /* @var $_weeeHelper Mage_Weee_Helper_Data */
        /* @var $_taxHelper Mage_Tax_Helper_Data */

        $_storeId = $_product->getStoreId();
        $_store = $_product->getStore();
        $_weeeSeparator = '';
        $_id = $_product->getId();
        $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
        $_minimalPriceValue = $_product->getMinimalPrice();
        $_minimalPriceValue = $_store->roundPrice($_store->convertPrice($_minimalPriceValue));
        $_minimalPrice = $_taxHelper->getPrice($_product, $_minimalPriceValue, $_simplePricesTax);
        $_convertedFinalPrice = $_store->roundPrice($_store->convertPrice($_product->getFinalPrice()));
        $_specialPriceStoreLabel = $this->getProductAttribute('special_price')->getStoreLabel();

        if ($_product->getTypeId() == "bundle") {
            return Mage::helper('simiconnector/bundle_price')->formatPriceFromProduct($_product, $is_detail);
        }

        if (!$_product->isGrouped()) {
            $_weeeTaxAmount = $_weeeHelper->getAmountForDisplay($_product);
            $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
            $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
            if ($_weeeHelper->isTaxable()) {
                $_weeeTaxAmountInclTaxes = $_weeeHelper->getAmountInclTaxes($_weeeTaxAttributes);
            }
            $_weeeTaxAmount = $_store->roundPrice($_store->convertPrice($_weeeTaxAmount));
            $_weeeTaxAmountInclTaxes = $_store->roundPrice($_store->convertPrice($_weeeTaxAmountInclTaxes));

            //price box
            $_convertedPrice = $_store->roundPrice($_store->convertPrice($_product->getPrice()));
            $_price = $_taxHelper->getPrice($_product, $_convertedPrice);
            $_regularPrice = $_taxHelper->getPrice($_product, $_convertedPrice, $_simplePricesTax);
            $_finalPrice = $_taxHelper->getPrice($_product, $_convertedFinalPrice);
            $_finalPriceInclTax = $_taxHelper->getPrice($_product, $_convertedFinalPrice, true);
            $_weeeDisplayType = $_weeeHelper->getPriceDisplayType();
            if ($_finalPrice >= $_price) {
                $priveV2['has_special_price'] = 0;
                if ($_taxHelper->displayBothPrices()) {
                    $priveV2['show_ex_in_price'] = 1;
                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)) {
                        $_exclTax = $_price + $_weeeTaxAmount;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    }elseif($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)){
                        $wee ='';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            $wee += $_weeeTaxAttribute->getName();
                            $wee += ": ";
                            $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                            $wee += " + ";
                            $priveV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priveV2, $wee);
                        $_exclTax = $_price + $_weeeTaxAmount;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                        //$priveV2['show_type'] = 1;
                        $priveV2['show_weee_price'] = 1;
                    }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)){
                        $wee ='';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            $wee += $_weeeTaxAttribute->getName();
                            $wee += ": ";
                            $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                            $wee += " + ";
                            $priveV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priveV2, $wee);
                        $_exclTax = $_price + $_weeeTaxAmount;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                        //$priveV2['show_type'] = 2;
                        $priveV2['show_weee_price'] = 2;
                    }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)){
                        $wee ='';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            $wee += $_weeeTaxAttribute->getName();
                            $wee += ": ";
                            $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                            $wee += " + ";
                            $priveV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priveV2, $wee);
                        $_exclTax = $_price;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                        //$priveV2['show_type'] = 1;
                        $priveV2['show_weee_price'] = 1;
                    }else{
                        $_exclTax = $_finalPrice;
                        if ($_finalPrice == $_price){
                            $_exclTax = $_price;
                        }
                        $_inclTax = $_finalPriceInclTax;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    }
                }else{
                    $priveV2['show_ex_in_price'] = 0;
                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, array(0, 1))){
                        $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                        $weeeAmountToDisplay = $_taxHelper->displayPriceIncludingTax() ? $_weeeTaxAmountInclTaxes : $_weeeTaxAmount;
                        $this->setTaxReguarlPrice($priveV2, $_price + $weeeAmountToDisplay);
                        if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)){
                            $wee ='';
                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                                $wee += $_weeeTaxAttribute->getName();
                                $wee += ": ";
                                $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                $wee += " + ";
                                $priveV2["weee"] = $wee;
                            }
                            //$priveV2['show_type'] = 4;
                            $priveV2['show_weee_price'] = 1;
                        }
                    }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)){
                        $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                        $this->setTaxReguarlPrice($priveV2, $_price + $_weeeTaxAmount);
                        if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)){
                            $wee ='';
                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                                $wee += $_weeeTaxAttribute->getName();
                                $wee += ": ";
                                $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                $wee += " + ";
                                $priveV2["weee"] = $wee;
                            }
                            //$priveV2['show_type'] = 4;
                            $priveV2['show_weee_price'] = 1;
                        }
                    }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)){
                        $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                        $weeeAmountToDisplay = $_taxHelper->displayPriceIncludingTax() ? $_weeeTaxAmountInclTaxes : $_weeeTaxAmount;
                        $this->setTaxReguarlPrice($priveV2, $_price + $weeeAmountToDisplay);
                        if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)){
                            $wee ='';
                            foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                                $wee += $_weeeTaxAttribute->getName();
                                $wee += ": ";
                                $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                                $wee += " + ";
                                $priveV2["weee"] = $wee;
                            }
                            $priveV2['show_weee_price'] = 2;
                        }
                    }else{
                        if ($_finalPrice == $_price){
                            $this->setTaxPrice($priveV2, $_finalPrice);
                        }else{
                            $this->setTaxPrice($priveV2, $_price);
                        }
                    }
                }
            }else{  /* if ($_finalPrice == $_price): */
                $priveV2['has_special_price'] = 1;
                $_originalWeeeTaxAmount = $_weeeHelper->getOriginalAmount($_product);
                $_originalWeeeTaxAmount = $_store->roundPrice($_store->convertPrice($_originalWeeeTaxAmount));
                if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 0)){
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $this->setTaxReguarlPrice($priveV2, $_regularPrice + $_originalWeeeTaxAmount);
                    if ($_taxHelper->displayBothPrices()){
                        $priveV2['show_ex_in_price'] = 1;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $_exclTax = $_finalPrice + $_weeeTaxAmount;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    }else{
                        $priveV2['show_ex_in_price'] = 0;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $this->setTaxPrice($priveV2, $_finalPrice + $_weeeTaxAmountInclTaxes);
                    }
                }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 1)){
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $this->setTaxReguarlPrice($priveV2, $_regularPrice + $_originalWeeeTaxAmount);
                    if ($_taxHelper->displayBothPrices()){
                        $priveV2['show_ex_in_price'] = 1;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $_exclTax = $_finalPrice + $_weeeTaxAmount;
                        $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                        $wee ='';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            $wee += $_weeeTaxAttribute->getName();
                            $wee += ": ";
                            $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                            $wee += " + ";
                            $priveV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priveV2, $wee);
                        $priveV2['show_weee_price'] = 1;
                    }else{
                        $priveV2['show_ex_in_price'] = 0;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $this->setTaxPrice($priveV2, $_finalPrice + $_weeeTaxAmountInclTaxes);
                        $wee ='';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                            $wee += $_weeeTaxAttribute->getName();
                            $wee += ": ";
                            $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                            $wee += " + ";
                            $priveV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priveV2, $wee);
                        $priveV2['show_weee_price'] = 1;
                    }
                }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 4)){
                    $priveV2['show_ex_in_price'] = 1;
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $this->setTaxReguarlPrice($priveV2, $_regularPrice + $_originalWeeeTaxAmount);
                    $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                    $_exclTax = $_finalPrice + $_weeeTaxAmount;
                    $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                    $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    $wee ='';
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                        $wee += $_weeeTaxAttribute->getName();
                        $wee += ": ";
                        $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                        $wee += " + ";
                        $priveV2["weee"] = $wee;
                    }
                    $this->setWeePrice($priveV2, $wee);
                    $priveV2['show_weee_price'] = 1;
                }elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)){
                    $priveV2['show_ex_in_price'] = 1;
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $this->setTaxReguarlPrice($priveV2, $_regularPrice);
                    $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                    $_exclTax = $_finalPrice;
                    $_inclTax = $_finalPriceInclTax + $_weeeTaxAmountInclTaxes;
                    $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    $wee ='';
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute) {
                        $wee += $_weeeTaxAttribute->getName();
                        $wee += ": ";
                        $wee += $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false);
                        $wee += " + ";
                        $priveV2["weee"] = $wee;
                    }
                    $this->setWeePrice($priveV2, $wee);
                    $priveV2['show_weee_price'] = 1;
                }else{
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Regular Price');
                    $this->setTaxReguarlPrice($priveV2, $_regularPrice);
                    if ($_taxHelper->displayBothPrices()){
                        $priveV2['show_ex_in_price'] = 1;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $_exclTax = $_finalPrice;
                        $_inclTax = $_finalPriceInclTax;
                        $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                    }else{
                        $priveV2['show_ex_in_price'] = 0;
                        $priveV2['special_price_label'] = $_specialPriceStoreLabel;
                        $this->setTaxPrice($priveV2, $_finalPrice);
                    }
                }
            }//end /* if ($_finalPrice == $_price): */
            if ($this->getDisplayMinimalPrice() && $_minimalPriceValue && $_minimalPriceValue < $_convertedFinalPrice){
                $_minimalPriceDisplayValue = $_minimalPrice;
                if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, array(0, 1, 4))){
                    $_minimalPriceDisplayValue = $_minimalPrice + $_weeeTaxAmount;
                    $priveV2['is_low_price'] = 1;
                    $priveV2['low_price_label'] = Mage::helper('catalog')->__('As low as:');
                    $this->setTaxLowPrice($priveV2, $_minimalPriceDisplayValue);
                }
            }
        } else { // group product
            $showMinPrice = $this->getDisplayMinimalPrice();
            if ($showMinPrice && $_minimalPriceValue) {
                $_exclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue);
                $_inclTax = $_taxHelper->getPrice($_product, $_minimalPriceValue, true);
                $price = $showMinPrice ? $_minimalPriceValue : 0;
            } else {
                $price = $_convertedFinalPrice;
                $_exclTax = $_taxHelper->getPrice($_product, $price);
                $_inclTax = $_taxHelper->getPrice($_product, $price, true);
            }

            if ($price) {
                if ($showMinPrice) {
                    $priveV2['price_label'] = Mage::helper('catalog')->__('Starting at:');
                }
                if ($_taxHelper->displayBothPrices()) {
                    $priveV2['show_ex_in_price'] = 1;
                    $this->setBothTaxPrice($priveV2, $_exclTax, $_inclTax);
                } else {
                    $priveV2['show_ex_in_price'] = 0;
                    $_showPrice = $_inclTax;
                    if (!$_taxHelper->displayPriceIncludingTax()) {
                        $_showPrice = $_exclTax;
                    }
                    $this->setTaxPrice($priveV2, $_showPrice);
                }
            }
        }
        return $priveV2;
    }

    public function getDisplayMinimalPrice()
    {
        if ($this->_product)
            return $this->_product->getMinimalPrice();
        return 0;
    }

    /**
     * @param $price
     * @param $_price
     * show type
     * 3 show price only.
     * 4 show price - wee.
     * 5 show wee - price.
     */
    public function setTaxReguarlPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['regular_price'] = $_price;
    }

    /**
     * @param $price
     * @param $_price
     * show type
     * 3 show price only.
     * 4 show price - wee.
     * 5 show wee - price.
     */
    public function setTaxPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
       // $price['show_type'] = 3;
        $price['price'] = $_price;
    }

    public function setTaxLowPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['low_price'] = $_price;
    }
    /**
     * @param $price
     * @param $_exclTax
     * @param $_inclTax
     * type
     * 0 show price only
     * 1 show ex + wee + in
     * 2 show  ex + in + wee
     */
    public function setBothTaxPrice(&$price, $_exclTax, $_inclTax)
    {
        $price['price_excluding_tax'] = array(
            'label' => $this->helper('tax')->__('Excl. Tax:'),
            'price' => $_exclTax,
        );
        $price['price_including_tax'] = array(
            'label' => $this->helper('tax')->__('Incl. Tax:'),
            'price' => $_inclTax,
        );
    }

    public function setWeePrice(&$price, $wee)
    {
        $price['wee'] = $wee;
    }
}