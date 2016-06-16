<?php

class Simi_Simiconnector_Model_Config_Source_Addressrequiretype {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Required')),
            array('value' => 2, 'label' => Mage::helper('adminhtml')->__('Optional')),
            array('value' => 3, 'label' => Mage::helper('adminhtml')->__('Hide')),
        );
    }

    public function toArray() {
        return array(
            1 => Mage::helper('adminhtml')->__('Required'),
            2 => Mage::helper('adminhtml')->__('Optional'),
            3 => Mage::helper('adminhtml')->__('Hide'),
        );
    }

}
