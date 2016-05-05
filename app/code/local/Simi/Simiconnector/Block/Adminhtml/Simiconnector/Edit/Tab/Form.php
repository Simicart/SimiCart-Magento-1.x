<?php

class Simi_Simiconnector_Block_Adminhtml_Simiconnector_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		if (Mage::getSingleton('adminhtml/session')->getSimiconnectorData()){
			$data = Mage::getSingleton('adminhtml/session')->getSimiconnectorData();
			Mage::getSingleton('adminhtml/session')->setSimiconnectorData(null);
		}elseif(Mage::registry('simiconnector_data'))
			$data = Mage::registry('simiconnector_data')->getData();
		
		$fieldset = $form->addFieldset('simiconnector_form', array('legend'=>Mage::helper('simiconnector')->__('Item information')));

		$fieldset->addField('title', 'text', array(
			'label'		=> Mage::helper('simiconnector')->__('Title'),
			'class'		=> 'required-entry',
			'required'	=> true,
			'name'		=> 'title',
		));

		$fieldset->addField('filename', 'file', array(
			'label'		=> Mage::helper('simiconnector')->__('File'),
			'required'	=> false,
			'name'		=> 'filename',
		));

		$fieldset->addField('status', 'select', array(
			'label'		=> Mage::helper('simiconnector')->__('Status'),
			'name'		=> 'status',
			'values'	=> Mage::getSingleton('simiconnector/status')->getOptionHash(),
		));

		$fieldset->addField('content', 'editor', array(
			'name'		=> 'content',
			'label'		=> Mage::helper('simiconnector')->__('Content'),
			'title'		=> Mage::helper('simiconnector')->__('Content'),
			'style'		=> 'width:700px; height:500px;',
			'wysiwyg'	=> false,
			'required'	=> true,
		));

		$form->setValues($data);
		return parent::_prepareForm();
	}
}