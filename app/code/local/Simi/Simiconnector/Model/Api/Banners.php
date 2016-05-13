<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Banners extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery($query) {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/banner')->load($data['resourceid']);
        } else {
            $bannerArray = array(0);
            foreach (Mage::getModel('simiconnector/banner')->getCollection() as $banner) {
                if (in_array(Mage::app()->getStore()->getId(), explode(',', $banner->getStoreviewId()))) {
                    $bannerArray[] = $banner->getId();
                }
            }
            $this->builderQuery = Mage::getModel('simiconnector/banner')->getCollection()->addFieldToFilter('banner_id',array('in',$bannerArray));
        }
    }
}
