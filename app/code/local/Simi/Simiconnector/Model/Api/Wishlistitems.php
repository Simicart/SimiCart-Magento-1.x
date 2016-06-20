<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Wishlistitems extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'wishlist_item_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId() && ($customer->getId() != '')) {
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
        } else
            throw new Exception(Mage::helper('customer')->__('Please login First.'), 4);
        if ($data['resourceid']) {
            //$this->builderQuery = Mage::getModel('core/store_group')->load($data['resourceid']);
        } else {
            $this->builderQuery = $wishlist->getItemCollection();
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
            
            $options = $this->_getOptionsSelectedFromItem($itemModel, $product);
            $addition_info[$itemModel->getData('wishlist_item_id')] = array(
                'product_type' => $product->getTypeId(),
                'product_regular_price' => Mage::app()->getStore()->convertPrice($product->getPrice(), false),
                'product_price' => Mage::app()->getStore()->convertPrice($product->getFinalPrice(), false),
                'stock_status' => $isSaleAble,
                //'product_image' => $this->getImageProduct($product, null, $width, $height),
                'is_show_price' => true,
                //'wishlist_item_id' => $item->getWishlistItemId(),
                'options' => $options,
                'selected_all_required_options' => $this->_checkIfSelectedAllRequiredOptions($itemModel, $options),
                'product_sharing_message' => $productSharingMessage,
                'product_sharing_url' => $product->getProductUrl(),
            );
        }
        foreach ($result['wishlistitems'] as $index=>$item) {
            $result['wishlistitems'][$index] = array_merge($item, $addition_info[$item['wishlist_item_id']]);
        }
        return $result;
    }

    /*
     * @param:
     * $item - Wishlist Item
     */
    function _checkIfSelectedAllRequiredOptions($item, $options = null) {
        $selected = true;
        $product = $item->getProduct();

        $itemOptions = Mage::getModel('wishlist/item_option')->getCollection()
                ->addItemFilter(array($item->getData('wishlist_item_id')));
        if (!$options)
            $options = $this->_getOptionsSelectedFromItem($item, $product);
        $productObjData = new Varien_Object();
        $productObjData->product_id = $product->getData('entity_id');
        $product_information = Mage::getModel('connector/catalog_product')->getDetail($productObjData);
        $product_options = $product_information['data'][0]['options'];

        foreach ($product_options as $product_option) {
            if ($product_option['is_required'] == 'YES') {
                $selected = false;
                foreach ($options as $option) {
                    if (($option['option_title'] == $product_option['option_title']) && ($option['option_value']) && ($option['option_value'] != ''))
                        $selected = true;
                }
            }
        }
        return $selected;
    }
    
    function _getOptionsSelectedFromItem($item, $product) {
        $options = array();
        if (version_compare(Mage::getVersion(), '1.5.0.0', '>=') === true) {
            $helper = Mage::helper('catalog/product_configuration');
            if ($product->getTypeId() == "simple") {
                $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getCustomOptions($item));
            } elseif ($product->getTypeId() == "configurable") {
                $options = Mage::helper('simiconnector/checkout')->convertOptionsCart($helper->getConfigurableOptions($item));
            } elseif ($product->getTypeId() == "bundle") {
                $options = Mage::helper('simiconnector/checkout')->getOptions($item);
            }
        } else {
            if ($product->getTypeId() != "bundle") {
                $options = Mage::helper('simiconnector/checkout')->getUsedProductOption($item);
            } else {
                $options = Mage::helper('simiconnector/checkout')->getOptions($item);
            }
        }
        return $options;
    }

}
