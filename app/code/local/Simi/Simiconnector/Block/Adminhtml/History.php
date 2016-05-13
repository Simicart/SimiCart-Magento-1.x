<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_history';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Notification History');
        parent::__construct();
        $this->removeButton('add');
    }

}
