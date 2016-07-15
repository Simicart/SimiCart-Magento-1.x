<?php

class Simi_Simiconnector_Block_Adminhtml_Simiproductlabel_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('simiproductlabel_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('simiconnector')->__('Label Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_Madapter_Block_Adminhtml_Madapter_Edit_Tabs
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('simiconnector')->__('Label Information'),
            'title' => Mage::helper('simiconnector')->__('Label Information'),
            'content' => $this->getLayout()->createBlock('simiconnector/adminhtml_simiproductlabel_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
