<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Connector Edit Block
 * 
 * @category 	
 * @package 	Connector
 * @author  	Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Productlist_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_productlist';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save List'));
        $this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete List'));

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

            function onchangeNoticeType(type){
                switch (type) {
                    case '1':
                        $('list_products').up('tr').show();                        
                        break;                    
                    default:
                        $('list_products').up('tr').hide();                        
                }
            }
		";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('productlist_data') && Mage::registry('productlist_data')->getId())
            return Mage::helper('simiconnector')->__("Edit List '%s'", $this->htmlEscape(Mage::registry('productlist_data')->getListTitle()));
        return Mage::helper('simiconnector')->__('Add List');
    }

}
