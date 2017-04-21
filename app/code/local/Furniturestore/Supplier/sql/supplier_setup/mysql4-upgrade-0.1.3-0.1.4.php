<?php

$installer = $this;

$installer->startSetup();

$installer->run("        
    
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_return')} (
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
		FOREIGN KEY (`purchase_order_id`) REFERENCES {$this->getTable('furniturestore_purchase_order')}(`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
");

$installer->endSetup();

