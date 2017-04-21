<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:29
 */

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('purchaseorderGrid');
        $this->setDefaultSort('purchaseorder_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('supplier/purchaseorder')->getCollection();


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $this->addColumn('purchase_order_id', array(
            'header' => Mage::helper('supplier')->__('Order #'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'purchase_order_id',
        ));

        $this->addColumn('purchase_on', array(
            'header' => Mage::helper('supplier')->__('Purchased On'),
            'align' => 'right',
            'type' => 'date',
            'index' => 'purchase_on',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

        $this->addColumn('bill_name', array(
            'header' => Mage::helper('supplier')->__('Bill to Name'),
            'width' => '150px',
            'align' => 'left',
            'index' => 'bill_name',
        ));

        $this->addColumn('supplier_name', array(
            'header' => Mage::helper('supplier')->__('Supplier'),
            'type' => 'options',
            'width' => '150px',
            'align' => 'left',
            'index' => 'supplier_id',
            'options' => Mage::helper('supplier/supplier')->getAllSupplierName(),
            'renderer' => 'supplier/adminhtml_purchaseorder_renderer_supplier',
            'filter_condition_callback' => array($this, 'filterSupplierCallback')
        ));

        $this->addColumn('total_products', array(
            'header' => Mage::helper('supplier')->__('Qty Requested'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_products',
        ));
        $this->addColumn('total_products_recieved', array(
            'header' => Mage::helper('supplier')->__('Qty Received'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_products_recieved',
        ));

        $this->addColumn('total_amount', array(
            'header' => Mage::helper('supplier')->__('Subtotal'),
            'width' => '150px',
            'type' => 'number',
            'align' => 'right',
            'index' => 'total_amount',
            'filter_index' => 'total_amount',
            'renderer' => 'supplier/adminhtml_purchaseorder_renderer_total',
            'filter_condition_callback' => array($this, 'filterTotalAmount')
        ));

        $this->addColumn('paid_all', array(
            'header' => Mage::helper('supplier')->__('Payment'),
            'width' => '150px',
            'type' => 'options',
            'align' => 'right',
            'index' => 'paid_all',
            'options' => Mage::helper('supplier/purchaseorder')->getPaymentStatus()
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('supplier')->__('Status'),
            'align' => 'left',
            'width' => '250px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('supplier/purchaseorder')->getReturnOrderStatus(),
            'renderer' => 'supplier/adminhtml_purchaseorder_renderer_status',
        ));

//        $labelAction = __('Edit');
//        $labelTrash = __('Move to Trash');
//        $this->addColumn('action', array(
//            'header' => Mage::helper('supplier')->__('Action'),
//            'renderer' => 'supplier/adminhtml_purchaseorder_renderer_action',
//        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('supplier')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('supplier')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}