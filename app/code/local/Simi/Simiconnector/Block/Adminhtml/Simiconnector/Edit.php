<?php

class Simi_Simiconnector_Block_Adminhtml_Simiconnector_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct(){
		parent::__construct();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'simiconnector';
		$this->_controller = 'adminhtml_simiconnector';
		
		$this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete Item'));
		
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('adminhtml')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);

		$this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('simiconnector_content') == null)
					tinyMCE.execCommand('mceAddControl', false, 'simiconnector_content');
				else
					tinyMCE.execCommand('mceRemoveControl', false, 'simiconnector_content');
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}

	public function getHeaderText(){
		if(Mage::registry('simiconnector_data') && Mage::registry('simiconnector_data')->getId())
			return Mage::helper('simiconnector')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('simiconnector_data')->getTitle()));
		return Mage::helper('simiconnector')->__('Add Item');
	}
}