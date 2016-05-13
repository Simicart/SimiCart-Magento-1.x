<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Simicategories extends Simi_Simiconnector_Model_Api_Abstract
{
    protected $_DEFAULT_ORDER = 'sort_order';
    
    public function setBuilderQuery($query) {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->load($data['resourceid']);
        } else {
            $categoryArray = array(0);
            foreach (Mage::getModel('simiconnector/simicategory')->getCollection() as $simicategory) {
                if (in_array(Mage::app()->getStore()->getId(), explode(',', $simicategory->getStoreviewId()))) {
                    $categoryArray[] = $simicategory->getId();
                }
            }
            $this->builderQuery = Mage::getModel('simiconnector/simicategory')->getCollection()->addFieldToFilter('simicategory_id', array('in', $categoryArray));
        }
    }
}