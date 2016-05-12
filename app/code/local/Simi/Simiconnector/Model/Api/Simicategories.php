<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Simicategories extends Simi_Simiconnector_Model_Api_Abstract
{
    protected $_DEFAULT_ORDER = 'simicategory_id';
    
    public function setBuilderQuery($query)
    {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->load($data['resourceid']);
        } else {
            $website_id = Mage::app()->getWebsite()->getId(); 
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->getCollection()->addFieldToFilter('website_id', array('in' => array('0',$website_id)));
        }
    }
}