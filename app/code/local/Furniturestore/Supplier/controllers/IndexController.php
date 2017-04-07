<?php

class Furniturestore_Supplier_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $purchaseorder = Mage::getModel('supplier/purchaseorder')->load(30);
        Zend_debug::dump($purchaseorder->getData());
        $purchaseorder->setCurrency('test')->save();
        Zend_debug::dump($purchaseorder->getData());
        die();
    }
}