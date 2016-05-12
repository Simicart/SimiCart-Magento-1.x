<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_Cms extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_cms';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('CMS');
        $this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Static Block');
        parent::__construct();
    }

}