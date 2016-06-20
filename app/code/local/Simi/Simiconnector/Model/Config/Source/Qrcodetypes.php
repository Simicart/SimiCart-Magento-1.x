<?php


class Simi_Simiconnector_Model_Config_Source_Qrcodetypes extends Varien_Object {

    /**
     * get model option as array
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => '1', 'label' => Mage::helper('simibarcode')->__('L - smallest')),
            array('value' => '0', 'label' => Mage::helper('simibarcode')->__('M')),
            array('value' => '2', 'label' => Mage::helper('simibarcode')->__('Q')),
            array('value' => '3', 'label' => Mage::helper('simibarcode')->__('H - best')),
        );
    }

    /**
     * get model option as array
     *
     * @return array
     */
    public function getSizes() {
        return array(
            array('value' => '50', 'label' => Mage::helper('simibarcode')->__('1')),
            array('value' => '100', 'label' => Mage::helper('simibarcode')->__('2')),
            array('value' => '150', 'label' => Mage::helper('simibarcode')->__('3')),
            array('value' => '200', 'label' => Mage::helper('simibarcode')->__('4')),
            array('value' => '250', 'label' => Mage::helper('simibarcode')->__('5')),
            array('value' => '300', 'label' => Mage::helper('simibarcode')->__('6')),
            array('value' => '350', 'label' => Mage::helper('simibarcode')->__('7')),
            array('value' => '400', 'label' => Mage::helper('simibarcode')->__('8')),
            array('value' => '450', 'label' => Mage::helper('simibarcode')->__('9')),
            array('value' => '500', 'label' => Mage::helper('simibarcode')->__('10')),
        );
    }

}
