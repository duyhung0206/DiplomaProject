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
class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Tab_Returnorder extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('returnorderGrid');
        $this->setDefaultSort('return_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('returnorder_filter');
    }

    protected function _prepareLayout() {
        $totalProductRecieved = Mage::helper('supplier/purchaseorder')->getDataByPurchaseOrderId($this->getRequest()->getParam('id'), 'total_products_recieved');
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        if (($totalProductRecieved > 0) && $this->checkCreateReturn()) {
            $this->setChild('return_order_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label' => Mage::helper('supplier')->__('Return Order'),
                                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/newreturnorder', array('purchaseorder_id' => $this->getRequest()->getParam('id'), 'action' => 'newreturnorder', '_current' => false)) . '\')',
                                'class' => 'add',
                                'style' => 'float:right'
                            ))
            );
        }
        if ($totalProductRecieved > 0 && $this->checkCreateReturnAll()) {
            $this->setChild('return_all_order_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'label' => Mage::helper('supplier')->__('Return All Orders'),
                                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/returnallorder', array('purchaseorder_id' => $this->getRequest()->getParam('id'), 'action' => 'newreturnorder', '_current' => false)) . '\')',
                                'class' => 'add',
                                'style' => 'float:right'
                            ))
            );
        }
        $this->setChild('print_receipt_return_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('supplier')->__('Print returned Items'),
                            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/printreceiptforreturned', array('purchaseorder_id' => $this->getRequest()->getParam('id'), '_current' => false)) . '\')',
//                    'class' => 'add',
                            'style' => 'float:right'
                        ))
        );
        return parent::_prepareLayout();
    }

    protected function _prepareCollection() {
        $purchase_order_id = Mage::app()->getRequest()->getParam('id');
        $collection = Mage::getModel('supplier/returnorder')
                        ->getCollection()->addFieldToFilter('purchase_order_id', $purchase_order_id);
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

        $this->addColumn('return_date', array(
            'header' => Mage::helper('supplier')->__('Return Date'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'return_date',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('supplier')->__('Name'),
            'align' => 'left',
            'index' => 'product_name',
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'renderer' => 'supplier/adminhtml_renderer_productimage',
            'filter' => false,
        ));

        $this->addColumn('qty_returned', array(
            'header' => Mage::helper('supplier')->__('Qty Returned'),
            'width' => '150px',
            'name' => 'qty_returned',
            'type' => 'number',
            'index' => 'qty_returned'
        ));

        $this->addColumn('create_by', array(
            'header' => Mage::helper('supplier')->__('Create by'),
            'name' => 'create_by',
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
        return null;
    }

    public function getSearchButtonHtml() {
        return parent::getSearchButtonHtml() . $this->getChildHtml('print_receipt_return_button') . $this->getChildHtml('return_order_button') . $this->getChildHtml('return_all_order_button') . $this->getChildHtml('cancel_order_button');
    }

    public function checkCreateReturnAll() {
        $canReturnAll = true;
        if(!$this->checkCreateReturn())
            $canReturnAll = false;
        return $canReturnAll;
    }

    public function checkCreateReturn() {
        $canReturn = false;
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
//        if($purchaseOrder->getStatus() == Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS){
//            return false;
//        }
        $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
            ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
        foreach ($purchaseOrderProducts as $purchaseOrderProduct){
            $qty_recieved = $purchaseOrderProduct->getData('qty_recieved');
            $qty_returned = $purchaseOrderProduct->getData('qty_returned');
            if($qty_recieved > $qty_returned)
                $canReturn = true;
        }
        return $canReturn;
    }

}
