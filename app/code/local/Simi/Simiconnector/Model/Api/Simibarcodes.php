<?php

class Simi_Simiconnector_Model_Api_Simibarcodes extends Simi_Simiconnector_Model_Api_Abstract {

    protected $_DEFAULT_ORDER = 'barcode_id';

    public function setBuilderQuery() {
        $data = $this->getData();
        if (isset($data['resourceid']) && $data['resourceid']) {
            $this->builderQuery = Mage::getModel('simiconnector/simibarcode')->getCollection()->addFieldToFilter('barcode_status', '1')->addFieldToFilter('barcode', $data['resourceid'])->getFirstItem();
            if (!$this->builderQuery->getId())
                $this->builderQuery = Mage::getModel('simiconnector/simibarcode')->getCollection()->addFieldToFilter('barcode_status', '1')->addFieldToFilter('qrcode', $data['resourceid'])->getFirstItem();
            if (!$this->builderQuery->getId())
                throw new Exception(Mage::helper('catalog')->__('There is No Product Matchs your Code'), 4);
        } else {
            $this->builderQuery = Mage::getModel('simiconnector/simibarcode')->getCollection();
        }
    }

}
