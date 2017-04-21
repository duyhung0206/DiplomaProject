<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        $options = Mage::helper('supplier/purchaseorder')->getReturnOrderStatus();
        $status = $options[$row->getStatus()];
        $className = str_replace(' ', '-', strtolower($status));
        $html = '<span class="po-status '.$className.'">'. $status .'</span>';
        return $html;
    }
}