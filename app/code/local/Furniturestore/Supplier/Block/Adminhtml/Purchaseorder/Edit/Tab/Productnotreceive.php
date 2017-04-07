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
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Supplier Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Productnotreceive extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productnotreceiveGrid');
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('returnorder_filter');

    }
    
    protected function  _prepareLayout()
    {
        $this->setChild('print_receipt_underselling_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('inventorypurchasing')->__('Print Shortfall Items'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/printreceiptforunderselling', array('purchaseorder_id'=>$this->getRequest()->getParam('id'),'_current'=>false)).'\')',
//                    'class' => 'add',
                    'style' => 'float:right'
                ))
        );
        return parent::_prepareLayout();
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Inventory_Block_Adminhtml_Supplier_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('inventorypurchasing/purchaseorder_product')->getCollection()
                            ->addFieldToFilter('purchase_order_id', $this->getRequest()->getParam('id'))
                            ->addFieldToFilter('qty', array ('gt' => new Zend_Db_Expr('qty_recieved')))
                            ->setIsGroupCountSql(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();    
    }
    
    /**
     * prepare columns for this grid
     *
     * @return
     */
    protected function _prepareColumns() {
        $currency = $this->getCurrency();

        $this->addColumn('product_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'product_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'align' => 'left',
            'index' => 'product_name',
            'renderer' => 'inventorypurchasing/adminhtml_purchaseorder_edit_tab_renderer_product',
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'product_sku'
        ));


        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'filter' => false,
            'renderer' => 'inventoryplus/adminhtml_renderer_productimage'
        ));


        $this->addColumn('supplier_sku', array(
            'header' => Mage::helper('inventorypurchasing')->__('Supplier SKU'),
            'name' => 'supplier_sku',
            'index' => 'supplier_sku',
            'width' => '80px',
            'filter' => false
        ));
        $this->addColumn('qty', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Ordered'),
            'name' => 'qty',
            'type' => 'number',
            'index' => 'qty',
            'filter' => false
        ));


        $this->addColumn('qty_recieved', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Received'),
            'name' => 'qty_recieved',
            'type' => 'number',
            'index' => 'qty_recieved',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('qty_returned', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Returned'),
            'name' => 'qty_returned',
            'type' => 'number',
            'index' => 'qty_returned',
            'filter' => false,
            'sortable' => false
        ));
        $this->addColumn('qty_not_receive', array(
            'header' => Mage::helper('inventorypurchasing')->__('Qty Shortfall'),
            'renderer' => 'inventorypurchasing/adminhtml_purchaseorder_edit_tab_renderer_productnotreceive',
            'index' => 'qty_not_receive',
            'align' => 'right',
            'width' => '80px',
            'filter' => false,
            'sortable' => false
        ));

    }
    
    /**
     * prepare mass action for this grid
     *
     * @return
     */
   
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        
    }

    public function  getSearchButtonHtml()
    {
        return parent::getSearchButtonHtml() . $this->getChildHtml('print_receipt_underselling_button');
    }
}