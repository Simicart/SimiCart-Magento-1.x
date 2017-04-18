<?php

class Simi_Simiconnector_Model_Api_Quoteitems extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'item_id';
    protected $_RETURN_MESSAGE;
    protected $_removed_items;
    public $detail_list;

    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    public function setBuilderQuery() {
        $quote = $this->_getQuote();
        $this->builderQuery = $quote->getItemsCollection();
    }

    /*
     * Change Qty, Add/remove Coupon Code
     */

    public function update() {
        $data = $this->getData();
        $parameters = (array) $data['contents'];
        if (isset($parameters['coupon_code'])) {
            $this->_RETURN_MESSAGE = Mage::helper('simiconnector/coupon')->setCoupon($parameters['coupon_code']);
        }
        $this->_updateItems($parameters);
        return $this->index();
    }

    private function _updateItems($parameters) {
        $cartData = array();
        foreach ($parameters as $index => $qty) {
            $cartData[$index] = array('qty' => $qty);
        }
        if (count($cartData)) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            $removedItems = array();
            foreach ($cartData as $index => $data) {
                if (isset($data['qty'])) {
                    $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    if ($data['qty'] == 0) {
                        $removedItems[] = $index;
                    }
                }
            }
            $this->_removed_items = $removedItems;
            $cart = $this->_getCart();
            if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                $cart->getQuote()->setCustomerId(null);
            }

            if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
                $cartData = $cart->suggestItemsQty($cartData);
            }
            $cart->updateItems($cartData)
                    ->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        }
    }

    /*
     * Add To Cart
     */

    public function store() {
        $this->addToCart();
        return $this->index();
    }

    public function addToCart() {
        $data = $this->getData();
        $cart = $this->_getCart();

        $controller = $data['controller'];
        $contents = $controller->getRequest()->getRawBody(); // using without GET method
        if ($contents && strlen($contents)) {
            $contents = urldecode($contents);
            $params = json_decode($contents, true);
        }
        $params = $this->convertParams($params);
        if (isset($params['qty'])) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            $params['qty'] = $filter->filter($params['qty']);
        }
        $product = $this->_initProduct($params['product']);
        $cart->addProduct($product, $params);
        $cart->save();
        $this->_getSession()->setCartWasUpdated(true);
        Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => Mage::app()->getRequest(), 'response' => Mage::app()->getResponse()));
        $this->_RETURN_MESSAGE = Mage::helper('simiconnector')->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
    }

    public function convertParams($params) {
        $convertList = array(
            //Custom Option (Simple/Virtual/Downloadable)
            'options',
            //Configurable Product
            'super_attribute',
            //Group Product
            'super_group',
            //Bundle Product
            'bundle_option',
            //Bundle Product Qty
            'bundle_option_qty',
        );
        foreach ($convertList as $type) {
            if (!isset($params[$type])) {
                continue;
            }
            $params[$type] = (array) $params[$type];
            $convertedParam = array();
            foreach ($params[$type] as $index => $item) {
                $convertedParam[(int) $index] = $item;
            }
            $params[$type] = $convertedParam;
        }
        return $params;
    }

    protected function _initProduct($productId) {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /*
     * Return Cart Detail
     */

    public function show() {
        return $this->index();
    }

    public function index() {
        $this->_getQuote()->collectTotals()->save();
        $collection = $this->builderQuery;
        $collection->addFieldToFilter('item_id', array('nin' => $this->_removed_items))
                ->addFieldToFilter('parent_item_id', array('null' => true));

        $this->filter();
        $data = $this->getData();
        $parameters = $data['params'];
        $page = 1;
        if (isset($parameters[self::PAGE]) && $parameters[self::PAGE]) {
            $page = $parameters[self::PAGE];
        }

        $limit = self::DEFAULT_LIMIT;
        if (isset($parameters[self::LIMIT]) && $parameters[self::LIMIT]) {
            $limit = $parameters[self::LIMIT];
        }

        $offset = $limit * ($page - 1);
        if (isset($parameters[self::OFFSET]) && $parameters[self::OFFSET]) {
            $offset = $parameters[self::OFFSET];
        }
        $collection->setPageSize($offset + $limit);

        $all_ids = array();
        $info = array();
        $total = $collection->getSize();

        if ($offset > $total) {
            throw new Exception($this->_helper->__('Invalid method.'), 4);
        }

        $fields = array();
        if (isset($parameters['fields']) && $parameters['fields']) {
            $fields = explode(',', $parameters['fields']);
        }

        $check_limit = 0;
        $check_offset = 0;

        /*
         * Add options and image
         */
        foreach ($collection as $entity) {
            if (++$check_offset <= $offset) {
                continue;
            }
            /*
			if (++$check_limit > $limit)
                break;

			*/
            if ($entity->getData('parent_item_id') != NULL)
                continue;

            if ($this->_removed_items) {
                if (in_array($entity->getData('item_id'), $this->_removed_items)) {
                    continue;
                }
            }

            $options = array();
            if (version_compare(Mage::getVersion(), '1.5.0.0', '>=') === true) {
                $helper = Mage::helper('catalog/product_configuration');
                if ($entity->getProductType() == "simple") {
                    $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getCustomOptions($entity));
                } elseif ($entity->getProductType() == "configurable") {
                    $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getConfigurableOptions($entity));
                } elseif ($entity->getProductType() == "bundle") {
                    $options = Mage::helper('simiconnector/checkout')->getOptions($entity);
                }
            } else {
                if ($entity->getProductType() != "bundle") {
                    $options = Mage::helper('simiconnector/checkout')->getUsedProductOption($entity);
                } else {
                    $options = Mage::helper('simiconnector/checkout')->getOptions($entity);
                }
            }

            $pro_price = $entity->getCalculationPrice();
            if (Mage::helper('tax')->displayCartPriceInclTax() || Mage::helper('tax')->displayCartBothPrices()) {
                $pro_price = Mage::helper('checkout')->getSubtotalInclTax($entity);
            }

            $quoteitem = $entity->toArray($fields);
            $quoteitem['option'] = $options;
			if (isset($parameters['image_width'])){
				$image_width = $parameters['image_width'];
				$image_height = $parameters['image_height'];
			} else {
				$image_width = 600;
				$image_height = 600;
			}
            $quoteitem['image'] = Mage::helper('simiconnector/products')->getImageProduct($entity->getProduct(), null, $image_width, $image_height);
            $info[] = $quoteitem;
            $all_ids[] = $entity->getId();
        }

        $this->detail_list = $this->getList($info, $all_ids, $total, $limit, $offset);
        Mage::dispatchEvent('simi_simiconnector_model_api_quoteitems_index_after', array('object' => $this, 'data' => $this->detail_list));
        return $this->detail_list;
    }

    /*
     * Add Message
     */

    public function getList($info, $all_ids, $total, $page_size, $from) {
        $result = parent::getList($info, $all_ids, $total, $page_size, $from);
        $result['total'] = Mage::helper('simiconnector/total')->getTotal();
        if ($this->_RETURN_MESSAGE) {
            $result['message'] = array($this->_RETURN_MESSAGE);
        }
        $session = Mage::getSingleton('checkout/session');
        $result['cart_total'] = Mage::helper('checkout/cart')->getItemsCount();
        $result['quote_id'] = $session->getQuoteId();
        return $result;
    }

}
