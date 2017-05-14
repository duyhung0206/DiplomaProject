<?php

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Edit_Tab_Returnorder extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('returnorderGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('returnorder_filter');
    }

    protected function _prepareCollection() {
        $supplier_id = Mage::app()->getRequest()->getParam('id');
        $purchaseOrders = Mage::getModel('supplier/purchaseorder')->getCollection()
            ->addFieldToFilter('supplier_id', $supplier_id);
        $purchaseOrderIds = array();
        foreach ($purchaseOrders as $purchaseOrder){
            $purchaseOrderIds[] = $purchaseOrder->getId();
        }
        $collection = Mage::getModel('supplier/returnorder')->getCollection()
            ->addFieldToFilter('purchase_order_id', array('in' => $purchaseOrderIds));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('return_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'return_id',
        ));

        $this->addColumn('purchase_order_id', array(
            'header' => Mage::helper('supplier')->__('Purchaseorder ID'),
            'name' => 'purchase_order_id',
            'width' => '80px',
            'index' => 'purchase_order_id'
        ));

        $this->addColumn('return_date', array(
            'header' => Mage::helper('supplier')->__('Return Date'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'return_date',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('supplier')->__('Product Name'),
            'align' => 'left',
            'index' => 'product_name',
        ));

        $this->addColumn('qty_returned', array(
            'header' => Mage::helper('supplier')->__('Qty Returned'),
            'width' => '150px',
            'name' => 'qty_returned',
            'type' => 'number',
            'index' => 'qty_returned'
        ));



        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created by'),
            'name' => 'created_by',
            'width' => '80px',
            'index' => 'created_by'
        ));

        $this->addColumn('reason', array(
            'header' => Mage::helper('supplier')->__('Reason(s)'),
            'name' => 'reason',
            'width' => '150px',
            'index' => 'reason'
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/sup_purchaseorder/edit', array('id' => $row->getPurchaseOrderId(),'active' => 'return'));
    }

}