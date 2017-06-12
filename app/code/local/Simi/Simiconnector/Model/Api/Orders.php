<?php

class Simi_Simiconnector_Model_Api_Orders extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'entity_id';
    protected $_RETURN_MESSAGE;
    protected $_QUOTE_INITED = FALSE;
    public $detail_onepage;
    public $place_order;
    public $order_placed_info;

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function _getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function setBuilderQuery()
    {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            if ($data['resourceid'] == 'onepage') {

            } else {
                $this->builderQuery = Mage::getModel('sales/order')->load($data['resourceid']);
                if (!$this->builderQuery->getId()) {
                    $this->builderQuery = Mage::getModel('sales/order')->loadByIncrementId($data['resourceid']);
                }
                if (!$this->builderQuery->getId()) {
                    throw new Exception(Mage::helper('simiconnector')->__('Cannot find the Order'), 6);
                }
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

    public function update()
    {
        $data = $this->getData();
        if ($data['resourceid'] == 'onepage') {
            $this->_updateOrder();
            return $this->show();
        } else {
            $order = $this->builderQuery;
            $param = $data['contents'];
            $order_helper = Mage::helper('simiconnector/orders');
            $result = null;
            if ($param->status == 'cancel') {
                $result = $order_helper->cancelOrder($order);
            } elseif ($param->status == 'invoice') {
                $result = $order_helper->invoiceOrder($order);
            } elseif ($param->status == 'ship') {
                $result = $order_helper->shipOrder($order);
            } elseif ($param->status == 'hold') {
                $result = $order_helper->holdOrder($order);
            } elseif ($param->status == 'unhold') {
                $result = $order_helper->unHoldOrder($order);
            } else {
                $order->setState($param->status, true);
                $order->save();
            }
            if (null != $result) {
                $return_data = $this->show();
                $return_data[$this->getSingularKey()]['message'] = $result['message'];
                return $return_data;
            }
            return $this->show();
        }
    }

    private function _updateOrder()
    {
        $data = $this->getData();
        $parameters = (array)$data['contents'];

        if (isset($parameters['b_address'])) {
            $this->_initCheckout();
            Mage::helper('simiconnector/address')->saveBillingAddress($parameters['b_address']);
            if (!isset($parameters['s_address']))
                $parameters['s_address'] = $parameters['b_address'];
        }
        if (isset($parameters['s_address'])) {
            $this->_initCheckout();
            Mage::helper('simiconnector/address')->saveShippingAddress($parameters['s_address']);
        }

        if (isset($parameters['coupon_code'])) {
            $this->_RETURN_MESSAGE = Mage::helper('simiconnector/coupon')->setCoupon($parameters['coupon_code']);
        }
        if (isset($parameters['s_method'])) {
            Mage::helper('simiconnector/checkout_shipping')->saveShippingMethod($parameters['s_method']);
        }
        if (isset($parameters['p_method'])) {
            Mage::helper('simiconnector/checkout_payment')->savePaymentMethod($parameters['p_method']);
        }
        $this->_getOnepage()->getQuote()->collectTotals()->save();
    }

    private function _initCheckout()
    {
        if (!$this->_QUOTE_INITED) {
            $this->_getCheckoutSession()->setCartWasUpdated(false);
            $this->_getOnepage()->initCheckout();
            $this->_QUOTE_INITED = TRUE;
        }
    }

    /*
     * Place Order
     */

    public function store()
    {
        $this->_updateOrder();

        $this->place_order = TRUE;
        Mage::dispatchEvent('simi_simiconnector_model_api_orders_onepage_store_before', array('object' => $this, 'data' => $this->getData()));
        if (!$this->place_order) {
            $result = array('order' => $this->order_placed_info);
            return $result;
        }

        $quote = $this->_getQuote();
        if (!$quote->validateMinimumAmount()) {
            throw new Exception(Mage::getStoreConfig('sales/minimum_order/error_message'), 4);
        }
        $this->_getOnepage()->saveOrder();
        $this->_getOnepage()->getQuote()->save();
        $order = array('invoice_number' => $this->_getCheckoutSession()->getLastRealOrderId(),
            'payment_method' => $this->_getOnepage()->getQuote()->getPayment()->getMethodInstance()->getCode()
        );

        $incrementId = $this->_getCheckoutSession()->getLastRealOrderId();
        $orderId = Mage::getModel('sales/order')->loadByIncrementId($incrementId)->getId();
        Mage::helper('simiconnector/checkout')->processOrderAfter($orderId, $order);
        $this->order_placed_info = $order;
        Mage::dispatchEvent('simi_simiconnector_model_api_orders_onepage_store_after', array('object' => $this, 'data' => $order));
        $result = array('order' => $this->order_placed_info);
        $this->cleanSession();
        return $result;
    }

    /*
     * Return Order Detail (History and Onepage)
     */

    public function show()
    {
        $data = $this->getData();
        if ($data['resourceid'] == 'onepage') {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $quote = $this->_getQuote();
            $list_payment = array();
            /*
             * Get Detail Payment
             */
            $paymentHelper = Mage::helper('simiconnector/checkout_payment');
            foreach (Mage::helper('simiconnector/checkout_payment')->getMethods() as $method) {
                $list_payment[] = $paymentHelper->getDetailsPayment($method);
            }

            $order = array();
            $order['billing_address'] = Mage::helper('simiconnector/address')->getAddressDetail($quote->getBillingAddress(), $customer);
            $order['shipping_address'] = Mage::helper('simiconnector/address')->getAddressDetail($quote->getShippingAddress(), $customer);
            $order['shipping'] = Mage::helper('simiconnector/checkout_shipping')->getMethods();
            $order['payment'] = $list_payment;
            $order['total'] = Mage::helper('simiconnector/total')->getTotal();
            $detail_onepage = array('order' => $order);
            if ($this->_RETURN_MESSAGE) {
                $detail_onepage['message'] = array($this->_RETURN_MESSAGE);
            }
            $this->detail_onepage = $detail_onepage;
            Mage::dispatchEvent('simi_simiconnector_model_api_orders_onepage_show_after', array('object' => $this, 'data' => $this->detail_onepage));
            return $this->detail_onepage;
        } else {
            $result = parent::show();
            if ($data['params']['reorder'] == 1) {
                $order = Mage::getModel('sales/order')->load($data['resourceid']);
                $cart = Mage::getSingleton('checkout/cart');
                $items = $order->getItemsCollection();
                foreach ($items as $item) {
                    $cart->addOrderItem($item);
                }
                $cart->save();
                $result['message'] = Mage::helper('simiconnector')->__('Reorder Succeeded');
            }
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

    public function index()
    {
        $result = parent::index();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        foreach ($result['orders'] as $index => $order) {
            $this->_updateOrderInformation($order, $customer);
            $result['orders'][$index] = $order;
        }
        return $result;
    }

    private function _updateOrderInformation(&$order, $customer)
    {
        $orderModel = Mage::getModel('sales/order')->load($order['entity_id']);
        $order['payment_method'] = $orderModel->getPayment()->getMethodInstance()->getTitle();
        $order['shipping_method'] = $orderModel->getShippingDescription();
        $order['billing_address'] = Mage::helper('simiconnector/address')->getAddressDetail($orderModel->getBillingAddress(), $customer);
        if (!$orderModel->getShippingAddress())
            $order['shipping_address'] = $order['billing_address'];
        else
            $order['shipping_address'] = Mage::helper('simiconnector/address')->getAddressDetail($orderModel->getShippingAddress(), $customer);
        $order['order_items'] = $this->_getProductFromOrderHistoryDetail($orderModel);
        $order['total'] = Mage::helper('simiconnector/total')->showTotalOrder($orderModel);
    }

    public function _getProductFromOrderHistoryDetail($order)
    {
        $productInfo = array();
        $itemCollection = $order->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            $options = array();
            if ($item->getProductOptions()) {
                $options = $this->_getOptions($item->getProductType(), $item->getProductOptions());
            }
            $product_id = $item->getProductId();
            $product = $item->getProduct();
            if (version_compare(Mage::getVersion(), '1.7.0.0', '<') === true) {
                $product = Mage::getModel('catalog/product')->load($product_id);
            }
            $productInfo[] = array_merge(array('option' => $options), $item->toArray(), array('image' => Mage::helper('simiconnector/products')->getImageProduct($item->getProduct()))
            );
        }

        return $productInfo;
    }

    public function _getOptions($type, $options)
    {
        $list = array();
        if ($type == 'bundle') {
            foreach ($options['bundle_options'] as $option) {
                foreach ($option['value'] as $value) {
                    $list[] = array(
                        'option_title' => $option['label'],
                        'option_value' => $value['title'],
                        'option_price' => $value['price'],
                    );
                }
            }
        } else {
            $options = array();
            $optionsList = array();
            if (isset($options['additional_options'])) {
                $optionsList = $options['additional_options'];
            } elseif (isset($options['attributes_info'])) {
                $optionsList = $options['attributes_info'];
            } elseif (isset($options['options'])) {
                $optionsList = $options['options'];
            }
            foreach ($optionsList as $option) {
                $list[] = array(
                    'option_title' => $option['label'],
                    'option_value' => $option['value'],
                    'option_price' => isset($option['price']) == true ? $option['price'] : 0,
                );
            }
        }
        return $list;
    }

    public function cleanSession()
    {
        $session = $this->_getOnepage()->getCheckout();
        $lastOrderId = $session->getLastOrderId();
        $session->clear();
        Mage::dispatchEvent('simiconnector_checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
    }

}
