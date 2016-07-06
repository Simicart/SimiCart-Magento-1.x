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
 * Appreport Resource Model
 * 
 * @category    
 * @package     Appreport
 * @author      Developer
 */
class Simi_Simiconnector_Model_Mysql4_Appreport extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('simiconnector/appreport', 'appreport_id');
    }
}