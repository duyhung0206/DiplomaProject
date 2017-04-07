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

    protected function _prepareColumns()
    {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $this->addColumn('purchase_order_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'align' =>'right',
            'width' => '50px',
            'index' => 'purchase_order_id',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('supplier')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('supplier')->__('XML'));

        return parent::_prepareColumns();
    }

}