<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Editdelivery_Renderer_Qtydelivery extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    /**
     * 
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {       
        $str = Mage::helper('supplier')->__('Received: '). $row->getData('qty_recieved') .'/'. $row->getData('qty');
        $data = Mage::getModel('admin/session')->getData('delivery_purchaseorder_product_import');
        if(isset($data) && $data != null){
            foreach ($data as $item){
                if($item['SKU'] == $row->getSku()){
                    $qty = $item['QTY'];
                }
            }
        }
        $qty = $qty == null ? ($row->getData('qty') - $row->getData('qty_recieved')): $qty;
        $str .= '<input type="text" class="input-text" name="' . $this->getColumn()->getId()
            . '" value="' . $qty . '"/>';
        return $str;
    }

}

