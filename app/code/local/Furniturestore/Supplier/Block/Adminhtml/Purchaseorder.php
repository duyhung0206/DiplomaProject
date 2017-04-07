<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_controller = 'adminhtml_purchaseorder';
        $this->_blockGroup = 'supplier';
        $this->_headerText = Mage::helper('supplier')->__('Manage Purchase Orders');
        $this->_addButtonLabel = Mage::helper('supplier')->__('Create Purchase order');
        parent::__construct();
    }
}