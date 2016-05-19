<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Connector
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Simi Edit Form Content Tab Block
 * 
 * @category 	
 * @package 	Madapter
 * @author  	Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Cms_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Simi_Connector_Block_Adminhtml_Banner_Edit_Tab_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getConnectorData()) {
            $data = Mage::getSingleton('adminhtml/session')->getConnectorData();
            Mage::getSingleton('adminhtml/session')->setConnectorData(null);
        } elseif (Mage::registry('cms_data'))
            $data = Mage::registry('cms_data')->getData();

        if ($data['cms_id']) {
            $typeID = Mage::helper('simiconnector')->getVisibilityTypeId('cms');
            $visibleStoreViews = Mage::getModel('simiconnector/visibility')->getCollection()
                    ->addFieldToFilter('content_type', $typeID)
                    ->addFieldToFilter('item_id', $data['cms_id']);
            $storeIdArray = array();
            foreach ($visibleStoreViews as $visibilityItem) {
                $storeIdArray[] = $visibilityItem->getData('store_view_id');
            }
            $data['storeview_id'] = implode(',', $storeIdArray);
        }
        $fieldset = $form->addFieldset('simiconnector_form', array('legend' => Mage::helper('simiconnector')->__('Block information')));
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $wysiwygConfig->addData(array(
            'add_variables' => false,
            'plugins' => array(),
            'widget_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
            'directives_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
            'directives_url_quoted' => preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
            'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
        ));


        $field = $fieldset->addField('storeview_id', 'multiselect', array(
            'name' => 'storeview_id[]',
            'label' => Mage::helper('simiconnector')->__('Store View'),
            'title' => Mage::helper('simiconnector')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);

        $fieldset->addField('cms_title', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'cms_title',
        ));

        $fieldset->addField('cms_image', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Icon (width:64px, height:64px)'),
            'name' => 'cms_image_o',
        ));

        $fieldset->addField('cms_content', 'editor', array(
            'name' => 'cms_content',
            'class' => 'required-entry',
            'required' => true,
            'config' => $wysiwygConfig,
            'label' => Mage::helper('simiconnector')->__('Content'),
            'title' => Mage::helper('simiconnector')->__('Content'),
            'style' => 'width: 600px;',
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Sort Order'),
            'required' => false,
            'name' => 'sort_order',
        ));

        $fieldset->addField('cms_status', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Enable'),
            'name' => 'cms_status',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            )
        ));


        $form->setValues($data);
        return parent::_prepareForm();
    }

}
