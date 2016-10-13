<?php

class Simi_Simiconnector_Helper_Checkout_Shipping extends Mage_Core_Helper_Abstract {

    public function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function _getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function saveShippingMethod($method_code) {
        $this->_getOnepage()->saveShippingMethod($method_code->method);
    }

    public function getAddress() {
        return $this->_getCheckoutSession()->getShippingAddress();
    }

    public function getShippingPrice($price, $flag) {
        return $this->_getCheckoutSession()->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), false);
    }

    public function getMethods() {
        $shipping = $this->_getCheckoutSession()->getQuote()->getShippingAddress();
        $shipping->collectShippingRates();
        $methods = $shipping->getGroupedAllShippingRates();

        $list = array();
        foreach ($methods as $_ccode => $_carrier) {
            foreach ($_carrier as $_rate) {
                if ($_rate->getData('error_message') != NULL) {
                    continue;
                }
                $select = false;
                if ($shipping->getShippingMethod() != null && $shipping->getShippingMethod() == $_rate->getCode()) {
                    $select = true;
                }

                $s_fee = $this->getShippingPrice($_rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax());
                $s_fee_incl = $this->getShippingPrice($_rate->getPrice(), true);

                if (Mage::helper('tax')->displayShippingBothPrices() && $s_fee != $s_fee_incl) {
                    $list[] = array(
                        's_method_id' => $_rate->getId(),
                        's_method_code' => $_rate->getCode(),
                        's_method_title' => $_rate->getCarrierTitle(),
                        's_method_fee' => Mage::app()->getStore()->convertPrice(floatval($s_fee), false),
                        's_method_fee_incl_tax' => $s_fee_incl,
                        's_method_name' => $_rate->getMethodTitle(),
                        's_method_selected' => $select,
                    );
                } else {
                    $list[] = array(
                        's_method_id' => $_rate->getId(),
                        's_method_code' => $_rate->getCode(),
                        's_method_title' => $_rate->getCarrierTitle(),
                        's_method_fee' => $s_fee,
                        's_method_name' => $_rate->getMethodTitle(),
                        's_method_selected' => $select,
                    );
                }
            }
        }
        $this->_getCheckoutSession()->getQuote()->collectTotals()->save();
        return $list;
    }

}
