<?php
/**
 * Created by PhpStorm.
 * User: frank
 * Date: 1/10/18
 * Time: 6:17 PM
 */

class Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Hidden
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function renderHeader()
    {
        if (false !== $this->getColumn()->getGrid()->getSortable() && false !== $this->getColumn()->getSortable()) {
            $className = 'not-sort';
            $dir = strtolower($this->getColumn()->getDir());
            $nDir= ($dir=='asc') ? 'desc' : 'asc';
            if ($this->getColumn()->getDir()) {
                $className = 'sort-arrow-' . $dir;
            }
            $out = '<a href="#" name="' . $this->getColumn()->getId() . '" title="' . $nDir
                . '" class="' . $className . '"><span id="span_hidden_simi" class="sort-title">'
                . Mage::helper("adminhtml")->getUrl('*/*/filterStateCity').'</span></a>';
        } else {
            $out = $this->getColumn()->getHeader();
        }
        return $out;
    }

}