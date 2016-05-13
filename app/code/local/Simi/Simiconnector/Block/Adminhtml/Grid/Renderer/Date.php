<?php

class Simi_Simiconnector_Block_Adminhtml_Grid_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if ($row->getExpiredTime()){
            return $row->getExpiredTime();
        }
       return '0000-00-00';
    }

}