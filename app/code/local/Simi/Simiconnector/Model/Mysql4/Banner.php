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
 * Connector Resource Model
 * 
 * @category    
 * @package     Connector
 * @author      Developer
 */
class Simi_Simiconnector_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('simiconnector/banner', 'banner_id');
	}
}