<?php

class Simi_Simiconnector_Model_Mysql4_Simibarcode extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('simiconnector/simibarcode', 'barcode_id');
	}
}