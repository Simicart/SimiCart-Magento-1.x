<?php

class Simi_Simiconnector_Model_Api_Simivideos extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'video_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        $productId = $data['params']['product_id'];
        if (!$productId)
            throw new Exception(Mage::helper('catalog')->__('There is No Product ID sent'), 4);

        $videoCollection = Mage::getModel('simiconnector/simivideo')->getCollection();
        $videoArray = array();
        foreach ($videoCollection as $video) {
            if (in_array($productId, explode(",", $video->getData('product_ids')))) {
                $videoArray[] = $video->getData('video_id');
            }
        }
        $this->builderQuery = Mage::getModel('simiconnector/simivideo')->getCollection()->addFieldToFilter('status', '1')->addFieldToFilter('video_id', array('in'=> $videoArray));
    }

}
