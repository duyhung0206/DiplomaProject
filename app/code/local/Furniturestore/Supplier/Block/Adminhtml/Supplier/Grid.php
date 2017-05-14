<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:29
 */

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('supplierGrid');
        $this->setDefaultSort('supplier_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('supplier/supplier')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('purchaseorder' => $collection->getTable('supplier/purchaseorder')), 'main_table.supplier_id=purchaseorder.supplier_id', array('purchase_order_id','total_products','change_rate','total_amount','total_product_refunded', 'purchase_on')
            );

        $collection->getSelect()->group('main_table.supplier_id');
        $collection->getSelect()->columns(array(
            'total_products' => 'SUM(purchaseorder.total_products)',
            'total_products_return' => 'SUM(purchaseorder.total_product_refunded)',
            'total_amount' => 'SUM(purchaseorder.total_amount/purchaseorder.change_rate)',
            'total_order' => 'COUNT(purchaseorder.purchase_order_id)',
        ));
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
//        Zend_Debug::dump($collection->getData());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $this->addColumn('supplier_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'align' =>'right',
            'width' => '20px',
            'type' => 'number',
            'index' => 'supplier_id',
            'filter_index' => "main_table.supplier_id",
        ));

        $this->addColumn('supplier_name', array(
            'header' => Mage::helper('supplier')->__('Supplier Name'),
            'align' =>'left',
            'type' => 'text',
            'index' => 'supplier_name',
            'filter_index' => "main_table.supplier_name",
        ));

        $this->addColumn('purchase_on', array(
            'header' => Mage::helper('supplier')->__('Created By'),
            'align' =>'left',
            'index' => 'purchase_on',
            'filter_index' => "main_table.purchase_on",
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created By'),
            'align' =>'left',
            'index' => 'created_by',
            'filter_index' => "main_table.created_by",
        ));

        $this->addColumn('total_order', array(
            'header' => Mage::helper('supplier')->__('Purchase Order'),
            'align' =>'left',
            'type' => 'number',
            'index' => 'total_order',
            'filter_index' => "COUNT(purchaseorder.purchase_order_id)",
            'filter_condition_callback' => array($this, '_filterTotalOrderCallback')
        ));
        $this->addColumn('total_amount', array(
            'header' => Mage::helper('supplier')->__('Purchase Order Value'),
            'align' =>'left',
            'type' => 'price',
            'currency_code' => $currencyCode,
            'index' => 'total_amount',
            'filter_index' => "SUM(purchaseorder.total_amount*purchaseorder.change_rate)",
            'filter_condition_callback' => array($this, '_filterTotalAmountCallback')
        ));
        $this->addColumn('total_products', array(
            'header' => Mage::helper('supplier')->__('Total qty'),
            'align' =>'left',
            'type' => 'number',
            'index' => 'total_products',
            'filter_index' => "SUM(purchaseorder.total_products)",
            'filter_condition_callback' => array($this, '_filterTotalProductsCallback')
        ));
        $this->addColumn('total_products_return', array(
            'header' => Mage::helper('supplier')->__('Total qty return'),
            'align' =>'left',
            'type' => 'number',
            'index' => 'total_products_return',
            'filter_index' => "SUM(purchaseorder.total_product_refunded)",
            'filter_condition_callback' => array($this, '_filterTotalProductRefundedCallback')
        ));
        $this->addColumnAfter('purchase_on', array(
            'header' => Mage::helper('supplier')->__('Last Purchase Order On'),
            'align' =>'left',
            'type' => 'date',
//            'after' => 'total_products_return',
//            'default' => '--',
            'index' => 'purchase_on',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ), 'total_products_return');

        $this->addColumn('supplier_status', array(
            'header' => Mage::helper('supplier')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'supplier_status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('lowstock', array(
            'header' => Mage::helper('supplier')->__('Product low stock'),
            'align' => 'left',
            'width' => '250px',
            'sortable'  => false,
            'index' => 'lowstock',
            'filter' => false,
            'renderer' => 'supplier/adminhtml_renderer_lowstock',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('supplier')->__('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('supplier')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('supplier')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('supplier')->__('XML'));

        return parent::_prepareColumns();
    }

    public function filterCreatedOn($collection, $column) {
        return $this;
    }

    protected function _filterCreatedByCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        $collection->addFieldToFilter('created_by', $filter);
//
//        Zend_Debug::dump($column->getFilter());
//        Zend_Debug::dump('aa');
//        Zend_Debug::dump($column->getFilter()->getData());
//        Zend_Debug::dump($filter);
//        die('1');
//        if (isset($filter['from']) && $filter['from']) {
//            $collection->getSelect()->having('SUM(purchaseorder.total_product_refunded) >= ?', $filter['from']);
//        }
//        if (isset($filter['to']) && $filter['to']) {
//            $collection->getSelect()->having('SUM(purchaseorder.total_product_refunded) <= ?', $filter['to']);
//        }
    }

    protected function _filterTotalProductRefundedCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_product_refunded) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_product_refunded) <= ?', $filter['to']);
        }
    }

    protected function _filterTotalProductsCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_products) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_products) <= ?', $filter['to']);
        }
    }

    protected function _filterTotalAmountCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_amount*purchaseorder.change_rate) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having('SUM(purchaseorder.total_amount*purchaseorder.change_rate) <= ?', $filter['to']);
        }
    }

    protected function _filterTotalOrderCallback($collection, $column) {
        $filter = $column->getFilter()->getValue();
        if (isset($filter['from']) && $filter['from']) {
            $collection->getSelect()->having('COUNT(purchaseorder.purchase_order_id) >= ?', $filter['from']);
        }
        if (isset($filter['to']) && $filter['to']) {
            $collection->getSelect()->having('COUNT(purchaseorder.purchase_order_id) <= ?', $filter['to']);
        }
    }
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid');
    }

}