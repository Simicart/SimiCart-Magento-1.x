<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_siminotification';
		$this->_blockGroup = 'simiconnector';
		$this->_headerText = Mage::helper('simiconnector')->__('Notification Manager');
		$this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Notification');
		parent::__construct();
	}
}