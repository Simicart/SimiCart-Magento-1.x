<?php

class Simi_Simiconnector_Model_Status extends Varien_Object {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    static public function getOptionArray() {
        return array(
            self::STATUS_ENABLED => Mage::helper('simiconnector')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('simiconnector')->__('Disabled')
        );
    }

    static public function getOptionHash() {
        $options = array();
        foreach (self::getOptionArray() as $value => $label)
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        return $options;
    }

    static public function getWebsite() {
        $options = array();
        $options[] = array(
            'value' => 0,
            'label' => Mage::helper('core')->__('All'),
        );
        $collection = Mage::helper('simiconnector')->getWebsites();
        foreach ($collection as $item) {
            $options[] = array(
                'value' => $item->getId(),
                'label' => $item->getName(),
            );
        }
        return $options;
    }

}
