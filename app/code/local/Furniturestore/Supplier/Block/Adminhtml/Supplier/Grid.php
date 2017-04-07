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

        $filter = $this->getParam($this->getVarNameFilter(), null);
        $condorder = '';
        if ($filter) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'last_purchase_order') {
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
                $collection->addFieldToFilter('last_purchase_order', array('gteq' => $from));
            }
            if ($to) {
                $to = date('Y-m-d', strtotime($to));
                $to .= ' 23:59:59';
                $collection->addFieldToFilter('last_purchase_order', array('lteq' => $to));
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $this->addColumn('supplier_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'align' =>'right',
            'width' => '50px',
            'index' => 'supplier_id',
        ));

        $this->addColumn('supplier_name', array(
            'header' => Mage::helper('supplier')->__('Supplier Name'),
            'align' =>'left',
            'index' => 'supplier_name',
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created By'),
            'align' =>'left',
            'index' => 'created_by',
        ));
        $this->addColumn('total_order', array(
            'header' => Mage::helper('supplier')->__('Purchase Order'),
            'align' =>'left',
            'index' => 'total_order',
        ));
        $this->addColumn('purchase_order', array(
            'header' => Mage::helper('supplier')->__('Purchase Order Value'),
            'align' =>'left',
            'type' => 'price',
            'currency_code' => $currencyCode,
            'index' => 'purchase_order',
        ));
        $this->addColumn('return_order', array(
            'header' => Mage::helper('supplier')->__('Return Order Value'),
            'align' =>'left',
            'type' => 'price',
            'currency_code' => $currencyCode,
            'index' => 'return_order',
        ));

        $this->addColumn('last_purchase_order', array(
            'header' => Mage::helper('supplier')->__('Last Purchase Order On'),
            'align' =>'left',
            'type' => 'date',
            'default' => '--',
            'index' => 'last_purchase_order',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

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