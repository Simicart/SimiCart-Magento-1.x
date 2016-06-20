<?php

class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit_Renderer_Barcodecustom extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $_values;

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
         $value = $row->getData($this->getColumn()->getIndex());
         
        if ($barcodeProducts = Mage::getModel('admin/session')->getData('barcode_product_import')) {            
            foreach($barcodeProducts as $barcodeProduct){
                if($barcodeProduct['PRODUCT_ID']==$row->getId()){                     
                    if(isset($barcodeProduct['BARCODE']) && $barcodeProduct['BARCODE']){
                        $value = $barcodeProduct['BARCODE'];
                    }
                }
            }
            
        }
        $checked = 'checked="checked"';
        if($value)
            $checked = '';
        $html = Mage::helper('simiconnector')->__('Auto').': <input onclick="setBarcodeAuto(this,\'barcode-' . $row->getId() . '\')" type="checkbox" name="barcode_auto" '.$checked.' value="">';
        $html .= '<input type="text" ';
        $html .= 'id="barcode-' . $row->getId() . '" ';
        $html .= 'style="width: 130px !important;"';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $value . '"';
        $html .= 'class="input-text' . $this->getColumn()->getInlineCss() . '" readonly />';
//        $html .= '<p class="note" id="note_barcode"><span>'.Mage::helper('inventory')->__('Just [A-Z][a-z][0-9] are allowed').'</span></p>';
        return $html;
    }

}

?>
