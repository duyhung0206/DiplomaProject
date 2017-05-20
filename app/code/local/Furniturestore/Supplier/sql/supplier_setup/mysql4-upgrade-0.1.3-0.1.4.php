<?php

$installer = $this;

$installer->startSetup();
$role = Mage::getModel('admin/roles');
$data = array(
    'role_name' => 'Role for supplier',
    'role_type' => 'G',
    'in_role_user' => '',
    'in_role_user_old' => '',
    'resource' => '__root__,admin/supplier,admin/supplier/list_supplier,admin/supplier/list_purchaseorder',
    'all' => '0',

);

$resource   = explode(',', '__root__,admin/supplier,admin/supplier/list_supplier,admin/supplier/list_purchaseorder');
$role->addData($data)->save();
$role->setName('Role for supplier')->save();

Mage::getModel('admin/rules')
    ->setRoleId($role->getId())
    ->setResources($resource)
    ->saveRel();
    
$installer->endSetup();

