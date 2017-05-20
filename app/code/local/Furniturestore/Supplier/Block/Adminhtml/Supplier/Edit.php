<?php

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct()  {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'supplier';
        $this->_controller = 'adminhtml_supplier';
        
        $this->_updateButton('save', 'label', Mage::helper('supplier')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('supplier')->__('Delete'));


        $admin = Mage::getSingleton('admin/session')->getUser();
        $roleData = Mage::getModel('admin/user')->load($admin->getUserId())->getRole();

        if($roleData->getRoleName() == 'Role for supplier'){
            $this->_removeButton('delete');
        }

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            Event.observe('auto_general_password','click',function(){
                if($('auto_general_password').checked){
                    $('new_password').value = '';
                    $('new_password').disable();
                }else{
                    $('new_password').enable();
                }
            });
            var id = '" . $this->getRequest()->getParam('id', null) . "';
            function toggleEditor() {
                if (tinyMCE.getInstanceById('inventory_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'inventory_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'inventory_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
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
                xhr.open('POST', '" . $this->getUrl('adminhtml/sup_index/importproduct') . "');
                xhr.send(fd);
           }

           function uploadProgress(evt) {
           }

           function uploadComplete(evt) {
               $('supplier_tabs_products_section').addClassName('notloaded');
               supplier_tabsJsTabs.showTabContent($('supplier_tabs_products_section'));               
           }

           function uploadFailed(evt) {
                alert('There was an error attempting to upload the file.');
           }

           function uploadCanceled(evt) {
                alert('The upload has been canceled by the user or the browser dropped the connection.');
           }
           
         
        ";
    }

    public function getHeaderText(){
        if(Mage::registry('supplier_data') && Mage::registry('supplier_data')->getId())
            return Mage::helper('supplier')->__("Edit %s", $this->htmlEscape(Mage::registry('supplier_data')->getSupplierName()));
        return Mage::helper('supplier')->__('Add Item');
    }
}