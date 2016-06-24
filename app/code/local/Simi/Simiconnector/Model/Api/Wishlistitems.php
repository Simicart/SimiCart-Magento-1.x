<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Wishlistitems extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'wishlist_item_id';
    protected $_RETURN_MESSAGE;
    protected $_WISHLIST;

    public function setBuilderQuery() {
        $data = $this->getData();

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId() && ($customer->getId() != '')) {
            $this->_WISHLIST = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);

            $sharingUrl = $this->_WISHLIST->getSharingCode();
            $this->_RETURN_MESSAGE = Mage::getStoreConfig('appwishlist/general/sharing_message') . ' ' . Mage::getUrl('wishlist/shared/index/code/' . $sharingUrl);
        } else
            throw new Exception(Mage::helper('customer')->__('Please login First.'), 4);
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('wishlist/item')->load($data['resourceid']);
        } else {
            $this->builderQuery = $this->_WISHLIST->getItemCollection();
        }
    }

    public function index() {
        $result = parent::index();
        $addition_info = array();
        foreach ($this->builderQuery as $itemModel) {
            $product = $itemModel->getProduct();
            $isSaleAble = $product->isSaleable();
            if ($isSaleAble) {
                $itemOptions = Mage::getModel('wishlist/item_option')->getCollection()
                        ->addItemFilter(array($itemModel->getData('wishlist_item_id')));
                foreach ($itemOptions as $itemOption) {
                    $optionProduct = Mage::getModel('catalog/product')->load($itemOption->getProductId());
                    if (!$optionProduct->isSaleable()) {
                        $isSaleAble = false;
                        break;
                    }
                }
            }

            $productSharingMessage = implode(' ', array(Mage::getStoreConfig('simiconnector/wishlist/product_sharing_message'), $product->getProductUrl()));
            $options = Mage::helper('simiconnector/wishlist')->getOptionsSelectedFromItem($itemModel, $product);
            $addition_info[$itemModel->getData('wishlist_item_id')] = array(
                'product_type' => $product->getTypeId(),
                'product_regular_price' => Mage::app()->getStore()->convertPrice($product->getPrice(), false),
                'product_price' => Mage::app()->getStore()->convertPrice($product->getFinalPrice(), false),
                'stock_status' => $isSaleAble,
                'product_image' => Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize($width, $height)->__toString(),
                'is_show_price' => true,
                'options' => $options,
                'selected_all_required_options' => Mage::helper('simiconnector/wishlist')->checkIfSelectedAllRequiredOptions($itemModel, $options),
                'product_sharing_message' => $productSharingMessage,
                'product_sharing_url' => $product->getProductUrl(),
            );
        }
        foreach ($result['wishlistitems'] as $index => $item) {
            $result['wishlistitems'][$index] = array_merge($item, $addition_info[$item['wishlist_item_id']]);
        }
        return $result;
    }

    /*
     * Add To Wishlist
     */

    public function store() {
        $data = $this->getData();
        $params = Mage::getModel('simiconnector/api_quoteitems')->convertParams((array) $data['contents']);
        $product = Mage::getModel('catalog/product')->load(($params['product']));
        if (isset($params['qty'])) {
            $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
            $params['qty'] = $filter->filter($params['qty']);
        }
        $buyRequest = new Varien_Object($params);
        $this->builderQuery = $this->_WISHLIST->addNewItem($product, $buyRequest); 
        return $this->show();
    }

    /*
     * Remove From Wishlist
     */
     public function destroy()
    {
         die;
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
        return $result;
    }

}
