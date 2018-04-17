<?php

/**
 * 

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct() 
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_siminotification';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Send'));
        $this->_addButton(
            'saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
            ), -100
        );

        $this->_addButton(
            'saveandsendlater', array(
            'label' => Mage::helper('adminhtml')->__('Save And Send Later'),
            'onclick' => 'saveAndSendLater()',
            'class' => 'save',
        ), -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('siminotification_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'siminotification_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'siminotification_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function saveAndSendLater(){
                editForm.submit($('edit_form').action+'simi_back/edit/');
            }
            
        ";
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('simi/siminotification/jquery-3.2.1.min.js');
        $this->getLayout()->getBlock('head')->addJs('simi/siminotification/simidevices.js');
        $this->getLayout()->getBlock('head')->addJs('simi/siminotification/siminotifications.js');
        return parent::_prepareLayout();
    }

    /**
     * get text to show in header when edit an notification
     *
     * @return string
     */
    public function getHeaderText() 
    {
        if (Mage::registry('siminotification_data') && Mage::registry('siminotification_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Message '%s'", $this->htmlEscape(Mage::registry('siminotification_data')->getNoticeTitle()));
        return Mage::helper('simiconnector')->__('Add Message');
    }

}
