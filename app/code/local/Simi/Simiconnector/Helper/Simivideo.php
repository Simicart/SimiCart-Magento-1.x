<?php

class Simi_Simiconnector_Helper_Simivideo extends Mage_Core_Helper_Abstract
{

    public function getProductVideo($product)
    {
        $videoCollection = Mage::getModel('simiconnector/simivideo')->getCollection();

        if ($websiteId = Mage::helper('simiconnector/cloud')->getWebsiteIdSimiUser()) {
            $storeIds = Mage::app()->getWebsite($websiteId)->getStoreIds();
            $videoCollection->addFieldToFilter('storeview_id', array('in' => $storeIds));
        }
        if ($videoCollection->count() == 0)
            return;
        $productId = $product->getId();
        if (!$productId)
            return;
        $videoArray = array();
        foreach ($videoCollection as $video) {
            if($video->getData('status') == Simi_Simiconnector_Model_Status::STATUS_DISABLED)
                continue;
            if (in_array($productId, explode(",", $video->getData('product_ids')))) {
                $videoArray[] = $video->getData('video_id');
            }
        }
        $collection = Mage::getModel('simiconnector/simivideo')->getCollection()->addFieldToFilter('status', '1')->addFieldToFilter('video_id', array('in' => $videoArray));
        $returnArray = array();
        foreach ($collection as $productVideo) {
            $returnArray[] = $productVideo->toArray();
        }
        return $returnArray;
    }

}
