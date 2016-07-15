<?php

class Simi_Simiconnector_Block_Adminhtml_Simiproductlabel_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_simiproductlabel';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Label'));
        $this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete Label'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('madapter_content') == null)
					tinyMCE.execCommand('mceAddControl', false, 'madapter_content');
				else
					tinyMCE.execCommand('mceRemoveControl', false, 'madapter_content');
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('simiproductlabel_data') && Mage::registry('simiproductlabel_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Label '%s'", $this->htmlEscape(Mage::registry('simiproductlabel_data')->getData('name')));
        return Mage::helper('simiconnector')->__('Add Label');
    }

}
