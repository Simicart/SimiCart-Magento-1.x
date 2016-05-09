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
class Simi_Simiconnector_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_banner';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('simiconnector')->__('Delete Banner'));

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
                        $('product_id').up('tr').show();                        
                        $('product_id').className = 'required-entry input-text'; 
                        $('category_id').up('tr').hide();
                        $('category_id').className = 'input-text'; 
                        $('banner_url').up('tr').hide(); 
                        $('banner_url').className = 'input-text'; 
                        break;
                    case '2':
                        $('category_id').up('tr').show(); 
                        $('category_id').className = 'required-entry input-text'; 
                        $('product_id').up('tr').hide(); 
                        $('product_id').className = 'input-text'; 
                        $('banner_url').up('tr').hide(); 
                        $('banner_url').className = 'input-text'; 
                        break;
                    case '3':
                        $('banner_url').up('tr').show();                         
                        $('product_id').up('tr').hide(); 
                        $('product_id').className = 'input-text'; 
                        $('category_id').up('tr').hide();
                        $('category_id').className = 'input-text'; 
                        break;
                    default:
                        $('product_id').up('tr').show(); 
                        $('product_id').className = 'required-entry input-text'; 
                        $('category_id').up('tr').hide(); 
                        $('category_id').className = 'input-text'; 
                        $('banner_url').up('tr').hide();
                        $('banner_url').className = 'input-text'; 
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
        if (Mage::registry('banner_data') && Mage::registry('banner_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getBannerTitle()));
        return Mage::helper('simiconnector')->__('Add Banner');
    }

}