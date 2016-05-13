<?php
/**
 * 

 */
class Simi_Simiconnector_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('simiconnector/history', 'history_id');
	}
}