<?php

class Simi_Simiconnector_Block_Adminhtml_SimiVideo_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_simivideo';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Video'));
        $this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete Video'));

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
        if (Mage::registry('simivideo_data') && Mage::registry('simivideo_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Video '%s'", $this->htmlEscape(Mage::registry('simivideo_data')->getData('video_title')));
        return Mage::helper('simiconnector')->__('Add Video');
    }

}
