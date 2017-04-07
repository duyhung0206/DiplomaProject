<?php

class Furniturestore_Supplier_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        echo 'test';
        $supplier = Mage::getModel('supplier/supplier')->getCollection();
        Zend_debug::dump($supplier->getData());
        die();
    }
}