<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Wishlistitems extends Simi_Simiconnector_Model_Api_Abstract
{

    protected $_DEFAULT_ORDER = 'wishlist_item_id';
    protected $_RETURN_MESSAGE;
    protected $_RETURN_URL;
    protected $_WISHLIST;

    public function setBuilderQuery() 
    {
        $data = $this->getData();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId() && ($customer->getId() != '')) {
            $this->_WISHLIST = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
            //check if not shared
            if (!$this->_WISHLIST->getShared()) {
                $this->_WISHLIST->setShared('1');
                $this->_WISHLIST->save();
            }

            $sharingUrl = $this->_WISHLIST->getSharingCode();
            $this->_RETURN_MESSAGE = Mage::getStoreConfig('appwishlist/general/sharing_message') . ' ' . Mage::getUrl('wishlist/shared/index/code/' . $sharingUrl);
            $this->_RETURN_URL = Mage::getUrl('wishlist/shared/index/code/' . $sharingUrl);
        } else
            throw new Exception(Mage::helper('customer')->__('Please login First.'), 4);
        
        if (isset($data['resourceid']) && $data['resourceid']) {
            if ($data['resourceid'] == 'add_all_tocart') {
                $this->addAllWishlistItemsToCart();
                $this->builderQuery = $this->_WISHLIST->getItemCollection();
                return;
            }
            
            $this->builderQuery = Mage::getModel('wishlist/item')->load($data['resourceid']);
            if (isset($data['params']['add_to_cart']) && $data['params']['add_to_cart']) {
                $this->addWishlistItemToCart($data['resourceid']);
                $this->builderQuery = $this->_WISHLIST->getItemCollection();
            }
        } else {
            $this->builderQuery = $this->_WISHLIST->getItemCollection();
        }
    }

    public function index() 
    {
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
                'type_id' => $product->getTypeId(),
                'product_regular_price' => Mage::app()->getStore()->convertPrice($product->getPrice(), false),
                'product_price' => Mage::app()->getStore()->convertPrice($product->getFinalPrice(), false),
                'stock_status' => $isSaleAble,
                'product_image' => Mage::helper('catalog/image')->init($product, 'small_image')->constrainOnly(TRUE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(600, 600)->__toString(),
                'is_show_price' => true,
                'options' => $options,
                'selected_all_required_options' => Mage::helper('simiconnector/wishlist')->checkIfSelectedAllRequiredOptions($itemModel, $options),
                'product_sharing_message' => $productSharingMessage,
                'product_sharing_url' => $product->getProductUrl(),
                'app_prices' => Mage::helper('simiconnector/price')->formatPriceFromProduct($product),
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

    public function store() 
    {
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
        $data = $this->getData();
        $item = Mage::getModel('wishlist/item')->load($data['resourceid']);
        if ($item->getId()) {
            $item->delete();
            $this->_WISHLIST->save();
            Mage::helper('wishlist')->calculate();
        }

        $this->builderQuery = $this->_WISHLIST->getItemCollection();
        return $this->index();
    }

    /*
     * Add From Wishlist To Cart
     */

    public function addWishlistItemToCart($itemId) 
    {
        foreach ($this->_WISHLIST->getItemCollection() as $wishlistItem) {
            if ($wishlistItem->getData('wishlist_item_id') == $itemId)
                $item = $wishlistItem;
        }

        $product = $item->getProduct();
        $options = Mage::helper('simiconnector/wishlist')->getOptionsSelectedFromItem($item, $product);
        if ($item && (Mage::helper('simiconnector/wishlist')->checkIfSelectedAllRequiredOptions($item, $options))) {
            $isSaleAble = $product->isSaleable();
            if ($isSaleAble) {
                $item = Mage::getModel('wishlist/item')->load($itemId);
                $item->setQty('1');
                $cart = Mage::getSingleton('checkout/cart');
                $options = Mage::getModel('wishlist/item_option')->getCollection()
                        ->addItemFilter(array($itemId));
                $item->setOptions($options->getOptionsByItem($itemId));
                if ($item->addToCart($cart, true)) {
                    $cart->save()->getQuote()->collectTotals();
                }

                $this->_WISHLIST->save();
                Mage::helper('wishlist')->calculate();
            }
        }
    }

    /*
     * Show An Item
     */

    public function show() 
    {
        $data = $this->getData();
        $useIndex= false;
        if (isset($data['params']) && isset($data['params']['add_to_cart']) && $data['params']['add_to_cart'])
            $useIndex = true;
        if (isset($data['resourceid']) && isset($data['resourceid']) && ($data['resourceid'] == 'add_all_tocart'))
            $useIndex = true;
        
        if ($useIndex) {
            $this->builderQuery = $this->_WISHLIST->getItemCollection();
            return $this->index();
        }

        return parent::show();
    }

    /*
     * Add All wishlist to cart
     */
    public function addAllWishlistItemsToCart()
    {
        $wishlist   = $this->_WISHLIST;
        $this->_RETURN_MESSAGE = '';
        
        $addedItems = array();
        $notSalable = array();
        $hasOptions = array();

        $cart       = Mage::getSingleton('checkout/cart');
        $collection = $wishlist->getItemCollection()
            ->setVisibilityFilter();

        foreach ($collection as $item) {
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();
                
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                if ($item->addToCart($cart, true)) {
                    $addedItems[] = $item->getProduct();
                }

            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                    $notSalable[] = $item;
                } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $hasOptions[] = $item;
                } else {
                    $this->_RETURN_MESSAGE .= ', '.Mage::helper('wishlist')->__('%s for "%s".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }
                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_RETURN_MESSAGE .= ', '.Mage::helper('wishlist')->__('Cannot add the item to shopping cart.');
            }
        }

        if ($notSalable) {
            $products = array();
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $this->_RETURN_MESSAGE .= ', '.Mage::helper('wishlist')->__('Unable to add the following product(s) to shopping cart: %s.', join(', ', $products));
        }

        if ($hasOptions) {
            $products = array();
            foreach ($hasOptions as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $this->_RETURN_MESSAGE .= ', '.Mage::helper('wishlist')->__('Product(s) %s have required options. Each of them can be added to cart separately only.', join(', ', $products));
        }

        if ($addedItems) {
            $wishlist->save();
            $products = array();
            foreach ($addedItems as $product) {
                $products[] = '"' . $product->getName() . '"';
            }
            $this->_RETURN_MESSAGE = 
                Mage::helper('wishlist')
                    ->__('%d product(s) have been added to shopping cart: %s.', count($addedItems), join(', ', $products));
            $cart->save()->getQuote()->collectTotals();
        }
        Mage::helper('wishlist')->calculate();
    }
    
    /*
     * Add Message
     */

    public function getList($info, $all_ids, $total, $page_size, $from) 
    {
        $result = parent::getList($info, $all_ids, $total, $page_size, $from);
        if ($this->_RETURN_MESSAGE) {
            $result['message'] = array($this->_RETURN_MESSAGE);
        }

        if ($this->_RETURN_URL) {
            $result['sharing_url'] = array($this->_RETURN_URL);
        }

        return $result;
    }

}
