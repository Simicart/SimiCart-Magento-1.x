<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_Productlist extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_productlist';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Product List');
        $this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Product List');
        parent::__construct();
    }

}