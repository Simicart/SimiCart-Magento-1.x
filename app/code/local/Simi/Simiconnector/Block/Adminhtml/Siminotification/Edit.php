<?php

/**
 * 

 */
class Simi_Simiconnector_Block_Adminhtml_Siminotification_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_siminotification';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Send'));
        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
				if (tinyMCE.getInstanceById('siminotification_content') == null)
					tinyMCE.execCommand('mceAddControl', false, 'siminotification_content');
				else
					tinyMCE.execCommand('mceRemoveControl', false, 'siminotification_content');
			}

			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
			
			function onchangeNoticeType(type){
				switch (type) {
					case '1':
						$('product_id').up('tr').show(); 						
						$('product_id').className = 'required-entry input-text'; 
						$('category_id').up('tr').hide();
						$('category_id').className = 'input-text'; 
						$('notice_url').up('tr').hide(); 
						$('notice_url').className = 'input-text'; 
						break;
					case '2':
						$('category_id').up('tr').show(); 
						$('category_id').className = 'required-entry input-text'; 
						$('product_id').up('tr').hide(); 
						$('product_id').className = 'input-text'; 
						$('notice_url').up('tr').hide(); 
						$('notice_url').className = 'input-text'; 
						break;
					case '3':
						$('notice_url').up('tr').show(); 
						$('notice_url').className = 'required-entry input-text'; 
						$('product_id').up('tr').hide(); 
						$('product_id').className = 'input-text'; 
						$('category_id').up('tr').hide();
						$('category_id').className = 'input-text'; 
						break;
					default:
						$('product_id').up('tr').show(); 
						$('product_id').className = 'required-entry input-text'; 
						$('category_id').up('tr').hide(); 
						$('category_id').className = 'input-text'; 
						$('notice_url').up('tr').hide();
						$('notice_url').className = 'input-text'; 
				}
			}

			// function previewNoti(){
			// 	alert('Developing...');
			// }

			// var autocompleteBilling = new google.maps.places.Autocomplete(document.getElementById('location'), {});
		 //    if (document.getElementById('country')) {
		 //        google.maps.event.addListener(autocompleteBilling, 'place_changed', function () {
		 //            var place = autocompleteBilling.getPlace();
		 //            for (var i = 0; i < place.address_components.length; i++) {
		 //                if (place.address_components[i].types[0] == 'country') {
		 //                    document.getElementById('country').value = place.address_components[i]['short_name'];
		 //                    break;
		 //                }
		 //            }

		 //        });
		 //    }
		";
    }

    /**
     * get text to show in header when edit an notification
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('siminotification_data') && Mage::registry('siminotification_data')->getId())
            return Mage::helper('simiconnector')->__("Edit Message '%s'", $this->htmlEscape(Mage::registry('siminotification_data')->getNoticeTitle()));
        return Mage::helper('simiconnector')->__('Add Message');
    }

}
