<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category 	Magestore
 * @package 	Magestore_Inventorysupplier
 * @copyright 	Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license 	http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$connection->addColumn(
        $this->getTable('furniturestore_purchase_order'), 'paid_all', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'length' => 1,
    'default' => 0,
    'comment' => 'paid all to supplier'
        )
);

$connection->addColumn(
        $this->getTable('furniturestore_purchase_order'), 'send_mail', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'length' => 1,
    'default' => 0,
    'comment' => 'send email to supplier'
        )
);

$connection->addColumn(
        $this->getTable('furniturestore_purchase_order'), 'complete_before', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'length' => 1,
    'default' => 0,
    'comment' => 'complete purchase order before receiving all products from supplier'
        )
);

$connection->addColumn(
        $this->getTable('furniturestore_purchase_order'), 'shipping_tax', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'length' => 1,
    'default' => 0,
    'comment' => 'tax calculation settings for shipping; 0: exclude tax, 1: include tax'
        )
);

$connection->addColumn(
        $this->getTable('furniturestore_purchase_order'), 'discount_tax', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'length' => 1,
    'default' => 0,
    'comment' => 'tax calculation settings for discount; 0: before discount, 1: after discount'
        )
);

$purchaseOrder = Mage::getModel('supplier/purchaseorder')->getCollection();
foreach ($purchaseOrder as $pOrder) {
    if ($pOrder->getPaid() == 0) {
        $pOrder->setPaidAll(0);
    } else {
        if ($pOrder->getPaid() >= $pOrder->getTotalAmount()) {
            $pOrder->setPaidAll(1);
        } else {
            $pOrder->setPaidAll(2);
        }
    }
    try {
        $pOrder->save();
    } catch (Exception $e) {
        Mage::log($e->getMessage(), null, 'furniturestore_supplier.log');
    }
}


$installer->endSetup();
