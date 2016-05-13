<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Cmspages extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'sort_order';

    public function setBuilderQuery($query) {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/cms')->load($data['resourceid']);
        } else {
            $cmsArray = array(0);
            foreach (Mage::getModel('simiconnector/cms')->getCollection() as $cms) {
                if (in_array(Mage::app()->getStore()->getId(), explode(',', $cms->getStoreviewId()))) {
                    $bannerArray[] = $banner->getId();
                }
            }
            $this->builderQuery = Mage::getModel('simiconnector/cms')->getCollection()->addFieldToFilter('cms_id', array('in', $cmsArray));
        }
    }

}
