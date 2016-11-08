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
 * Connector Adminhtml Block
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container {

    protected $_theme;

    public function __construct() {
        $this->_theme = Mage::helper('simiconnector/cloud')->getThemeLayout();
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('Banner Manager on ' .$this->_theme.' theme');
        $this->_addButtonLabel = Mage::helper('simiconnector')->__('Add Banner');
        parent::__construct();
    }

    public function getTheme(){
        return $this->_theme;
    }
}