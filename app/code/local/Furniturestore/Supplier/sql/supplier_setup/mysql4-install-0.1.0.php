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

$installer->run("	
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_supplier')} (
		`supplier_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`supplier_name` varchar(255) NOT NULL default '',
		`contact_name` varchar(255),
		`supplier_email` varchar(255) NOT NULL default '',
		`telephone` varchar(50) NOT NULL default '',
		`fax` varchar(50) default '',
		`street` text NOT NULL default '',
		`city` varchar(255) NOT NULL default '',
		`country_id` char(3) NOT NULL default '',
		`state` varchar(255) NOT NULL default '',
		`state_id` varchar(255) NOT NULL default '',
		`postcode` varchar(255) NOT NULL default '',
		`description` text default '',
		`website` varchar(255) default '',
		`created_by` varchar(255) default '',
		`created_time` DATETIME default NULL,
		`updated_time` DATETIME default NULL,
		`supplier_status` tinyint(1) NOT NULL default '1',     
		`total_order` decimal(10,0) NOT NULL default '0',
                `purchase_order` decimal(12,4) NOT NULL default '0',
                `return_order` decimal(12,4) NOT NULL default '0',
		`last_purchase_order` date default NULL,
		`ship_via` int(11) default 0,
        `payment_term` int(11) default 0,
        `password_hash` varchar(255) default '',
		PRIMARY KEY(`supplier_id`)
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
	CREATE TABLE IF NOT EXISTS {$this->getTable('furniturestore_supplier_product')}(
		`supplier_product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`supplier_id` int(11) unsigned NOT NULL,
		`product_id` int(11) unsigned NOT NULL,		
		`cost` decimal(12,4) unsigned NOT NULL default '0.0000',
		`discount` float unsigned NOT NULL default '0.0000',
		`tax` float unsigned NOT NULL default '0.0000',
		`supplier_sku` varchar(244) default NULL,
		INDEX (`supplier_id`),
		INDEX (`product_id`),
		PRIMARY KEY(`supplier_product_id`),
		FOREIGN KEY (`supplier_id`) REFERENCES {$this->getTable('furniturestore_supplier')}(`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')}(`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
	)ENGINE=InnoDB DEFAULT CHARSET=utf8;
    	
");

$installer->endSetup();

