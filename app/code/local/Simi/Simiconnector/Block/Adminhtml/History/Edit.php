<?php
/**
 * 

 */
class Simi_Simiconnector_Block_Adminhtml_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct(){
		parent::__construct();
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'simiconnector';
		$this->_controller = 'adminhtml_history';
		
        $this->removeButton('reset');
        $this->removeButton('save');
      
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
		";
	}
	
	/**
	 * get text to show in header when edit an notification
	 *
	 * @return string
	 */
	public function getHeaderText(){
		if(Mage::registry('history_data') && Mage::registry('history_data')->getId())
			return Mage::helper('simiconnector')->__("View Message '%s'", $this->htmlEscape(Mage::registry('history_data')->getNoticeTitle()));
		return Mage::helper('simiconnector')->__('Add Message');
	}
}