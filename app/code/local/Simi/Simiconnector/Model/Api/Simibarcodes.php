<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 5/3/16
 * Time: 9:37 PM
 */
class Simi_Simiconnector_Model_Api_Simibarcodes extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'barcode_id';
   
    public function setBuilderQuery() {
        $data = $this->getData();
        if ($data['resourceid']) {
            $this->setStoreView($data);
            $this->setCurrency($data);
            $this->builderQuery = Mage::getModel('simiconnector/simibarcode')->load($data['resourceid']);
        } else {
            $this->builderQuery = Mage::getModel('simiconnector/simibarcode')->getCollection();
        }
    }


}
