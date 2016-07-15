<?php

class Simi_Simiconnector_Model_Simiproductlabel extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('simiconnector/simiproductlabel');
    }
}