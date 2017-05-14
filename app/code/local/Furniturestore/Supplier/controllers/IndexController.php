<?php

class Furniturestore_Supplier_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $suppliers = Mage::getModel('supplier/supplier')->getCollection();
        foreach ($suppliers as $supplier){
            $purchaseOrders = Mage::getModel('supplier/purchaseorder')->getCollection()
                ->addFieldToFilter('supplier_id', $supplier->getId());
            foreach ($purchaseOrders as $purchaseOrder){
                $totalProductReturn = 0;
                $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());

                foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
                    $totalProductReturn += $purchaseOrderProduct->getQtyReturned();
                }
                $purchaseOrder->setTotalProductRefunded($totalProductReturn);
                $purchaseOrder->save();
            }
        }


    }

    public function testAction(){
//        Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
    }
}