<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_purchase_order')} (
		`purchase_order_id` int(11) unsigned NOT NULL auto_increment,
		`purchase_on` DATETIME default NULL,
		`bill_name` varchar(255) default NULL,
		`supplier_id` int(11) unsigned NOT NULL,
		`supplier_name` varchar(255) default '',
		`total_products` decimal(10,0) default '0',
		`total_amount` decimal(12,4) default '0',
		`comments` text,
		`tax_rate` float default '0',
		`shipping_cost` float default '0',
		`delivery_process` float default '0',
		`status` tinyint(1) NOT NULL default '1',
		`paid` decimal(12,4) default '0',
		`total_products_recieved` decimal(10,0) default '0',
		`created_by` varchar(255) default '',
		`order_placed` int(11),
		`started_date` date,
		`canceled_date` date,
		`expected_date` date,
		`payment_date` date,
		`change_rate` varchar(255) NOT NULL default '1',
		`total_product_refunded` int(11) not null default 0,
		PRIMARY KEY  (`purchase_order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
		
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_purchase_order_product')} (
		`purchase_order_product_id` int(11) unsigned NOT NULL auto_increment,
		`product_id` int(11) unsigned default NULL,
		`product_name` varchar(255) default '',
		`product_sku` varchar(255) default '',
		`purchase_order_id` int(11) unsigned NOT NULL,
		`qty` decimal(10,0) default '0',
		`qty_recieved` decimal(10,0) default '0',
		`cost` decimal(12,4) unsigned NOT NULL default '0.0000',
		`discount` float unsigned NOT NULL default '0.0000',
		`tax` float unsigned NOT NULL default '0.0000',
		`qty_returned` decimal(10,0) default '0',
		`supplier_sku` varchar(244) default NULL,
		PRIMARY KEY(`purchase_order_product_id`),
		INDEX(`purchase_order_id`),
		FOREIGN KEY (`purchase_order_id`) REFERENCES {$this->getTable('furniturestore_purchase_order')}(`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
    
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_delivery')} (
		`delivery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`delivery_date` datetime,
		`qty_delivery` decimal(10,0) unsigned NOT NULL default '0',
		`purchase_order_id` int(11) unsigned NOT NULL,
		`product_id` int(11) unsigned NOT NULL,
		`product_name` varchar(255) NOT NULL,
		`product_sku` varchar(255) NOT NULL,
		`sametime` varchar(255) default '',
		`created_by` varchar(255) default '',
		PRIMARY KEY(`delivery_id`),
		FOREIGN KEY (`purchase_order_id`) REFERENCES {$this->getTable('furniturestore_purchase_order')}(`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_returned_order')} (
		`returned_order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`purchase_order_id` int(11) unsigned NOT NULL,
		`total_products` decimal(10,0) unsigned NOT NULL default '0',
		`total_amount` decimal(12,4) unsigned NOT NULL default '0.0000',
		`returned_on` datetime,
		`status` tinyint(1) NOT NULL default '1',
		`paid` decimal(12,4) default '0',
		`supplier_id` int(11) unsigned NOT NULL default '0',
		PRIMARY KEY(`returned_order_id`),
		INDEX(`purchase_order_id`),
		FOREIGN KEY (`purchase_order_id`) REFERENCES {$this->getTable('furniturestore_purchase_order')}(`purchase_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;


	
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_returned_order_product')} (
		`returned_order_product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`returned_order_id` int(11) unsigned NOT NULL,
		`qty_return` decimal(10,0) unsigned NOT NULL default '0',
		`product_id` int(11) unsigned NOT NULL,
		`product_name` varchar(255) default '',
		`product_sku` varchar(255) default '',
		PRIMARY KEY(`returned_order_product_id`),
		INDEX (`returned_order_id`),
		FOREIGN KEY (`returned_order_id`) REFERENCES {$this->getTable('furniturestore_returned_order')}(`returned_order_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8; 
");
    
$installer->endSetup();

