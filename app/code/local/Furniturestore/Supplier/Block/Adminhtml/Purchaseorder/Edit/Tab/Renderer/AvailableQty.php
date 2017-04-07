<?php


class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_AvailableQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $product_id = $row->getId();
        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product_id);

       	$availableQty = 0 + $stockItem->getQty();
        
	return '<input name="qty_order" class="input-text" type="text" value=""/><br/>'.Mage::helper('supplier')->__('Current Qty: ') . $availableQty;
        

    }
}
