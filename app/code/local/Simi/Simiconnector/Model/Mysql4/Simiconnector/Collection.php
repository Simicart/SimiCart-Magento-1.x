<?php

class Simi_Simiconnector_Model_Mysql4_Simiconnector_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('simiconnector/simiconnector');
	}
}