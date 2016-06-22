<?php

class Simi_Simiconnector_Block_Adminhtml_Simivideo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('simivideo_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('simiconnector')->__('Video Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Edit_Tabs
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('simiconnector')->__('Video Information'),
            'title' => Mage::helper('simiconnector')->__('Video Information'),
            'content' => $this->getLayout()->createBlock('simiconnector/adminhtml_simivideo_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
