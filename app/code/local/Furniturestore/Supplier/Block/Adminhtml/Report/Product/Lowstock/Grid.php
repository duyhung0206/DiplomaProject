<?php

class Furniturestore_Supplier_Block_Adminhtml_Report_Product_Lowstock_Grid extends Mage_Adminhtml_Block_Report_Product_Lowstock_Grid
{
    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        /** @var $collection Mage_Reports_Model_Resource_Product_Lowstock_Collection  */
        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('supplier', array(
            'header'    =>  Mage::helper('reports')->__('Supplier'),
            'sortable'  =>  false,
            'filter'    =>  false,
            'index'     =>  'supplier',
            'renderer'  =>  'Furniturestore_Supplier_Block_Adminhtml_Report_Renderer_Supplier',
            'after'     =>  'qty'
        ), 'qty');

        return parent::_prepareColumns();
    }
}
