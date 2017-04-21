<?php

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Region extends Mage_Core_Block_Template
{
  
    public function getSupplier()
    {
        $id = $this->getRequest()->getParam('id');
        $supplier = Mage::getModel('supplier/supplier')->load($id);
        return $supplier;
    }
   
}