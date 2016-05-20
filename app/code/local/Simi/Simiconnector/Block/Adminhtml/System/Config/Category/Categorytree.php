<?php

class Simi_Simiconnector_Block_Adminhtml_System_Config_Category_Categorytree extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	return $this->getLayout()->createBlock('simiconnector/adminhtml_system_config_category_categories')->toHtml();
    }

}