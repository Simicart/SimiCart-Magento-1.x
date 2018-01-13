<?php

class Simi_Simiconnector_Helper_Simivideo extends Mage_Core_Helper_Abstract
{
    public function getProductVideo($product)
    {
        if (!Mage::getStoreConfig("simiconnector/simivideo/enable"))
            return;
        
        $productId = $product->getId();
        if (!$productId)
            return;
        $videoCollection = Mage::getModel('simiconnector/simivideo')->getCollection()
            ->addFieldToFilter('status', Simi_Simiconnector_Model_Status::STATUS_ENABLED)
            ->addFieldToFilter('storeview_id', Mage::app()->getStore()->getId());

        if ($videoCollection->count() == 0)
            return;
        foreach ($videoCollection as $video) {
            if (in_array($productId, explode(",", $video->getData('product_ids')))) {
                $returnArray[] = $video->toArray();
            }
        }

        return $returnArray;
    }

}
