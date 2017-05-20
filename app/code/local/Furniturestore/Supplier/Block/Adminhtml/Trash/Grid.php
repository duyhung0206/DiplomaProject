<?php

class Furniturestore_Supplier_Block_Adminhtml_Trash_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('trashGrid');
        $this->setDefaultSort('purchase_order_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('supplier/purchaseorder')->getCollection();

        $collection -> addFieldToFilter(
            'trash',
            array('eq' => Furniturestore_Supplier_Model_Purchaseorder::IS_TRASH,
            )
        );
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $condorder = '';
        if ($filter) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'purchase_on') {
                    $condorder = $key;
                }
            }
        }
        if ($condorder) {
            $condorder = Mage::helper('supplier')->filterDates($condorder, array('from', 'to'));
            if (isset($condorder['from']) && ($from = $condorder['from']) ) {
                $from = date('Y-m-d', strtotime($from));
                $collection->addFieldToFilter('purchase_on', array('gteq' => $from));
            }
            if (isset($condorder['to']) && ($to = $condorder['to']) ) {
                $to = date('Y-m-d', strtotime($to));
                $to .= ' 23:59:59';
                $collection->addFieldToFilter('purchase_on', array('lteq' => $to));
            }
        }
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

        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created by'),
            'width' => '80px',
            'align' => 'left',
            'index' => 'created_by'
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

        $this->addColumn('status', array(
            'header' => Mage::helper('supplier')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('supplier/purchaseorder')->getReturnOrderStatus(),
        ));
        $labelAction = __('Restore');
        $this->addColumn('action', array(
            'header' => Mage::helper('supplier')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $labelAction,
                    'url' => array('base' => '*/*/recycle'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true
        ));

        $this->addExportType('*/*/exportCsvTrash', Mage::helper('supplier')->__('CSV'));
        $this->addExportType('*/*/exportExcelTrash', Mage::helper('supplier')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function filterSupplierCallback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->getSelect()->where('supplier_id = ' . $value);
        }
        return $this;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('purchase_order_id');
        $this->getMassactionBlock()->setFormFieldName('purchaseorder_ids');

        $this->getMassactionBlock()->addItem('recycle', array(
            'label' => Mage::helper('supplier')->__('Restore'),
            'url' => $this->getUrl('*/*/massRecycle', array('_current' => true))
        ));
        return $this;
    }

    public function filterCreatedOn($collection, $column) {
        return $this;
    }
    public function filterTotalAmount($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->where('total_amount >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->where('total_amount <= ?', $filter['to']);
        }
    }

    public function getRowClass($item){
        if($item->getData('status') == Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS){
            return 'pending-po';
        }
    }

}