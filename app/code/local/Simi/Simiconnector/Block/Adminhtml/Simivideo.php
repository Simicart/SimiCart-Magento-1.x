<?php

class Simi_Simiconnector_Block_Adminhtml_Simivideo extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_simivideo';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Videos');                
		parent::__construct();
    }
}