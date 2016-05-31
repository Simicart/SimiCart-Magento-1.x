<?php

class Simi_Simiconnector_Model_Api_Quoteitems extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery() {
        $data = $this->getData();
        $cart = Mage::getModel('checkout/cart')->getQuote();
        zend_debug::dump($this->getCart());
        die;

        if ($data['resourceid']) {
            
        } else {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
            $visibilityTable = Mage::getSingleton('core/resource')->getTableName('simiconnector/visibility');
            $cmsCollection = Mage::getModel('simiconnector/cms')->getCollection();
            $cmsCollection->getSelect()
                    ->join(array('visibility' => $visibilityTable), 'visibility.item_id = main_table.cms_id AND visibility.content_type = ' . $typeID . ' AND visibility.store_view_id =' . Mage::app()->getStore()->getId());
            $this->builderQuery = $cmsCollection;
        }
    }

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    protected function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    public function getCart() {
        $list = array();
        $quote = $this->_getCheckoutSession()->getQuote();
        $allItems = $quote->getAllVisibleItems();
        zend_debug::dump($allItems->getData());
        foreach ($allItems as $item) {
            $product = $item->getProduct();
            $options = array();
            if (version_compare(Mage::getVersion(), '1.5.0.0', '>=') === true) {
                $helper = Mage::helper('catalog/product_configuration');
                if ($item->getProductType() == "simple") {
                    $options = Mage::helper('connector/checkout')->convertOptionsCart($helper->getCustomOptions($item));
                } elseif ($item->getProductType() == "configurable") {
                    $options = Mage::helper('connector/checkout')->convertOptionsCart($helper->getConfigurableOptions($item));
                } elseif ($item->getProductType() == "bundle") {
                    $options = Mage::helper('connector/checkout')->getOptions($item);
                }
            } else {
                if ($item->getProductType() != "bundle") {
                    $options = Mage::helper('connector/checkout')->getUsedProductOption($item);
                } else {
                    $options = Mage::helper('connector/checkout')->getOptions($item);
                }
            }

            $pro_price = $item->getCalculationPrice();
            if (Mage::helper('tax')->displayCartPriceInclTax() || Mage::helper('tax')->displayCartBothPrices()) {
                $pro_price = Mage::helper('checkout')->getSubtotalInclTax($item);
            }

            $list[] = array(
                'cart_item_id' => $item->getId(),
                'product_id' => $product->getId(),
                'stock_status' => $product->isSaleable(),
                'product_name' => $product->getName(),
                'product_price' => $pro_price,
                'product_image' => Mage::getSingleton('connector/catalog_product')->getImageProduct($product, null, $width, $height),
                'product_qty' => $item->getQty(),
                'options' => $options,
            );
        }
        $this->_getCheckoutSession()->getQuote()->collectTotals();
        $information = $this->statusSuccess();
        $information['data'] = $list;

        $event_name = $this->getControllerName() . '_total';
        $event_value = array(
            'object' => $this,
        );
        $other_total = array();
        $total = $this->_getCheckoutSession()->getQuote()->getTotals();
        //hai ta 2082014
        Mage::helper('connector/checkout')->setTotal($total, $other_total);
        //end haita 2082014
        $subTotal = $total['subtotal']->getValue();
        $information['message'] = array($subTotal);
        $data_change = $this->changeData($other_total, $event_name, $event_value);
        $other_total = $data_change;
        $information['other'] = $other_total;
        Mage::getModel('connector/checkout_cart')->checkItemCart($information);
        return $information;
    }

}
