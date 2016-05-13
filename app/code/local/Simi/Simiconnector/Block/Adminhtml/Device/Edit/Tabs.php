<?php

/**
 * 

 */
class Simi_Simiconnector_Block_Adminhtml_Device_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('siminotification_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('simiconnector')->__('Device Information'));
    }

    /**
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('simiconnector')->__('Device Information'),
            'title' => Mage::helper('simiconnector')->__('Device Information'),
            'content' => $this->getLayout()->createBlock('simiconnector/adminhtml_device_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
