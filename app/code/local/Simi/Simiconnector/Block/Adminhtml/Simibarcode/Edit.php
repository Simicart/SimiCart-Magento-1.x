<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Simibarcode
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Simibarcode Edit Block
 * 
 * @category    
 * @package     Simibarcode
 * @author      Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_simibarcode';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Item'));

        if ($this->getRequest()->getParam('id')) {
            $this->_removeButton('reset');

            $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
                    ), -100);
        }
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('simibarcode_data') && Mage::registry('simibarcode_data')->getId()
        ) {
            return Mage::helper('simiconnector')->__("Edit Code '%s'", $this->htmlEscape(Mage::registry('simibarcode_data')->getBarcode())
            );
        }
        return Mage::helper('simiconnector')->__('Add QR & Barcode ');
    }

}
