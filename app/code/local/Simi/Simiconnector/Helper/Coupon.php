<?php

class Simi_Simiconnector_Helper_Coupon extends Mage_Core_Helper_Abstract {

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }


    public function setCoupon($couponCode) {
        $this->_getCart()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->_getCart()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
        $total = $this->_getCart()->getQuote()->getTotals();
        $return['discount'] = 0;
        if ($total['discount'] && $total['discount']->getValue()) {
            $return['discount'] = abs($total['discount']->getValue());
        }
        if (strlen($couponCode)) {
            if ($couponCode == $this->_getCart()->getQuote()->getCouponCode() && $return['discount'] != 0) {
                $message = Mage::helper('simiconnector')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
            } else {
                $message = Mage::helper('simiconnector')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
            }
        } else {
            $message = Mage::helper('simiconnector')->__('Coupon code was canceled.', Mage::helper('core')->htmlEscape($couponCode));
        }
        return $message;
    }

}
