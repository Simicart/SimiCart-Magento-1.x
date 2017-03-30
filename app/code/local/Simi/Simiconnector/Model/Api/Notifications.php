<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Notifications extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'notice_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/siminotification')->load($data['resourceid']);
        } else {
            $deviceModel = Mage::getModel('simiconnector/device')->getCollection()->addFieldToFilter('device_token', $data['params']['device_token'])->getFirstItem();
            if (!($deviceModel->getId())) {
                $this->builderQuery = Mage::getModel('simiconnector/siminotification')->getCollection();
                return;
            }
            $shownList = array();
            foreach (Mage::getModel('simiconnector/history')->getCollection() as $noticeHistory) {
                $noticeId = $noticeHistory->getData('notice_id');
                if ($noticeId && !in_array($noticeId, $shownList)) {
                    if (in_array($deviceModel->getId(), explode(',', str_replace(' ', '', $noticeHistory->getData('devices_pushed'))))) {
                        $shownList[] = $noticeHistory->getData('notice_id');
                    }
                }
            }
            $this->builderQuery = Mage::getModel('simiconnector/siminotification')->getCollection()->addFieldToFilter('notice_id', array('in' => $shownList));
        }
    }

    public function index() {
        $result = parent::index();
        foreach ($result['notifications'] as $index => $notification) {
            if (!$notification['type'])
                $notification['type'] = '1';
            if ($notification['image_url']) {
                $notification['image_url'] = Mage::getBaseUrl('media') . $notification['image_url'];
                $list = @getimagesize($notification['image_url']);
                $notification['width'] = $list[0];
                $notification['height'] = $list[1];
            }
            if ($notification['category_id']) {
                $categoryId = $notification['category_id'];
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $categoryChildrenCount = $category->getChildrenCount();
                $categoryName = $category->getName();
                $notification['category_name'] = $categoryName;
                if ($categoryChildrenCount > 0)
                    $categoryChildrenCount = 1;
                else
                    $categoryChildrenCount = 0;
                $notification['has_child'] = $categoryChildrenCount;
                if (!$notification['has_child']) {
                    $notification['has_child'] = '';
                }
            }
            if ($notification['product_id']) {
                $productId = $notification['product_id'];
                $productName = Mage::getModel('catalog/product')->load($productId)->getName();
                $notification['product_name'] = $productName;
            }
            $result['notifications'][$index] = $notification;
        }

        return $result;
    }

}
