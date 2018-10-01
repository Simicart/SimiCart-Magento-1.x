<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Siminotification
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Siminotification Model
 * 
 * @category    
 * @package     Siminotification
 * @author      Developer
 */
class Simi_Simiconnector_Model_Observer
{

    public function saleOrderPlaceAfter($observer){
        $order = $observer->getEvent()->getOrder();
        $customer_email = $order->getCustomerEmail();
        /*
         * save To App report
         */
        if ($platform =
            Mage::getSingleton('core/session')->getData('simiconnector_platform')) {
            try {
                $orderId = $order->getId();
                $existedTransaction = Mage::getModel('simiconnector/appreport')
                    ->getCollection()
                    ->addFieldToFilter('order_id', array('nin' => $orderId))
                    ->getFirstItem();
                if(!$existedTransaction || !$existedTransaction->getId()) {
                    $newTransaction = Mage::getModel('simiconnector/appreport');
                    $newTransaction->setOrderId($orderId);
                    $platform = ($platform == 'pwa')?'1':'0';
                    $newTransaction->setPlatform($platform);
                    $newTransaction->save();
                }
            } catch (Exception $exc) {
            }
        }
        if($customer_email){
            $collection = Mage::getModel('simiconnector/device')->getCollection()->addFieldToFilter('user_email',$customer_email);
            foreach ($collection as $device) {
                $id = $device->getId();
                $deviceModel = Mage::getModel('simiconnector/device')->load($id);
                if ($deviceModel->getId()) {
                    $count_purchase = $deviceModel->getCountPurchase() + 1;

                    $deviceModel->setCountPurchase($count_purchase);
                    $deviceModel->save();
                }
            }
        }
    }

     public function sendNotificationShedule()
    {
        $server_timezone = date('Z')/3600;
        $nowDate = new Zend_Date(now(), Varien_Date::DATETIME_INTERNAL_FORMAT);

        $normal_server_time = $nowDate->subTime($server_timezone);
        Mage::log('Called sendNotificationShedule '.$nowDate.' server timezone  '.$server_timezone.' normal server time  '.$normal_server_time, null, 'similog.log');

        $collection = Mage::getModel('simiconnector/siminotification')->getCollection()->addFieldToFilter('status_send', '1');


        if ($collection->getSize() > 0) {

            foreach ($collection as $notification) {
                if ($notification['server_time_to_send'] && $notification['server_time_to_send']) {

                    $shedule_time = ($notification['server_time_to_send']);
                    $server_time_to_send = new Zend_Date($shedule_time, Varien_Date::DATETIME_INTERNAL_FORMAT);

                    if ($server_time_to_send->isEarlier($normal_server_time)) {
                    //if ($shedule_time <= now()) {
                        Mage::helper('simiconnector/siminotification')->sendNotice($notification->toArray());

                        Mage::log('Send notification ' . json_encode($notification->toArray()), null, 'similog.log');
                    } else {
                        Mage::log('Checked  ' . $server_time_to_send, null, 'similog.log');

                    }


                }

            }

        }

    }



