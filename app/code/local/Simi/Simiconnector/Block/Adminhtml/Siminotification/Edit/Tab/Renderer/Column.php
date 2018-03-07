<?php
/**
 * Created by PhpStorm.
 * User: frank
 * Date: 2/23/18
 * Time: 10:59 AM
 */

class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit_Tab_Renderer_Column extends Mage_Adminhtml_Block_Widget_Grid_Column
{
    protected function _getRendererByType()
    {
        $type = strtolower($this->getType());
        $renderers = $this->getGrid()->getColumnRenderers();



        if (is_array($renderers) && isset($renderers[$type])) {
            return $renderers[$type];
        }

        switch ($type) {
            case 'date':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_date';
                break;
            case 'datetime':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_datetime';
                break;
            case 'number':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_number';
                break;
            case 'currency':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_currency';
                break;
            case 'price':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_price';
                break;
            case 'country':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_country';
                break;
            case 'concat':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_concat';
                break;
            case 'action':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_action';
                break;
            case 'options':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_options';
                break;
            case 'customoptions':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_options';
                break;
            case 'checkbox':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_checkbox';
                break;
            case 'massaction':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_massaction';
                break;
            case 'radio':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_radio';
                break;
            case 'input':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_input';
                break;
            case 'select':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_select';
                break;
            case 'text':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_longtext';
                break;
            case 'store':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_store';
                break;
            case 'wrapline':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_wrapline';
                break;
            case 'theme':
                $rendererClass = 'adminhtml/widget_grid_column_renderer_theme';
                break;
            default:
                $rendererClass = 'adminhtml/widget_grid_column_renderer_text';
                break;
        }
        return $rendererClass;
    }

    protected function _getFilterByType()
    {
        $type = strtolower($this->getType());
        $filters = $this->getGrid()->getColumnFilters();


        if (is_array($filters) && isset($filters[$type])) {
            return $filters[$type];
        }

        switch ($type) {
            case 'datetime':
                $filterClass = 'adminhtml/widget_grid_column_filter_datetime';
                break;
            case 'date':
                $filterClass = 'adminhtml/widget_grid_column_filter_date';
                break;
            case 'range':
            case 'number':
            case 'currency':
                $filterClass = 'adminhtml/widget_grid_column_filter_range';
                break;
            case 'price':
                $filterClass = 'adminhtml/widget_grid_column_filter_price';
                break;
            case 'country':
                $filterClass = 'adminhtml/widget_grid_column_filter_country';
                break;
            case 'options':
                $filterClass = 'adminhtml/widget_grid_column_filter_select';
                break;
            case 'customoptions':
                //$filterClass = 'adminhtml/widget_grid_column_filter_select';
                $filterClass = 'simiconnector/adminhtml_siminotification_edit_tab_renderer_select';
                break;
            case 'massaction':
                $filterClass = 'adminhtml/widget_grid_column_filter_massaction';
                break;

            case 'checkbox':
                $filterClass = 'adminhtml/widget_grid_column_filter_checkbox';
                break;

            case 'radio':
                $filterClass = 'adminhtml/widget_grid_column_filter_radio';
                break;
            case 'store':
                $filterClass = 'adminhtml/widget_grid_column_filter_store';
                break;
            case 'theme':
                $filterClass = 'adminhtml/widget_grid_column_filter_theme';
                break;
            default:
                $filterClass = 'adminhtml/widget_grid_column_filter_text';
                break;
        }
        return $filterClass;
    }

}