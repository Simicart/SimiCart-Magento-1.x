<?php
/**
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Appreport
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Appreport Adminhtml Block
 * 
 * @category    
 * @package     Appreport
 * @author      Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Appreport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_appreport';
        $this->_blockGroup = 'simiconnector';
        $this->_headerText = Mage::helper('simiconnector')->__('App Transactions');                
		parent::__construct();
                $this->removeButton("add");
    }
}