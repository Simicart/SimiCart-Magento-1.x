<?php

class Simi_Simiconnector_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getWebsites() {
        $websites = Mage::getModel('core/website')->getCollection();
        return $websites;
    }

}
