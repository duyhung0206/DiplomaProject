<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Returnorder_Renderer_Qtyreturn extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row) {
        $str = Mage::helper('supplier')->__('Return: '). $row->getData('qty_returned') .'/'. $row->getData('qty_recieved');
        $data = Mage::getModel('admin/session')->getData('returnorder_product_import');
        if(isset($data) && $data != null){
            foreach ($data as $item){
                if($item['SKU'] == $row->getSku()){
                    $qty = $item['QTY'];
                }
            }
        }
        $qty = $qty == null ? ($row->getData('qty_recieved') - $row->getData('qty_returned')): $qty;
        $str .= '<input type="text" class="input-text" name="' . $this->getColumn()->getId()
            . '" value="' . $qty . '"/>';
        return $str;
    }

}

