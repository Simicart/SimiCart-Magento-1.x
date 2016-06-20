<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit_Renderer_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        
        $name = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<select name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $row->getData($this->getColumn()->getIndex());
	if ($barcodeProducts = Mage::getModel('admin/session')->getData('barcode_product_import')) {
            $fieldIds = explode('_', $this->getColumn()->getId());
            foreach($fieldIds as $id => $value){
                if($id == 1)
                    $_field = $value;
                if($id > 1)
                    $_field .= '_'. $value;
            }
            foreach($barcodeProducts as $barcodeProduct){
                if($barcodeProduct['PRODUCT_ID']==$row->getId()){                   
                    if(isset($barcodeProduct[strtoupper($_field)]) && $barcodeProduct[strtoupper($_field)]){
                        $value = $barcodeProduct[strtoupper($_field)];
                    }
                }
            }
            
        }	
         
        foreach ($this->getColumn()->getOptions() as $val => $label){
            $selected = ( ($val == $value && (!is_null($value))) ? ' selected="selected"' : '' );
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html.='</select>';
        return $html;
    }

}

?>
