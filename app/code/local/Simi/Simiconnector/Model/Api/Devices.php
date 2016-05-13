<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Devices extends Simi_Simiconnector_Model_Api_Abstract
{
    protected $_DEFAULT_ORDER = 'device_id';
    
    public function setBuilderQuery($query)
    {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/device')->load($data['resourceid']);
        } else { 
            $this->builderQuery = Mage::getModel('simiconnector/device')->getCollection();
        }
    }
}