<?php

/**

 */
class Simi_Simiconnector_Helper_Total extends Mage_Core_Helper_Abstract {

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    /*
     * Get Quote Price
     */

    public function getTotal() {
        $orderTotal = array();
        $total = $this->_getQuote()->getTotals();
        $this->setTotal($total, $orderTotal);
        return $orderTotal;
    }

    public function setTotal($total, &$data) {
        //tax_cart_display_subtotal
        //1 || 3
        $data['subtotal_excl_tax'] = $total['subtotal']->getValueExclTax();
        //2 || 3
        $data['subtotal_incl_tax'] = $total['subtotal']->getValueInclTax();

        /*
         * tax_cart_display_grandtotal
         */
        //0
        $data['grand_total_excl_tax'] = $this->getTotalExclTaxGrand($total['grand_total']);
        //1
        $data['grand_total_incl_tax'] = $total['grand_total']->getValue();


        /*
         * tax_cart_display_zero_tax
         */
        if (isset($total['tax'])) {
            $data['tax'] = $total['tax']->getValue();
        }

        if (isset($total['shipping'])) {
            /*
             * tax_cart_display_shipping
             */
            //1 || 3
            $data['shipping_hand_incl_tax'] = $this->getShippingIncludeTax($total['shipping']);
            //2 || 3
            $data['shipping_hand_excl_tax'] = $this->getShippingExcludeTax($total['shipping']);
        }

        if (isset($total['discount'])) {
            $data['discount'] = abs($total['discount']->getValue());
        }

        $coupon = '';
        if (Mage::getSingleton('checkout/session')->getQuote()->getCouponCode()) {
            $coupon = Mage::getSingleton('checkout/session')->getQuote()->getCouponCode();
            $data['coupon_code'] = $coupon;
        }
    }

    public function displayBothTaxSub() {
        return Mage::getSingleton('tax/config')->displayCartSubtotalBoth(Mage::app()->getStore());
    }

    public function includeTaxGrand($total) {
        if ($total->getAddress()->getGrandTotal()) {
            return Mage::getSingleton('tax/config')->displayCartTaxWithGrandTotal(Mage::app()->getStore());
        }
        return false;
    }

    public function getTotalExclTaxGrand($total) {
        $excl = $total->getAddress()->getGrandTotal() - $total->getAddress()->getTaxAmount();
        $excl = max($excl, 0);
        return $excl;
    }

    public function displayBothTaxShipping() {
        return Mage::getSingleton('tax/config')->displayCartShippingBoth(Mage::app()->getStore());
    }

    public function displayIncludeTaxShipping() {
        return Mage::getSingleton('tax/config')->displayCartShippingInclTax(Mage::app()->getStore());
    }

    public function getShippingIncludeTax($total) {
        return $total->getAddress()->getShippingInclTax();
    }

    public function getShippingExcludeTax($total) {
        return $total->getAddress()->getShippingAmount();
    }

}
