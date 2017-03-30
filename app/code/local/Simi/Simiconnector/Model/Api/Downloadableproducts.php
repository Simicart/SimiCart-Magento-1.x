<?php


class Simi_Simiconnector_Model_Api_Downloadableproducts extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'item_id';
    protected $_purchased = '';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            
        } else {
            $this->builderQuery = $this->getCollectionItems();
        }
    }

    public function index() {
        $result = parent::index();
        foreach ($result['downloadableproducts'] as $index => $item) {
            $_item = $this->getCollectionItems()->addFieldToFilter('item_id', $item['item_id'])->getFirstItem();
            $fileName = '';
            if ($_item->getData('link_file')) {
                $fileName = $_item->getData('link_file');
                $fileName = explode('/', $fileName);
                $fileName = end($fileName);
            }
            $itDe = $this->getPurchased()->getItemById($_item->getPurchasedId());
            $data = array(
                'order_id' => $itDe->getOrderIncrementId(),
                'order_date' => $itDe->getCreatedAt(),
                'order_name' => $itDe->getProductName(),
                'order_link' => $this->getDownloadUrl($_item),
                'order_file' => $fileName,
                'order_status' => $_item->getStatus(),
                'order_remain' => $this->getRemainingDownloads($_item)
            );
            $item = array_merge($item, $data);
            $result['downloadableproducts'][$index] = $item;
        }
        return $result;
    }

    public function getCollectionItems() {
        $session = Mage::getSingleton('customer/session');
        $purchased = Mage::getResourceModel('downloadable/link_purchased_collection')
                ->addFieldToFilter('customer_id', $session->getCustomerId())
                ->addOrder('created_at', 'desc');

        $this->setPurchased($purchased);
        $purchasedIds = array();
        foreach ($purchased as $_item) {
            $purchasedIds[] = $_item->getId();
        }
        if (empty($purchasedIds)) {
            $purchasedIds = array(null);
        }

        $purchasedItems = Mage::getResourceModel('downloadable/link_purchased_item_collection')
                ->addFieldToFilter('purchased_id', array('in' => $purchasedIds))
                ->addFieldToFilter('status', array(
                    'nin' => array(
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
                    )
                        )
                )
                ->setOrder('item_id', 'desc');
        return $purchasedItems;
    }

    public function getDownloadUrl($item) {
        return Mage::getUrl('downloadable/download/link', array('id' => $item->getLinkHash(), '_secure' => true));
    }

    public function getRemainingDownloads($item) {
        if ($item->getNumberOfDownloadsBought()) {
            $downloads = $item->getNumberOfDownloadsBought() - $item->getNumberOfDownloadsUsed();
            return $downloads;
        }
        return Mage::helper('downloadable')->__('Unlimited');
    }
    
    public function getPurchased() {
        return $this->_purchased;
    }
    
    public function setPurchased($value) {
        $this->_purchased = $value;
    }

}
