<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_simibarcode';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Manage QR & Barcodes');
        $this->_addButtonLabel = Mage::helper('simiconnector')->__('Add New Custom QR & Barcode');
        parent::__construct();
    }

}
