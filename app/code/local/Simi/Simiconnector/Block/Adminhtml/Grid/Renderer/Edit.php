<?php

class Simi_Simiconnector_Block_Adminhtml_Grid_Renderer_Edit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $webId = Mage::getBlockSingleton('simiconnector/adminhtml_web_switcher')->getWebsiteId();
        $url = Mage::helper("adminhtml")->getUrl('*/*/edit', array('id' => $row->getId(), 'website' => $webId, 'device_id' => $row->getDeviceId()));
        return "<a href=" . "'" . $url . "'" . ">" . Mage::helper('core')->__("Edit") . "</a>";
    }

}
