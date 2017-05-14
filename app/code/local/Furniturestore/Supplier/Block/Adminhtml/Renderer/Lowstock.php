<?php

class Furniturestore_Supplier_Block_Adminhtml_Renderer_Lowstock extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $supplierId = $row->getData('supplier_id');
        $supplierProducts = Mage::getModel('supplier/product')->getCollection()
            ->addFieldToFilter('supplier_id', $supplierId);
        $productSuppliers = array();
        foreach ($supplierProducts as $supplierProduct){
            $productSuppliers[] = $supplierProduct->getProductId();
        }
        $storeId = Mage::app()->getStore()->getId();
        /** @var $collection Mage_Reports_Model_Resource_Product_Lowstock_Collection  */
        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $productSuppliers))
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }
        if(count($collection) == 0){
            return "";
        }
        $html = "<div style='max-height: 100px;overflow-y: scroll;'><table><tbody>";
        foreach ($collection as $product){
            $html.= "<tr><td>".$product->getData('name')."</td>";
            $html.= "<td>".$product->getData('sku')."</td>";
            $html.= "<td>".$product->getData('qty')."</td>";
        }
        $html.="</tbody></table></div>";
        return $html;
    }

}
