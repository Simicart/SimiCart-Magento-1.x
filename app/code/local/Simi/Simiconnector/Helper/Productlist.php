<?php

class Simi_Simiconnector_Helper_Productlist extends Mage_Core_Helper_Abstract {

    public function getListTypeId() {
        return array(
            1 => Mage::helper('simiconnector')->__('Custom Product List'),
            2 => Mage::helper('simiconnector')->__('Best Seller'),
            3 => Mage::helper('simiconnector')->__('Most View'),
            4 => Mage::helper('simiconnector')->__('Newly Updated'),
            5 => Mage::helper('simiconnector')->__('Recently Added')
        );
    }

    public function getTypeOption() {
        return array(
            array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Custom Product List')),
            array('value' => 3, 'label' => Mage::helper('simiconnector')->__('Best Seller')),
            array('value' => 4, 'label' => Mage::helper('simiconnector')->__('Most View')),
            array('value' => 5, 'label' => Mage::helper('simiconnector')->__('Newly Updated')),
            array('value' => 6, 'label' => Mage::helper('simiconnector')->__('Recently Added')),
        );
    }

}
