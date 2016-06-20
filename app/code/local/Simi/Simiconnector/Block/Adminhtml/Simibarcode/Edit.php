<?php

/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    
 * @package     Simibarcode
 * @copyright   Copyright (c) 2012 
 * @license     
 */

/**
 * Simibarcode Edit Block
 * 
 * @category    
 * @package     Simibarcode
 * @author      Developer
 */
class Simi_Simiconnector_Block_Adminhtml_Simibarcode_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'simiconnector';
        $this->_controller = 'adminhtml_simibarcode';

        $this->_updateButton('save', 'label', Mage::helper('simiconnector')->__('Save Item'));

        if ($this->getRequest()->getParam('id')) {
            $this->_removeButton('reset');

            $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
                    ), -100);

            $this->_addButton('print_barcode', array(
                'label' => Mage::helper('simiconnector')->__('Print Barcodes'),
                'onclick' => 'createBarcode()',
                'class' => 'add',
                    ), 0);

            $this->_addButton('print_qrcode', array(
                'label' => Mage::helper('simiconnector')->__('Print QR codes'),
                'onclick' => 'createQrcode()',
                'class' => 'add',
                    ), 0);
        }
        $classBarcode = $this->getValidateBarcode();

        $this->_formScripts[] = "
			function createBarcode(){
                var url = '" . $this->getUrl('adminhtml/simibarcode_printbarcode/selecttemplate', array('barcode' => $this->getRequest()->getParam('id'))) . "';
                window.open(url,'_blank', 'scrollbars=yes, resizable=yes, width=700, height=700, left=80, menubar=yes');                
            }

            function createQrcode(){
                var url = '" . $this->getUrl('adminhtml/simibarcode_printqrcode/selecttemplate', array('barcode' => $this->getRequest()->getParam('id'))) . "';
                window.open(url,'_blank', 'scrollbars=yes, resizable=yes, width=700, height=700, left=80, menubar=yes');                
            }
            
            function toggleEditor() {
                if (tinyMCE.getInstanceById('simibarcode_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'simibarcode_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'simibarcode_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function setBarcodeAuto (element, id){
                if(element.checked){
                    $(id).value = '';
                    $(id).readOnly = true;
                    $(id).removeClassName('" . $classBarcode . "')                    
                }else{
                    $(id).readOnly = false;
                    $(id).addClassName('" . $classBarcode . "')                    
                }
            }
            
             function fileSelected() {
                var file = document.getElementById('fileToUpload').files[0];
                if (file) {
                    var fileSize = 0;
                    if (file.size > 1024 * 1024)
                        fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
                    else
                        fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
                    document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
                    document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
                    document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
                }
            }
			
            function uploadFile() {
                if(!$('fileToUpload') || !$('fileToUpload').value){
                    alert('Please choose CSV file to import!');return false;
                }
                if($('loading-mask')){
                    $('loading-mask').style.display = 'block';
                    $('loading-mask').style.height = '900px';                    
                    $('loading-mask').style.width = '1500px';                    
                    $('loading-mask').style.top = '0';                    
                    $('loading-mask').style.left = '-2';                    
                }
                var fd = new FormData();
                fd.append('fileToUpload', document.getElementById('fileToUpload').files[0]);
                fd.append('form_key', document.getElementById('form_key').value);
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', uploadProgress, false);
                xhr.addEventListener('load', uploadComplete, false);
                xhr.addEventListener('error', uploadFailed, false);
                xhr.addEventListener('abort', uploadCanceled, false);
                xhr.open('POST', '" . $this->getUrl('simibarcodeadmin/adminhtml_simibarcode/getImportCsv') . "');
                xhr.send(fd);
           }

           function uploadProgress(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    //document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
                   // document.getElementById('prog').value = percentComplete;
                }
                else {
                   // document.getElementById('progressNumber').innerHTML = 'unable to compute';
                }
           }
          
           function uploadComplete(evt) {
                var import_data = '" . Mage::getModel('admin/session')->getData('null_barcode_product_import') . "';    
                    
                if(import_data == '1'){
                     alert('No product was added');
                }
                
                $('barcode_tabs_products_section').addClassName('notloaded');
                barcode_tabsJsTabs.showTabContent($('barcode_tabs_products_section'));
                //varienGlobalEvents.attachEventHandler('showTab',function(){ sendstockproductGridJsObject.doFilter(); });
           }

           function uploadFailed(evt) {
                alert('" . Mage::helper('simiconnector')->__('There was an error attempting to upload the file.') . "');
           }

           function uploadCanceled(evt) {
                alert('" . Mage::helper('simiconnector')->__('The upload has been canceled by the user or the browser dropped the connection.') . "');
           }   
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('simibarcode_data') && Mage::registry('simibarcode_data')->getId()
        ) {
            return Mage::helper('simiconnector')->__("Edit Code '%s'", $this->htmlEscape(Mage::registry('simibarcode_data')->getBarcode())
            );
        }
        return Mage::helper('simiconnector')->__('Add QR & Barcode ');
    }

    public function getValidateBarcode() {
        $validate = 'required-entry';
        return $validate;
    }

}
