<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_New extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'supplier';
        $this->_controller = 'adminhtml_purchaseorder';
        $this->_mode = 'new';
        $this->removeButton('save');

        $this->_formScripts[] = "           		   		   
                Event.observe('currency', 'change', function() {
                    var base_currency = \"" . Mage::app()->getStore()->getBaseCurrencyCode() . "\";
                    var select_currency = $('currency').value;
                    var change_rate = $('change_rate').value;
                    var comment = '(1 '+ base_currency +' = ' + change_rate +' ' +select_currency +')';
                    $('change_rate_comment').innerHTML = comment;					
                });	
                
                Event.observe('change_rate', 'change', function() {
                    var base_currency = \"" . Mage::app()->getStore()->getBaseCurrencyCode() . "\";
                    var select_currency = $('currency').value;
                    var change_rate = $('change_rate').value;
                    var comment = '(1 '+ base_currency +' = ' + change_rate +' ' +select_currency +')';
                    $('change_rate_comment').innerHTML = comment;					
                });			  
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('purchaseorder_data')
                && Mage::registry('purchaseorder_data')->getId()
        ) {
            return Mage::helper('supplier')->__("Edit Order No. '%s'", $this->htmlEscape(Mage::registry('purchaseorder_data')->getBillName())
            );
        }
        return Mage::helper('supplier')->__('Add New Purchase Order');
    }

}