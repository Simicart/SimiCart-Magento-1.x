<?php
/**

 */
class Simi_Simiconnector_Block_Adminhtml_History_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm(){
		$form = new Varien_Data_Form();
        $this->setForm($form);
        $websites = Mage::helper('simiconnector')->getWebsites();
        
        $list_web = array();
        foreach ($websites as $website) {
            $list_web[] = array(
                'value' => $website->getId(),
                'label' => $website->getName(),
            );
        }
		
		if (Mage::getSingleton('adminhtml/session')->getHistoryData()){
			$data = Mage::getSingleton('adminhtml/session')->getHistoryData();
			Mage::getSingleton('adminhtml/session')->setHistoryData(null);
		}elseif(Mage::registry('history_data'))
			$data = Mage::registry('history_data')->getData();
        
		$fieldset = $form->addFieldset('history_data', array('legend'=>Mage::helper('simiconnector')->__('Notification Information')));
        $fieldset->addType('datetime', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Datetime');
        $fieldset->addField('website_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Website'),
            'name' => 'website_id',
            'values' => $list_web,
            'disabled'  => true,
        ));

         $fieldset->addField('show_popup', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Show Popup'),
            'name' => 'show_popup',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('Yes')),
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('No')),
            ),
            'disabled'  => true,
        ));

        $fieldset->addField('notice_title', 'label', array(
            'label' => Mage::helper('simiconnector')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'notice_title',
            'bold' =>true,
        ));

        $fieldset->addField('image_url', 'image', array(
            'label'        => Mage::helper('simiconnector')->__('Image'),
            'name'        => 'image_url',
            'disabled'  => true,
        ));

        $fieldset->addField('notice_content', 'editor', array(
            'name' => 'notice_content',
            // 'class' => 'required-entry',
            // 'required' => true,
            'label' => Mage::helper('simiconnector')->__('Message'),
            'title' => Mage::helper('simiconnector')->__('Message'),
            'note'  => Mage::helper('simiconnector')->__('characters max: 250'),
            'readonly' => true,
        ));

        $fieldset->addField('type', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Direct viewers to'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'type',
            'values' => Mage::getModel('simiconnector/siminotification')->toOptionArray(),
            'onchange' => 'onchangeNoticeType(this.value)',
            'after_element_html' => '<script> Event.observe(window, "load", function(){onchangeNoticeType(\''.$data['type'].'\');});</script>',
            'disabled'  => true,
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
            'bold'  => true,
            'name'  => 'created_date',
        ));    

        $fieldsetFilter = $form->addFieldset('filter_form', array(
            'legend'=>Mage::helper('simiconnector')->__('Notification Device & Location')
        ));
        $fieldsetFilter->addType('selectname', 'Simi_Simiconnector_Block_Adminhtml_Device_Edit_Renderer_Selectname');
        $fieldsetFilter->addField('device_id', 'select', array(
            'label' => Mage::helper('simiconnector')->__('Device Type'),
            'name' => 'device_id',
            'values' => array(
                array('value' => 0, 'label' => Mage::helper('simiconnector')->__('All')),
                array('value' => 1, 'label' => Mage::helper('simiconnector')->__('IOS')),
                array('value' => 2, 'label' => Mage::helper('simiconnector')->__('Android')),
            ),
            'onchange' => 'onchangeDevice()',
            'after_element_html' => '<script> 
                                        Event.observe(window, "load", function(){
                                            onchangeDevice();
                                        });
                                    </script>',
            'disabled'  => true,
        ));

        $fieldsetFilter->addField('address', 'label', array(
            'name' => 'address',
            'label' => Mage::helper('simiconnector')->__('Address'),
        ));

        $fieldsetFilter->addField('country', 'selectname', array(
            'name' => 'country',
            'label' => Mage::helper('simiconnector')->__('Country'),
        ));

        $fieldsetFilter->addField('state', 'label', array(
            'name' => 'state',
            'label' => Mage::helper('simiconnector')->__('State/Province'),
        ));

        $fieldsetFilter->addField('city', 'label', array(
            'name' => 'city',
            'label' => Mage::helper('simiconnector')->__('City'),
        ));

        $fieldsetFilter->addField('zipcode', 'label', array(
            'name' => 'zipcode',
            'label' => Mage::helper('simiconnector')->__('Zip Code'),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}