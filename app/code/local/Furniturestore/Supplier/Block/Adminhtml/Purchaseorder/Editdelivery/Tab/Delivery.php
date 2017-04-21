<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Editdelivery_Tab_Delivery
    extends Mage_Adminhtml_Block_Widget_Grid{

    public function __construct() {
        parent::__construct();
        $this->setId('productGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds))
                $productIds = 0;
            if ($column->getFilter()->getValue())
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            elseif ($productIds)
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {
        $purchaseorder_id = $this->getRequest()->getParam('purchaseorder_id');
        $purchaseorderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                ->addFieldToFilter('purchase_order_id', $purchaseorder_id);
        $filterData = $this->getRequest()->getParam('filter');
        $filter_encoded = Mage::helper('adminhtml')->prepareFilterString($filterData);
        $productIds = array();
        foreach ($purchaseorderProducts as $purchaseorderProduct) {
            if ($purchaseorderProduct->getQtyRecieved() < $purchaseorderProduct->getQty())
                $productIds[] = $purchaseorderProduct->getProductId();
        }
        $collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $productIds));
        $collection->getSelect()
            ->joinLeft(
                array(
                    'purchaseorderproduct' => $collection->getTable('supplier/purchaseorder_product')
                ),
                'purchaseorderproduct.product_id = e.entity_id and purchaseorderproduct.purchase_order_id IN (' . "'" . $purchaseorder_id . "'" . ')',
                array(
                    'qty' => "purchaseorderproduct.qty",
                    'qty_recieved' => 'purchaseorderproduct.qty_recieved',
                )
            );
        if ($storeId = $this->getRequest()->getParam('store', 0))
            $collection->addStoreFilter($storeId);

        $collection->getSelect()->group('e.entity_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id',
            'use_index' => true,
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('supplier')->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();


        $this->addColumn('product_sku', array(
            'header' => Mage::helper('supplier')->__('SKU'),
            'width' => '80px',
            'index' => 'sku'
        ));
        
        $this->addColumn('qty', array(
            'header' => Mage::helper('supplier')->__('Total Qty Ordered'),
            'name' => 'qty',
            'type' => 'number',
            'index' => 'qty',
            'filter' => false
        ));
        $this->addColumn('qty_recieved', array(
            'header' => Mage::helper('supplier')->__('Total Qty Received'),
            'name' => 'qty_recived',
            'type' => 'number',
            'index' => 'qty_recieved',
            'filter' => false
        ));

        $this->addColumn('qty_delivery', array(
            'header' => 'Qty delivered',
            'name' => 'qty_delivery',
            'type' => 'number',
            'filter' => false,
            'editable' => true,
            'edit_only' => true,
            'align' => 'right',
            'sortable' => false,
            'renderer' => 'supplier/adminhtml_purchaseorder_editdelivery_renderer_qtydelivery'
        ));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/preparedeliveryGrid', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
        ));
    }

    public function _getSelectedProducts() {
        $products = $this->getProducts();
        if (!is_array($products) || Mage::getModel('admin/session')->getData('delivery_purchaseorder_product_import')) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    public function getSelectedProducts() {
        $purchaseOrder = $this->getPurchaseOrder();
        $products = array();
        $purchaseOrderProducts = Mage::getResourceModel('supplier/purchaseorder_product_collection')
                ->addFieldToFilter('purchase_order_id', $this->getRequest()->getParam('purchaseorder_id'));

        if ($deliveryProductImports = Mage::getModel('admin/session')->getData('delivery_purchaseorder_product_import')) {
            $productModel = Mage::getModel('catalog/product');
            foreach ($deliveryProductImports as $productImport) {
                $productId = $productModel->getIdBySku($productImport['SKU']);
                if ($productId) {
                    foreach ($productImport as $pImport => $p) {
                        if ($pImport == 'QTY') {
                            if ($pImport[1]) {
                                $products[$productId]['QTY'] = $p;
                            }
                        }
                    }
                }
            }
        }else{
            $purchaseOrderProduct = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
            foreach ($purchaseOrderProduct as $product) {
                if($product->getData('qty') > $product->getData('qty_recieved')){
                    $products[$product->getProductId()]['QTY'] = $product->getData('qty') - $product->getData('qty_recieved');
                }
            }
        }
        return $products;
    }


    public function getPurchaseOrder() {
        return Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('purchaseorder_id'));
    }

    /**
     * get currrent store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getRowUrl($row) {
        return false;
    }
    
    public function getRowClass($row) {
        return 'row-'. $row->getEntityId();
    }

}
