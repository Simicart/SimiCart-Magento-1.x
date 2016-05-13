<?php

class Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Selectname extends Varien_Data_Form_Element_Abstract {

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
	    
        $html = $this->getBold() ? '<strong>' : ''; 
        $values = explode(',', $this->getEscapedValue());
        foreach($values as $id => $value){     
            $countryId = $value;
            $country = Mage::getResourceModel('directory/country_collection')
                ->loadByStore()
                ->addFieldToFilter('country_id',$countryId)
                ->getFirstItem();
            $html .= $country->getName().'<br/>';         
        }         
        
        $html .= $this->getBold() ? '</strong>' : '';
        $html .= $this->getAfterElementHtml();
        return $html;
    }

}


