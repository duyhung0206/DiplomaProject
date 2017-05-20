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
        $setup = new Mage_Core_Model_Resource_Setup();
        $installer = $setup;
        $installer->startSetup();
        $installer->run("CREATE TABLE IF NOT EXISTS {$setup->getTable('furniturestore_return')} (
		`return_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`return_date` datetime,
		`qty_returned` decimal(10,0) unsigned NOT NULL default '0',
		`purchase_order_id` int(11) unsigned NOT NULL,
		`reason` varchar(255) NOT NULL,
		`product_id` int(11) unsigned NOT NULL,
		`product_name` varchar(255) NOT NULL,
		`product_sku` varchar(255) NOT NULL,
		`sametime` varchar(255) default '',
		`created_by` varchar(255) default '',
		PRIMARY KEY(`return_id`),
		FOREIGN KEY (`purchase_order_id`) REFERENCES {$setup->getTable('furniturestore_purchase_order')}(`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $installer->endSetup();
        echo "success";

    }
}