<?php

class Simi_Simiconnector_Block_Adminhtml_Simiproductlabel extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_simiproductlabel';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Product Label');                
		parent::__construct();
    }
}