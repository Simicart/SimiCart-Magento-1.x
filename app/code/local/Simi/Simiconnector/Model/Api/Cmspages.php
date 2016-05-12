<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Cmspages extends Simi_Simiconnector_Model_Api_Abstract
{
    public function setBuilderQuery($query)
    {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/cms')->load($data['resourceid']);
        } else {
            $website_id = Mage::app()->getWebsite()->getId(); 
            $this->builderQuery = Mage::getModel('simiconnector/cms')->getCollection()->addFieldToFilter('website_id', array('in' => array('0',$website_id)));
        }
    }
}