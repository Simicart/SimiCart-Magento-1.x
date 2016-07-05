<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_History_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getHistoryData()) {
            $data = Mage::getSingleton('adminhtml/session')->getHistoryData();
            Mage::getSingleton('adminhtml/session')->setHistoryData(null);
        } elseif (Mage::registry('history_data'))
            $data = Mage::registry('history_data')->getData();

        $fieldset = $form->addFieldset('history_data', array('legend' => Mage::helper('simiconnector')->__('Notification Information')));
        $fieldset->addType('datetime', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime');

        $stores = Mage::getModel('core/store')->getCollection();

        $list_store = array();
        foreach ($stores as $store) {
            $list_store[] = array(
                'value' => $store->getId(),
                'label' => $store->getName(),
            );
        }
        $fieldset->addField('storeview_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Store View'),
            'name' => 'storeview_id',
            'values' => $list_store,
            'disabled' => true,
            'onchange' => 'clearDevices()'
        ));


        $fieldset->addField('show_popup', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Show Popup'),
            'name' => 'show_popup',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            ),
            'disabled' => true,
        ));

        $fieldset->addField('notice_title', 'label', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'notice_title',
            'bold' => true,
        ));

        $fieldset->addField('image_url', 'image', array(
            'label' => Mage::helper('simiconnector')->__('Image'),
            'name' => 'image_url',
            'disabled' => true,
        ));

        $fieldset->addField('notice_content', 'editor', array(
            'name' => 'notice_content',
            // 'class' => 'required-entry',
            // 'required' => true,
            'label' => Mage::helper('simiconnector')->__('Message'),
            'title' => Mage::helper('simiconnector')->__('Message'),
            'note' => Mage::helper('simiconnector')->__('characters max: 250'),
            'readonly' => true,
        ));

        $fieldset->addField('type', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Direct viewers to'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'type',
            'values' => Mage::getModel('simiconnector/siminotification')->toOptionArray(),
            'onchange' => 'onchangeNoticeType(this.value)',
            'after_element_html' => '<script> Event.observe(window, "load", function(){onchangeNoticeType(\'' . $data['type'] . '\');});</script>',
            'disabled' => true,
        ));

        $fieldset->addField('product_id', 'text', array(
            'name' => 'product_id',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Product ID'),
            'readonly' => true,
        ));

        $fieldset->addField('category_id', 'text', array(
            'name' => 'category_id',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('Category ID'),
            'readonly' => true,
        ));

        $fieldset->addField('notice_url', 'text', array(
            'name' => 'notice_url',
            'class' => 'required-entry',
            'required' => true,
            'label' => Mage::helper('simiconnector')->__('URL'),
            'readonly' => true,
        ));

        $fieldset->addField('created_time', 'datetime', array(
            'label' => Mage::helper('simiconnector')->__('Sent Date'),
            'bold' => true,
            'name' => 'created_date',
        ));

        $fieldset->addField('devices_pushed', 'text', array(
            'label' => Mage::helper('simiconnector')->__('Devices pushed'),
            'name' => 'devices_pushed',
            'readonly' => true,
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
