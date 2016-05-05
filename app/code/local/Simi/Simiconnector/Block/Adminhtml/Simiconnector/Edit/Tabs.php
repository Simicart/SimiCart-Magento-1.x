<?php

class Simi_Simiconnector_Block_Adminhtml_Simiconnector_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('simiconnector_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('simiconnector')->__('Item Information'));
	}

	protected function _beforeToHtml(){
		$this->addTab('form_section', array(
			'label'	 => Mage::helper('simiconnector')->__('Item Information'),
			'title'	 => Mage::helper('simiconnector')->__('Item Information'),
			'content'	 => $this->getLayout()->createBlock('simiconnector/adminhtml_simiconnector_edit_tab_form')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}