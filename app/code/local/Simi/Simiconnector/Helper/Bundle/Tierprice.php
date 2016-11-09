<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 11/9/16
 * Time: 1:29 PM
 */
class Simi_Simiconnector_Helper_Bundle_Tierprice extends Simi_Simiconnector_Helper_Tierprice
{
    public function formatTierPrice($product) {
        $data = array();
        $_product = $product;
        $_tierPrices = $this->getTierPrices($_product);
        if (count($_tierPrices) > 0) {
            $stringHt = '';
            foreach ($_tierPrices as $_price) {
                $stringHt = Mage::helper('bundle')->__('Buy %1$s with %2$s discount each', $_price['price_qty'], ($_price['price'] * 1) . '%');
                $data[] = $stringHt;
            }
        }
        return $data;
    }
}