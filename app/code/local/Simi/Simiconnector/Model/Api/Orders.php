<?php

class Simi_Simiconnector_Model_Api_Orders extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'entity_id';
    protected $_RETURN_MESSAGE;

    /*
      protected function _getSession() {
      return Mage::getSingleton('checkout/session');
      }

      protected function _getCart() {
      return Mage::getSingleton('checkout/cart');
      }

      protected function _getQuote() {
      return $this->_getCart()->getQuote();
      }
     */

    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            if ($data['resourceid'] == 'onepage') {
                die;
            } else {
                $this->builderQuery = Mage::getModel('sales/order')->load($data['resourceid']);
            }
        } else {
            $this->builderQuery = Mage::getModel('sales/order')->getCollection()
                    ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
                    ->setOrder('entity_id', 'DESC');
        }
    }

    public function update() {
        
    }

    public function store() {
        
    }

    public function show() {
        $result = parent::show();
        $order = $result['order'];
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $this->_updateOrderInformation($order, $customer);
        $result['order'] = $order;
        return $result;
    }

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
        $order['shipping_address'] = Mage::getModel('simiconnector/address')->getAddressDetail($orderModel->getShippingAddress(), $customer);
        $order['billing_address'] = Mage::getModel('simiconnector/address')->getAddressDetail($orderModel->getBillingAddress(), $customer);
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

    public function getList($info, $all_ids, $total, $page_size, $from) {
        $result = parent::getList($info, $all_ids, $total, $page_size, $from);
        if ($this->_RETURN_MESSAGE) {
            $result['message'] = array($this->_RETURN_MESSAGE);
        }
        return $result;
    }

}
