<?php

class Simi_Simiconnector_Block_Adminhtml_Simicategory_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_simicategory';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Category'));
        $this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete Category'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('simicategory_content') == null)
					tinyMCE.execCommand('mceAddControl', false, 'simicategory_content');
				else
					tinyMCE.execCommand('mceRemoveControl', false, 'simicategory_content');
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
        
    }

    public function getHeaderText() {
        if (Mage::registry('simicategory_data') && Mage::registry('simicategory_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Category '%s'", $this->htmlEscape(Mage::registry('simicategory_data')->getSimicategoryName()));
        return Mage::helper('simiconnector')->__('Add Category');
    }

}
