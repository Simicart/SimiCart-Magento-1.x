<?php
/**
 * Created by PhpStorm.
 * User: frank
 * Date: 2/23/18
 * Time: 11:05 AM
 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Renderer_Select extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _renderOption($option, $value)
    {
        $selected = (( $this->compareString($option['label'], $value)  && (!is_null($value))) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'.$this->escapeHtml($option['label']).'</option>';
    }

    public function getHtml()
    {
        $html = '<select name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" class="no-changes">';
        $value = $this->getValue();
        foreach ($this->_getOptions() as $option){
            if (is_array($option['value'])) {
                $html .= '<optgroup label="' . $this->escapeHtml($option['label']) . '">';
                foreach ($option['value'] as $subOption) {
                    $html .= $this->_renderOption($subOption, $value);
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html.='</select>';
        return $html;
    }

    protected function compareString($str1,$str2){
//        setLocale(LC_ALL, 'vn_VN');
//        $str1 = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str1));
//
//        $str2 = preg_replace('#[^\w\s]+#', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str2));

        if( strpos($str1,$str2) !== false){
            return true;
        }

        return false;

    }

}