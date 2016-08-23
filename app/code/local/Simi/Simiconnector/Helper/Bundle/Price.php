<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/20/16
 * Time: 8:52 AM
 */
class Simi_Simiconnector_Helper_Bundle_Price extends Mage_Core_Helper_Abstract
{
    protected $_product = null;
    protected $_minimalPriceTax = null;
    protected $_minimalPriceInclTax = null;

    public function helper($helper)
    {
        return Mage::helper($helper);
    }

    public function getProductAttribute($attribute) {
        return $this->_product->getResource()->getAttribute($attribute);
    }

    public function displayBothPrices()
    {
        $product = $this->_product;
        if ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC &&
            $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false) {
            return false;
        }
        return $this->helper('tax')->displayBothPrices();
    }

    public function formatPriceFromProduct($_product, $is_detail = false)
    {
        $priceV2 = array();
        $this->_product = $_product;
        /**
         * @var $_coreHelper Mage_Core_Helper_Data
         * @var $_weeeHelper Mage_Weee_Helper_Data
         * @var $_taxHelper Mage_Tax_Helper_Data
         */
        $_coreHelper = $this->helper('core');
        $_weeeHelper = $this->helper('weee');
        $_taxHelper  = $this->helper('tax');

        /**
         * @var $_product Mage_Catalog_Model_Product
         * @var $_priceModel Mage_Bundle_Model_Product_Price
         */
        $_priceModel  = $_product->getPriceModel();

        list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($_product, null, null, false);
        list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($_product, null, true, false);

        $_weeeTaxAmount = 0;

        if ($_product->getPriceType() == 1) {
            $_weeeTaxAmount = $_weeeHelper->getAmountForDisplay($_product);
            $_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
            if ($_weeeHelper->isTaxable()) {
                $_attributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
                $_weeeTaxAmountInclTaxes = $_weeeHelper->getAmountInclTaxes($_attributes);
            }
            if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, array(0, 1, 4))) {
                $_minimalPriceTax     += $_weeeTaxAmount;
                $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                $_maximalPriceTax     += $_weeeTaxAmount;
                $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
            }
            if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($_product, 2)) {
                $_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
                $_maximalPriceInclTax += $_weeeTaxAmountInclTaxes;
            }

            if ($_weeeHelper->typeOfDisplay($_product, array(1, 2, 4))) {
                $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
            }
        }
        if ($_product->getPriceView()){
            $priceV2['price_label'] = Mage::helper('catalog')->__('As low as');
            $priceV2['minimal_price'] = 1;
            if ($this->displayBothPrices()){
                $priceV2['show_ex_in_price'] = 1;
                $this->setBothTaxPrice($priceV2, $_minimalPriceTax, $_minimalPriceInclTax);
                if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                    $wee = '';

                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                        if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                            $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                        }else{
                            $amount = $_weeeTaxAttribute->getAmount();
                        }
                        $wee .= $_weeeTaxAttribute->getName();;
                        $wee .= ": ";
                        $wee .= $_coreHelper->currency($amount, true, false);
                        $wee .= " + ";
                        $priceV2["weee"] = $wee;
                    }
                    $this->setWeePrice($priceV2, $wee);
                    $priceV2['show_weee_price'] = 1;
                }
            }else{
                $priceV2['show_ex_in_price'] = 0;
                if ($_taxHelper->displayPriceIncludingTax()){
                    $this->setTaxPrice($priceV2, $_minimalPriceInclTax);
                }else{
                    $this->setTaxPrice($priceV2, $_minimalPriceTax);
                }
                if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                    $wee = '';
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                        if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                            $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                        }else{
                            $amount = $_weeeTaxAttribute->getAmount();
                        }
                        $wee .= $_weeeTaxAttribute->getName();;
                        $wee .= ": ";
                        $wee .= $_coreHelper->currency($amount, true, false);
                        $wee .= " + ";
                        $priceV2["weee"] = $wee;
                    }
                    $this->setWeePrice($priceV2, $wee);
                    $priceV2['show_weee_price'] = 1;
                }
                if ($_weeeHelper->typeOfDisplay($_product, 2) && $_weeeTaxAmount){
                    $this->setTaxPriceIn($priceV2, $_minimalPriceInclTax);
                }
            }
        }else{
            $priceV2['minimal_price'] = 0;
            if ($_minimalPriceTax <> $_maximalPriceTax){
                $priceV2['product_from_label'] = $this->helper('catalog')->__('From') ;
                $priceV2['product_to_label'] = $this->helper('catalog')->__('To') ;
                $priceV2['show_from_to_tax_price'] = 1;
                if ($this->displayBothPrices()){
                    $priceV2['show_ex_in_price'] = 1;
                    $this->setBothTaxFromPrice($priceV2, $_minimalPriceTax, $_minimalPriceInclTax);
                    $this->setBothTaxToPrice($priceV2, $_maximalPriceTax, $_maximalPriceInclTax);
                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';

                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $_coreHelper->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee_from"] = $wee;
                            $priceV2["weee_to"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                }else{
                    $priceV2['show_ex_in_price'] = 0;
                    if ($_taxHelper->displayPriceIncludingTax()){
                        $this->setTaxFromPrice($priceV2, $_minimalPriceInclTax);
                        $this->setTaxToPrice($priceV2, $_maximalPriceInclTax);
                    }else{
                        $this->setTaxFromPrice($priceV2, $_minimalPriceTax);
                        $this->setTaxToPrice($priceV2, $_maximalPriceTax);
                    }

                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $_coreHelper->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                    if ($_weeeHelper->typeOfDisplay($_product, 2) && $_weeeTaxAmount){
                        $this->setTaxFromPrice($priceV2, $_minimalPriceInclTax);
                        $this->setTaxToPrice($priceV2, $_maximalPriceInclTax);
                    }
                }
                //to price
            }else{
                //not show from and to with tax
                $priceV2['show_from_to_tax_price'] = 0;
                if ($this->displayBothPrices()){
                    $priceV2['show_ex_in_price'] = 1;
                    $priceV2['product_from_label'] = $this->helper('catalog')->__('From') ;
                    $priceV2['product_to_label'] = $this->helper('catalog')->__('To') ;

                    $this->setTaxFromPrice($priceV2, $_minimalPriceTax);
                    $this->setTaxToPrice($priceV2, $_minimalPriceInclTax);

                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $_coreHelper->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                }else{
                    $this->setTaxPrice($priceV2, $_minimalPriceTax);
                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $_coreHelper->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                    if ($_weeeHelper->typeOfDisplay($_product, 2) && $_weeeTaxAmount){
                        if ($_taxHelper->displayPriceIncludingTax()){
                            $this->setTaxPrice($priceV2, $_minimalPriceInclTax);
                        }else{
                            $this->setTaxPrice($priceV2, $_minimalPriceTax + $_weeeTaxAmount);
                        }
                    }
                }
            }
        }
        if($is_detail){
            $this->_minimalPriceInclTax = $_minimalPriceInclTax;
            $this->_minimalPriceTax = $_minimalPriceTax;
            $priceV2['configure'] = $this->formatPriceFromProductDetail($_product);
        }
        return $priceV2;
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
    public function setTaxPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['price'] = $_coreHelper->currency($_price, false, false);
    }

    public function setTaxPriceIn(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['price_in'] = $_coreHelper->currency($_price, false, false);
    }

    public function setTaxFromPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['from_price'] = $_coreHelper->currency($_price, false, false);
    }

    public function setTaxToPrice(&$price, $_price)
    {
        $_coreHelper = $this->helper('core');
        // $price['show_type'] = 3;
        $price['to_price'] = $_coreHelper->currency($_price, false, false);
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
        $_coreHelper = $this->helper('core');
        $price['price_excluding_tax'] = array(
            'label' => $this->helper('tax')->__('Excl. Tax'),
            'price' => $_coreHelper->currency($_exclTax, false, false),
        );
        $price['price_including_tax'] = array(
            'label' => $this->helper('tax')->__('Incl. Tax'),
            'price' => $_coreHelper->currency($_inclTax, false, false),
        );
    }

    public function setBothTaxFromPrice(&$price, $_exclTax, $_inclTax)
    {
        $_coreHelper = $this->helper('core');
        $price['from_price_excluding_tax'] = array(
            'label' => $this->helper('tax')->__('Excl. Tax'),
            'price' => $_coreHelper->currency($_exclTax, false, false),
        );
        $price['from_price_including_tax'] = array(
            'label' => $this->helper('tax')->__('Incl. Tax'),
            'price' => $_coreHelper->currency($_inclTax, false, false),
        );
    }

    public function setBothTaxToPrice(&$price, $_exclTax, $_inclTax)
    {
        $_coreHelper = $this->helper('core');
        $price['to_price_excluding_tax'] = array(
            'label' => $this->helper('tax')->__('Excl. Tax'),
            'price' => $_coreHelper->currency($_exclTax, false, false),
        );
        $price['to_price_including_tax'] = array(
            'label' => $this->helper('tax')->__('Incl. Tax'),
            'price' => $_coreHelper->currency($_inclTax, false, false),
        );
    }

    public function setWeePrice(&$price, $wee)
    {
        $price['wee'] = $wee;
    }

    public function formatPriceFromProductDetail($_product){
        $priceV2 = array();
        $_weeeHelper = $this->helper('weee');
        $_finalPrice = $_product->getFinalPrice() > $this->_minimalPriceTax ? $this->_minimalPriceTax : $_product->getFinalPrice();
        $_finalPriceInclTax = $_product->getFinalPrice()> $this->_minimalPriceInclTax ? $this->_minimalPriceInclTax : $_product->getFinalPrice();
        $_weeeTaxAmount = 0;

        if ($_product->getPriceType() == 1) {
            $_weeeTaxAmount = Mage::helper('weee')->getAmount($_product);
            if (Mage::helper('weee')->typeOfDisplay($_product, array(1,2,4))) {
                $_weeeTaxAttributes = Mage::helper('weee')->getProductWeeeAttributesForRenderer($_product, null, null, null, true);
            }
        }
     //   $isMAPTypeOnGesture = Mage::helper('catalog')->isShowPriceOnGesture($_product);
        $isMAPTypeOnGesture = true;
        $canApplyMAP  = Mage::helper('catalog')->canApplyMsrp($_product);
        if ($_product->getCanShowPrice() !== false){
            $priceV2['product_label'] = $this->helper('bundle')->__('Price as configured');
            if ($isMAPTypeOnGesture) {
                if ($this->helper('tax')->displayBothPrices()){
                    $priceV2['show_ex_in_price'] = 1;
                    if (!$canApplyMAP){
                        $this->setBothTaxPrice($priceV2, $_finalPrice, $_finalPriceInclTax);
                    }
                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $this->helper('core')->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                }else{
                    if (!$canApplyMAP){
                        $this->setTaxPrice($priceV2, $_finalPrice);
                    }

                    if ($_weeeTaxAmount && $_product->getPriceType() == 1 && $_weeeHelper->typeOfDisplay($_product, array(2, 1, 4))){
                        $wee = '';
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            if ($_weeeHelper->typeOfDisplay($_product, array(2, 4))){
                                $amount = $_weeeTaxAttribute->getAmount()+$_weeeTaxAttribute->getTaxAmount();
                            }else{
                                $amount = $_weeeTaxAttribute->getAmount();
                            }
                            $wee .= $_weeeTaxAttribute->getName();;
                            $wee .= ": ";
                            $wee .= $this->helper('core')->currency($amount, true, false);
                            $wee .= " + ";
                            $priceV2["weee"] = $wee;
                        }
                        $this->setWeePrice($priceV2, $wee);
                        $priceV2['show_weee_price'] = 1;
                    }
                }
            }
        }
        return $priceV2;
    }
}