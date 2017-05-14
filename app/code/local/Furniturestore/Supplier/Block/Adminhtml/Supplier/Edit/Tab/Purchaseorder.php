<?php

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Edit_Tab_Purchaseorder extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('purchaseorderGrid');
        $this->setDefaultSort('purchase_order_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('purchaseorder_filter');
    }

    protected function _prepareCollection() {
        $supplier_id = Mage::app()->getRequest()->getParam('id');
        $collection = Mage::getModel('supplier/purchaseorder')->getCollection()
            ->addFieldToFilter('supplier_id', $supplier_id)
            ->addFieldToFilter('trash', array('eq' => Furniturestore_Supplier_Model_Purchaseorder::IS_NOT_TRASH));

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
            $from = $condorder['from'];
            $to = $condorder['to'];
            if ($from) {
                $from = date('Y-m-d', strtotime($from));
                $collection->addFieldToFilter('purchase_on', array('gteq' => $from));
            }
            if ($to) {
                $to = date('Y-m-d', strtotime($to));
                $to .= ' 23:59:59';
                $collection->addFieldToFilter('purchase_on', array('lteq' => $to));
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('purchase_order_id', array(
            'header' => Mage::helper('supplier')->__('Order ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'purchase_order_id',
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created by'),
            'align' => 'left',
            'index' => 'created_by',
            'width' => '80px',
        ));

        $this->addColumn('purchase_on', array(
            'header' => Mage::helper('supplier')->__('Purchased On'),
            'type' => 'date',
            'align' => 'left',
            'index' => 'purchase_on',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

        $this->addColumn('grand_total_excl_tax', array(
            'header' => Mage::helper('supplier')->__('Grand Total Excl .TAX'),
            'width' => '150px',
            'type' => 'number',
			//'sortable'	=> false,
			//'filter' => false,
            'index' => 'total_amount',
            'renderer' => 'Furniturestore_Supplier_Block_Adminhtml_Supplier_Renderertotalexcl',
        ));

        $this->addColumn('grand_total_incl_tax', array(
            'header' => Mage::helper('supplier')->__('Grand Total Incl.TAX'),
            'width' => '150px',
            'align' => 'right',
			//'sortable'	=> false,
			//'filter' => false,
            'type' => 'number',
            'index' => 'total_amount',
            'renderer' => 'Furniturestore_Supplier_Block_Adminhtml_Supplier_Renderertotalincl',
        ));

        $this->addColumn('paid_all', array(
            'header' => Mage::helper('supplier')->__('Paid All'),
            'width' => '150px',
           'type' => 'options',
            'align' => 'right',
            'index' => 'paid_all',
            'options' => Mage::helper('supplier/purchaseorder')->getPaymentStatus()
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('supplier')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('supplier/purchaseorder')->getPurchaseOrderStatus()
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('supplier')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'getter' => 'getPurchaseOrderId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('supplier')->__('View'),
                    'url' => array('base' => 'adminhtml/sup_purchaseorder/edit'),
                    'field' => 'id'
            )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }


    public function getRowUrl($row) {
        return false;
    }

    public function filterCreatedOn($collection, $column) {
        return $this;
    }

}