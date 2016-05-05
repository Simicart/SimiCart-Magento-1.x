<?php

class Simi_Simiconnector_Block_Adminhtml_Simiconnector extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_simiconnector';
		$this->_blockGroup = 'simiconnector';
		$this->_headerText = Mage::helper('simiconnector')->__('Item Manager');
		$this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Item');
		parent::__construct();
	}
}