    /**
     * process catalog_product_save_after event
     *
     * @return Simi_Siminotification_Model_Observer
     */
    public function sendNotificationProductChangePrice($observer) 
    {
        $helper = Mage::helper('simiconnector/siminotification');
        $storeViewCollection = Mage::getModel('core/store')->getCollection();
        foreach ($storeViewCollection as $storeview) {
            $storeviewId = $storeview->getId();
            if ($helper->getConfig('simiconnector/notification/noti_price_enable', $storeviewId)) {
                $newProduct = $observer->getProduct();
                if (!in_array($storeview->getWebsiteId(), $newProduct->getWebsiteIds()))
                    continue;
                $newPrice = $newProduct->getData('price');
                $newSpecialPrice = $newProduct->getData('special_price');
                $oldProduct = Mage::getModel('catalog/product')->load($newProduct->getId());
                $oldPrice = $oldProduct->getData('price');
                $oldSpecialPrice = $oldProduct->getData('special_price');
                if ($oldSpecialPrice != $newSpecialPrice && $newProduct->getId() > 0 && $newProduct->getStatus() == '1' && $newProduct->getVisibility() != '1') {
                    $data = array();
                    $content = Mage::helper('simiconnector/siminotification')->__(
                        $helper->getConfig('simiconnector/notification/noti_price_message', $storeviewId), $newProduct->getName(), $this->formatPrice($oldSpecialPrice), $this->formatPrice($newSpecialPrice)
                    );
                    $data['website_id'] = $helper->getConfig('simiconnector/notification/noti_price_website', $storeviewId);
                    $data['show_popup'] = $helper->getConfig('simiconnector/notification/noti_price_showpopup', $storeviewId);
                    $data['notice_title'] = $helper->getConfig('simiconnector/notification/noti_price_title', $storeviewId);
                    $data['notice_url'] = $helper->getConfig('simiconnector/notification/noti_price_url', $storeviewId);
                    $data['notice_content'] = $content;
                    $data['device_id'] = $helper->getConfig('simiconnector/notification/noti_price_platform', $storeviewId);
                    $data['notice_sanbox'] = $helper->getConfig('simiconnector/notification/noti_price_sandbox', $storeviewId);
                    $data['type'] = $helper->getConfig('simiconnector/notification/noti_price_type', $storeviewId);
                    $data['product_id'] = $newProduct->getId();
                    $data['category_id'] = $helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId);
                    $data['category_name'] = $this->getCategoryName($helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId));
                    $data['has_child'] = $this->getCategoryChildrenCount($helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId));
                    $data['created_time'] = now();
                    $data['notice_type'] = 1;
                    $data['notice_sanbox'] = '2';
                    $data['storeview_id'] = $storeviewId;
                    $data['devices_pushed'] = $this->getAllDeviceToPush($storeviewId);
                    if ($data['devices_pushed']) {
                        Mage::helper('simiconnector/siminotification')->sendNotice($data);
                    }
                } elseif ($oldPrice != $newPrice && $newProduct->getId() > 0 && $newProduct->getStatus() == '1' && $newProduct->getVisibility() != '1') {
                    $data = array();
                    $content = Mage::helper('simiconnector/siminotification')->__(
                        $helper->getConfig('simiconnector/notification/noti_price_message', $storeviewId), $newProduct->getName(), $this->formatPrice($oldPrice), $this->formatPrice($newPrice)
                    );
                    $data['website_id'] = $helper->getConfig('simiconnector/notification/noti_price_website', $storeviewId);
                    $data['show_popup'] = $helper->getConfig('simiconnector/notification/noti_price_showpopup', $storeviewId);
                    $data['notice_title'] = $helper->getConfig('simiconnector/notification/noti_price_title', $storeviewId);
                    $data['notice_url'] = $helper->getConfig('simiconnector/notification/noti_price_url', $storeviewId);
                    $data['notice_content'] = $content;
                    $data['device_id'] = $helper->getConfig('simiconnector/notification/noti_price_platform', $storeviewId);
                    $data['notice_sanbox'] = $helper->getConfig('simiconnector/notification/noti_price_sandbox', $storeviewId);
                    $data['type'] = $helper->getConfig('simiconnector/notification/noti_price_type', $storeviewId);
                    $data['product_id'] = $newProduct->getId();
                    $data['category_id'] = $helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId);
                    $data['category_name'] = $this->getCategoryName($helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId));
                    $data['has_child'] = $this->getCategoryChildrenCount($helper->getConfig('simiconnector/notification/noti_price_category_id', $storeviewId));
                    $data['created_time'] = now();
                    $data['notice_type'] = 1;
                    $data['notice_sanbox'] = '2';
                    $data['storeview_id'] = $storeviewId;
                    $data['devices_pushed'] = $this->getAllDeviceToPush($storeviewId);
                    if ($data['devices_pushed']) {
                        Mage::helper('simiconnector/siminotification')->sendNotice($data);
                    }
                } elseif (!$newProduct->getId()) {
                    Mage::getSingleton('core/session')->setData('new_added_product_sku', $newProduct->getSku());
                }
            }
        }
    }

    public function sendNotificationNewProduct($observer) 
    {
        $helper = Mage::helper('simiconnector/siminotification');
        $storeViewCollection = Mage::getModel('core/store')->getCollection();
        foreach ($storeViewCollection as $storeview) {
            $storeviewId = $storeview->getId();
            if ($helper->getConfig('simiconnector/notification/new_product_enable', $storeviewId)) {
                $newProduct = $observer->getProduct();
                if (!in_array($storeview->getWebsiteId(), $newProduct->getWebsiteIds()))
                    continue;
                $lastProductId = Mage::getModel('catalog/product')->getCollection()
                                ->setOrder('entity_id', 'desc')->getFirstItem()->getId();
                if ($newProduct->getId() && $newProduct->getId() == $lastProductId && $newProduct->getStatus() == '1' && $newProduct->getVisibility() != '1' && $newProduct->getSku() == Mage::getSingleton('core/session')->getData('new_added_product_sku')) {
                    $content = Mage::helper('simiconnector/siminotification')->__(
                        $helper->getConfig('simiconnector/notification/new_product_message', $storeviewId), $newProduct->getName()
                    );
                    $data = array();
                    $data['website_id'] = $helper->getConfig('simiconnector/notification/new_product_website', $storeviewId);
                    $data['show_popup'] = $helper->getConfig('simiconnector/notification/new_product_showpopup', $storeviewId);
                    $data['notice_title'] = $helper->getConfig('simiconnector/notification/new_product_title', $storeviewId);
                    $data['notice_url'] = $helper->getConfig('simiconnector/notification/new_product_url', $storeviewId);
                    $data['notice_content'] = $content;
                    $data['device_id'] = $helper->getConfig('simiconnector/notification/new_product_platform', $storeviewId);
                    $data['notice_sanbox'] = $helper->getConfig('simiconnector/notification/new_product_sandbox', $storeviewId);
                    $data['type'] = $helper->getConfig('simiconnector/notification/new_product_type', $storeviewId);
                    $data['product_id'] = $newProduct->getId();
                    $data['category_id'] = $helper->getConfig('simiconnector/notification/new_product_category_id', $storeviewId);
                    $data['category_name'] = $this->getCategoryName($helper->getConfig('simiconnector/notification/new_product_category_id', $storeviewId));
                    $data['has_child'] = $this->getCategoryChildrenCount($helper->getConfig('simiconnector/notification/new_product_category_id', $storeviewId));
                    $data['created_time'] = now();
                    $data['notice_type'] = 2;
                    $data['notice_sanbox'] = '2';
                    Mage::getSingleton('core/session')->setData('new_added_product_sku', NULL);
                    $data['storeview_id'] = $storeviewId;
                    $data['devices_pushed'] = $this->getAllDeviceToPush($storeviewId);
                    if ($data['devices_pushed']) {
                        Mage::helper('simiconnector/siminotification')->sendNotice($data);
                    }
                }
            }
        }
    }

    public function getCategoryName($categoryId) 
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $categoryName = $category->getName();
        return $categoryName;
    }

    public function getCategoryChildrenCount($categoryId) 
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $categoryChildrenCount = $category->getChildrenCount();
        if ($categoryChildrenCount > 0)
            $categoryChildrenCount = 1;
        else
            $categoryChildrenCount = 0;
        return $categoryChildrenCount;
    }

    public function formatPrice($price) 
    {
        return Mage::helper('core')->currency($price, true, false);
    }

    public function getAllDeviceToPush($storeview_id) 
    {
        $idArray = array();
        $tokenArray = array();
        foreach (Mage::getModel('simiconnector/device')->getCollection()->addFieldToFilter('storeview_id', $storeview_id) as $device) {
            if (!in_array($device->getData('device_token'), $idArray)) {
                $idArray[] = $device->getId();
                $tokenArray[] = $device->getData('device_token');
            }
        }

        return implode(',', $idArray);
    }

    /*
     * Remove coupon on Cart (coupon for app only)
     */
    public function sales_quote_collect_totals_before($observer) 
    {
        $quote = $observer->getQuote();
        $coupon = $quote->getCouponCode();
        $isApp = strpos(Mage::getUrl('*/*'), 'simiconnector');
        $pre_fix = (string) Mage::getStoreConfig('simiconnector/general/app_dedicated_coupon');

        if ($pre_fix && ($pre_fix != '') && ($isApp == false) && $coupon) {
            if (strpos(strtolower($coupon), strtolower($pre_fix)) !== false) {
                $quote->setCouponCode('');
            }
        }
    }

}
