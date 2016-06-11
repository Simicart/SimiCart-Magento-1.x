<?php

class Simi_Simiconnector_Model_Api_Orders extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'entity_id';
    protected $_RETURN_MESSAGE;

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            if ($data['resourceid'] == 'onepage') {
                
            } else {
                $this->builderQuery = Mage::getModel('sales/order')->load($data['resourceid']);
            }
        } else {
            $this->builderQuery = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                    ->setOrder('entity_id', 'DESC');
        }
    }

    /*
     * Update Checkout Order (onepage) Information
     */

    public function update() {
        $data = $this->getData();
        $parameters = (array) $data['contents'];
        if (isset($parameters['coupon_code'])) {
            $this->_RETURN_MESSAGE = Mage::helper('simiconnector/coupon')->setCoupon($parameters['coupon_code']);
        }
        if (isset($parameters['s_method_code'])) {
            Mage::helper('simiconnector/checkout_shipping')->saveShippingMethod($parameters['s_method_code']);
        }
        if (isset($parameters['p_method'])) {
            Mage::helper('simiconnector/checkout_payment')->savePaymentMethod($parameters['p_method']);
        }
        return $this->show();
    }

    /*
     * Place Order
     */

    public function store() {
        
    }

    /*
     * Return Order Detail (History and Onepage)
     */

    public function show() {
        $data = $this->getData();
        if ($data['resourceid'] == 'onepage') {
            $result = array();



            $list_shipping = Mage::helper('simiconnector/checkout_shipping')->getMethods();

            $list_payment = array();
            $paymentHelper = Mage::helper('simiconnector/checkout_payment');
            foreach (Mage::helper('simiconnector/checkout_payment')->getMethods() as $method) {
                $list_payment[] = $paymentHelper->getDetailsPayment($method);
            }
            $order = array();
            $quote = $this->_getQuote();
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $order['billing_address'] = Mage::helper('simiconnector/address')->getAddressDetail($quote->getBillingAddress(), $customer);
            $order['shipping_address'] = Mage::helper('simiconnector/address')->getAddressDetail($quote->getShippingAddress(), $customer);
            $order['shipping'] = $list_shipping;
            $order['payment'] = $list_payment;
            $order['total'] = Mage::helper('simiconnector/total')->getTotal();
            $result = array('order' => $order);
            return $result;
        } else {
            $result = parent::show();
            $order = $result['order'];
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $this->_updateOrderInformation($order, $customer);
            $result['order'] = $order;
            return $result;
        }
    }

    /*
     * Order History
     */

    public function index() {
        $result = parent::index();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        foreach ($result['orders'] as $index => $order) {
            $this->_updateOrderInformation($order, $customer);
            $result['orders'][$index] = $order;
        }
        return $result;
    }

    private function _updateOrderInformation(&$order, $customer) {
        $orderModel = Mage::getModel('sales/order')->load($order['entity_id']);
        $order['payment_method'] = $orderModel->getPayment()->getMethodInstance()->getTitle();
        $order['shipping_method'] = $orderModel->getShippingDescription();
        $order['shipping_address'] = Mage::helper('simiconnector/address')->getAddressDetail($orderModel->getShippingAddress(), $customer);
        $order['billing_address'] = Mage::helper('simiconnector/address')->getAddressDetail($orderModel->getBillingAddress(), $customer);
        $order['order_items'] = $this->_getProductFromOrderList($orderModel->getAllVisibleItems());
        $order['total'] = Mage::helper('simiconnector/total')->showTotalOrder($orderModel);
    }

    private function _getProductFromOrderList($itemCollection) {
        $productInfo = array();
        foreach ($itemCollection as $item) {
            $productInfo[] = $item->toArray();
        }
        return $productInfo;
    }

    /*
     * Add Message
     */

    public function getList($info, $all_ids, $total, $page_size, $from) {
        $result = parent::getList($info, $all_ids, $total, $page_size, $from);
        if ($this->_RETURN_MESSAGE) {
            $result['message'] = array($this->_RETURN_MESSAGE);
        }
        return $result;
    }

}
