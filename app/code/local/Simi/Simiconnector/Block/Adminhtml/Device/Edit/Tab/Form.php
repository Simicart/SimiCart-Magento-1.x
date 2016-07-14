<?php

/**

 */
class Simi_Simiconnector_Block_Adminhtml_Device_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        

        if (Mage::getSingleton('adminhtml/session')->getDeviceData()) {
            $data = Mage::getSingleton('adminhtml/session')->getDeviceData();
            Mage::getSingleton('adminhtml/session')->setDeviceData(null);
        } elseif (Mage::registry('device_data'))
            $data = Mage::registry('device_data')->getData();

        $fieldset = $form->addFieldset('device_form', array('legend' => Mage::helper('simiconnector')->__('Device information')));
        $fieldset->addType('datetime', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime');
        $fieldset->addType('selectname', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Selectname');

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
        
        $fieldset->addField('plaform_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Device Type'),
            'name' => 'plaform_id',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('iPhone')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('iPad')),
                array('value' => 3, 'label' => Mage::helper('simiconnector')->__('Android')),
            ),
            'disabled' => true,
        ));

        $fieldset->addField('country', 'selectname', array(
            'label' => Mage::helper('simiconnector')->__('Country'),
            'bold' => true,
            'name' => 'country',
        ));

        $fieldset->addField('state', 'label', array(
            'label' => Mage::helper('simiconnector')->__('State/Province'),
                // 'bold'  => true,
        ));

        $fieldset->addField('city', 'label', array(
            'label' => Mage::helper('simiconnector')->__('City'),
                // 'bold'  => true,
        ));

        $fieldset->addField('device_token', 'label', array(
            'label' => Mage::helper('simiconnector')->__('Device Token'),
        ));

        $fieldset->addField('created_time', 'datetime', array(
            'label' => Mage::helper('simiconnector')->__('Create Date'),
            'bold' => true,
            'name' => 'created_date',
        ));

        $fieldset->addField('is_demo', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Is Demo'),
            'bold' => true,
            'values' => array(
                array('value' => 3, 'label' => Mage::helper('simiconnector')->__('N/A')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('NO')),
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('YES')),
            ),
            'name' => 'is_demo',
            'disabled' => true,
        ));
        $form->setValues($data);
        return parent::_prepareForm();
    }

}
