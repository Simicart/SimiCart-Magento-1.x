<?php

class Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime extends Varien_Data_Form_Element_Abstract
{

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $values = explode(',', $this->getEscapedValue());
        $html = $this->getBold() ? '<strong>' : ''; 
        foreach($values as $id => $value){     
             $dateFormatIso = Mage::app()->getLocale()
                ->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $time = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($dateFormatIso);
                $html .= $time . '<br/>';           
        }      
        
        $html.= $this->getBold() ? '</strong>' : '';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

}


