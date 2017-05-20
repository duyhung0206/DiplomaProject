<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {


        $this->_controller = 'adminhtml_purchaseorder';
        $this->_blockGroup = 'supplier';
        $this->_headerText = Mage::helper('supplier')->__('Manage Purchase Orders');

        $admin = Mage::getSingleton('admin/session')->getUser();
        $roleData = Mage::getModel('admin/user')->load($admin->getUserId())->getRole();
        $supplier = Mage::getModel('supplier/supplier')->getCollection()
            ->addFieldToFilter('user_id', $admin->getUserId())
            ->getFirstItem();
        if($roleData->getRoleName() != 'Role for supplier'){
            $this->_addButtonLabel = Mage::helper('supplier')->__('Create Purchase order');

            $this->_addButton('Trash', array(
                'label' => Mage::helper('supplier')->__('Deleted Purchase Orders'),
                'onclick' => "setLocation('{$this->getUrl('*/*/trash')}')",
                'class' => 'delete',
            ), 0);
        }

        parent::__construct();
        if($roleData->getRoleName() == 'Role for supplier'){
            $this->_removeButton('add');
        }
    }
}