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
 * Connector Edit Form Block
 * 
 * @category 	
 * @package 	Madapter
 * @author  	Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Cms_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare form's information for block
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Edit_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array(
                'id' => $this->getRequest()->getParam('id'),
            )),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
