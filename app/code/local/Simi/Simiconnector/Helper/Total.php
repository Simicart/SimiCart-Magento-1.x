<?php

/**

 */
class Simi_Simiconnector_Helper_Total extends Mage_Core_Helper_Abstract {

    public $data;

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

    /*
     * For Cart and OnePage Order
     */

    public function setTotal($total, &$data) {
        if (isset($total['shipping'])) {
            /*
             * tax_cart_display_shipping
             */
            $data['shipping_hand_incl_tax'] = $this->getShippingIncludeTax($total['shipping']);
            $data['shipping_hand_excl_tax'] = $this->getShippingExcludeTax($total['shipping']);
        }
        /*
         * tax_cart_display_zero_tax
         */
        if (isset($total['tax'])) {
            $data['tax'] = $total['tax']->getValue();
            $taxSumarry = array();
            foreach ($total['tax']->getFullInfo() as $info) {
                if (isset($info['hidden']) && $info['hidden'])
                    continue;
                $amount = $info['amount'];
                $rates = $info['rates'];
                foreach ($rates as $rate) {
                    $title = $rate['title'];
                    if (!is_null($rate['percent'])) {
                        $title.= ' (' . $rate['percent'] . '%)';
                    }
                    $taxSumarry[] = array('title' => $title,
                        'amount' => $amount,
                    );
                    /*
                     * SimiCart only show the first Rate for Each Item 
                     */
                    break;
                }
            }
            if (count($taxSumarry))
                $data['tax_summary'] = $taxSumarry;
        }

        if (isset($total['discount'])) {
            $data['discount'] = abs($total['discount']->getValue());
        }
        /*
         * tax_cart_display_subtotal
         */
        if ($this->displayTypeSubOrder() == 3) {
            $data['subtotal_excl_tax'] = $total['subtotal']->getValueExclTax();
            $data['subtotal_incl_tax'] = $total['subtotal']->getValueInclTax();
        } else if (($this->displayTypeSubOrder() == 1) && isset($data['tax'])) {
            $data['subtotal_excl_tax'] = $total['subtotal']->getValue();
            $data['subtotal_incl_tax'] = $data['subtotal_excl_tax'] + $data['tax'];
        } else if (($this->displayTypeSubOrder() == 2) && isset($data['tax'])) {
            $data['subtotal_incl_tax'] = $total['subtotal']->getValue();
            $data['subtotal_excl_tax'] = $data['subtotal_incl_tax'] - $data['tax'];
        }

        /*
         * tax_cart_display_grandtotal
         */
        $data['grand_total_excl_tax'] = $this->getTotalExclTaxGrand($total['grand_total']);
        $data['grand_total_incl_tax'] = $total['grand_total']->getValue();

        $coupon = '';
        if (Mage::getSingleton('checkout/session')->getQuote()->getCouponCode() && ($data['discount'] != 0)) {
            $coupon = Mage::getSingleton('checkout/session')->getQuote()->getCouponCode();
            $data['coupon_code'] = $coupon;
        }


        $this->data = $data;

        /*
         * For Phoenix COD fee adding (as Example as well)
         */
        if ((Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->getMethod()) && (Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment()->getMethodInstance()->getCode() == 'phoenix_cashondelivery')) {
            $codFee = Mage::getSingleton('checkout/type_onepage')->getQuote()->getCodTaxAmount() + Mage::getSingleton('checkout/type_onepage')->getQuote()->getCodFee();
            $this->addCustomRow(Mage::helper('phoenix_cashondelivery')->__('Cash on Delivery fee'), 4, $codFee);
        }

        Mage::dispatchEvent('simi_simiconnector_helper_total_settotal_after', array('object' => $this, 'data' => $this->data));
        $data = $this->data;
    }

    public function displayTypeSubOrder() {
        return Mage::getStoreConfig("tax/cart_display/subtotal");
    }

    /*
     * For Order History
     */

    public function showTotalOrder($order) {
        $data = array();
        $data['subtotal_excl_tax'] = $order->getSubtotal();
        $data['subtotal_incl_tax'] = $order->getSubtotalInclTax();
        if ($data['subtotal_incl_tax'] == null) {
            $data['subtotal_incl_tax'] = $order->getSubtotal() + $order->getTaxAmount();
        }
        $data['shipping_hand_excl_tax'] = $order->getShippingAmount();
        $data['shipping_hand_incl_tax'] = $order->getShippingInclTax();
        $data['tax'] = $order->getTaxAmount();
        $data['discount'] = abs($order->getDiscountAmount());
        $data['grand_total_excl_tax'] = $order->getGrandTotal() - $data['tax'];
        $data['grand_total_incl_tax'] = $order->getGrandTotal();

        if (Mage::app()->getLocale()->currency($order->getOrderCurrency()->getCurrencyCode())->getSymbol() != null) {
            $data['currency_symbol'] = Mage::app()->getLocale()->currency($order->getOrderCurrency()->getCurrencyCode())->getSymbol();
        } else {
            $data['currency_symbol'] = $order->getOrderCurrency()->getCurrencyCode();
        }
        return $data;
    }

    public function addCustomRow($title, $sortOrder, $value, $valueString = null) {
        if (isset($this->data['custom_rows']))
            $customRows = $this->data['custom_rows'];
        else
            $customRows = array();
        if (!$valueString)
            $customRows[] = array('title' => $title, 'sort_order' => $sortOrder, 'value' => $value);
        else
            $customRows[] = array('title' => $title, 'sort_order' => $sortOrder, 'value' => $value, 'value_string' => $valueString);
        $this->data['custom_rows'] = $customRows;
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
