<?php

class Simi_Simiconnector_Helper_Simivideo extends Mage_Core_Helper_Abstract {

    public function getProductVideo($product) {
        $videoCollection = Mage::getModel('simiconnector/simivideo')->getCollection();
        if ($videoCollection->count() == 0)
            return;
        $productId = $product->getId();
        if (!$productId)
            return;
        $videoArray = array();
        foreach ($videoCollection as $video) {
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
