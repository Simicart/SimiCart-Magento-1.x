<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_Productlist extends Mage_Adminhtml_Block_Widget_Grid_Container {
    protected $_theme;
    public function __construct() {
        $this->_theme = Mage::helper('simiconnector/cloud')->getThemeLayout();
        $this->_controller = 'adminhtml_productlist';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Product List on ' .$this->_theme.' theme');
        $this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Product List');
        parent::__construct();
    }

    public function getTheme(){
        return $this->_theme;
    }
}