<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('simibarcode_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('simiconnector')->__('Barcode'));
    }

    protected function _beforeToHtml() {
        if (!$this->getRequest()->getParam('id')) {
            $this->addTab('products_section', array(
                'label' => Mage::helper('simiconnector')->__('Barcode'),
                'title' => Mage::helper('simiconnector')->__('Barcode'),
                'url' => $this->getUrl('*/*/products', array('_current' => true)),
                'class' => 'ajax',
            ));
        } else {
            $this->addTab('form_section', array(
                'label' => Mage::helper('simiconnector')->__('Barcode Information'),
                'title' => Mage::helper('simiconnector')->__('Barcode Information'),
                'content' => $this->getLayout()
                        ->createBlock('simiconnector/adminhtml_simibarcode_edit_tab_form')
                        ->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
    }

}
