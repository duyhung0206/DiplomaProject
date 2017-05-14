<?php


class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid {

    protected $_editable = true;

    public function __construct() {
        parent::__construct();
        $this->checkEditable();
        $this->setId('productGrid');
        if (!$this->_editable) {
            $this->setDefaultSort('entity_id');
        } else {
            $this->setDefaultSort('product_id');
        }
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if (($this->getPurchaseOrder() && $this->getPurchaseOrder()->getId()) || Mage::getModel('admin/session')->getData('purchaseorder_product_import')) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    protected function checkEditable() {
        if ($this->getPurchaseOrder() && $this->getPurchaseOrder()->getId()) {
            $deliveries = Mage::getModel('supplier/delivery')
                    ->getCollection()
                    ->addFieldToFilter('purchase_order_id', $this->getPurchaseOrder()->getId());
            if (($deliveries->getSize() > 0)
                || in_array($this->getPurchaseOrder()->getStatus(), array(
                    Furniturestore_Supplier_Model_Purchaseorder::CANCELED_STATUS,
                    Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS,
                    Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS,
                    Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS))) {
                $this->_editable = false;
            }
        }
    }

    public function _getDisabledProducts() {
        $disableCheck = false;
        $products = array();
        if (!$disableCheck)
            return $products;
        $supplierProducts = Mage::getModel('supplier/product')->getCollection();
        if (count($supplierProducts)) {
            foreach ($supplierProducts as $product) {
                $products[$product->getProductId()] = $product->getProductId();
            }
        }
        return array_keys($products);
    }

    protected function getCurrency() {
        if (!$this->getRequest()->getParam('id')) {
            $currency = $this->getRequest()->getParam('currency');
        } else {
            $currency = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('id'))->getCurrency();
        }
//        Zend_Debug::dump($currency);
//        die('1');
        return $currency;
    }

    protected function getChangeRate() {
        if (!$this->getRequest()->getParam('id')) {
            $currencyRate = $this->getRequest()->getParam('change_rate');
        } else {
            $currencyRate = $this->getPurchaseOrder()->getData('change_rate');
        }
        return $currencyRate;
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds))
                $productIds = 0;
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } elseif ($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
            }
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection()
    {
        if ($this->_editable) {
            $supplier_id = $this->getRequest()->getParam('supplier_id');
            if (!$supplier_id) {
                $purchaseOrderId = $this->getRequest()->getParam('id');
                if ($purchaseOrderId) {
                    $purchaseOrder = $this->getPurchaseOrder();
                    $supplier_id = $purchaseOrder->getSupplierId();
                } else {
                    return;
                }
            }
            $productIds = array();
            $supplierProducts = Mage::getModel('supplier/product')->getCollection()
                                    ->addFieldToFilter('supplier_id', $supplier_id);
            foreach ($supplierProducts as $supplierProduct) {
                $productIds[] = $supplierProduct->getProductId();
            }
            $collection = Mage::getResourceModel('catalog/product_collection')
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter('entity_id', array('in' => $productIds));
            if ($storeId = $this->getRequest()->getParam('store', 0)) {
                $collection->addStoreFilter($storeId);
            }
            $attributeCode = 'cost';
            $alias     = $attributeCode . '_table';
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
            $collection->getSelect()->joinLeft(
                array($alias => $attribute->getBackendTable()),
                "e.entity_id = $alias.entity_id AND $alias.attribute_id={$attribute->getId()}",
                array($attributeCode => 'value')
            );
            $collection->getSelect()
                        ->joinLeft(
                            array(
                                    'supplierproduct' => $collection->getTable('supplier/product')
                            ),
                            'supplierproduct.product_id = e.entity_id and supplierproduct.supplier_id IN (' . "'" . $supplier_id . "'" . ')',
                            array(
                                'cost_product' => "supplierproduct.cost",
                                'tax' => 'supplierproduct.tax',
                                'discount' => 'supplierproduct.discount',
                                'supplier_sku' => 'supplierproduct.supplier_sku',
                            )
                        );
            if ($this->getRequest()->getParam('id')) {
                $collection->getSelect()
                    ->joinLeft(
                        array(
                            'purchaseproduct' => $collection->getTable('furniturestore_purchase_order_product')
                        ),
                        "purchase_order_id = " . $this->getRequest()->getParam('id') . " AND (e.entity_id = purchaseproduct.product_id)",
                        array(
                            "qty" => "IFNULL(purchaseproduct.qty,0)",
                            "qty_recieved" => "IFNULL(purchaseproduct.qty_recieved,0)",
                            "qty_returned" => "IFNULL(purchaseproduct.qty_returned,0)",
                            "cost_product_purchase" => "IF(IFNULL(purchaseproduct.cost,supplierproduct.cost) = 0, cost_table.value, IFNULL(purchaseproduct.cost,supplierproduct.cost))",
                            "discount" => "IFNULL(purchaseproduct.discount,supplierproduct.discount)",
                            "tax" => "IFNULL(purchaseproduct.tax,supplierproduct.tax)",
                            'supplier_sku' => 'IFNULL(purchaseproduct.supplier_sku,supplierproduct.supplier_sku)',
                        )
                    );

            }
            $collection->getSelect()->group('e.entity_id');

        } else {
            $collection = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                                ->addFieldToFilter('purchase_order_id', $this->getRequest()->getParam('id'));
//                                ->setIsGroupCountSql(true);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     *
     */
    protected function _prepareColumns() {
        $currency = $this->getCurrency();
        if ($this->_editable) {
            $this->addColumn('in_products', array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
                'use_index' => true,
                //'disabled_values' => $this->_getDisabledProducts()
            ));
        }
        if ($this->_editable) {
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('catalog')->__('ID'),
                'sortable' => true,
                'width' => '60',
                'index' => 'entity_id'
            ));
        } else {
            $this->addColumn('product_id', array(
                'header' => Mage::helper('catalog')->__('ID'),
                'sortable' => true,
                'width' => '60',
                'index' => 'product_id'
            ));
        }

        if ($this->_editable) {
            $this->addColumn('product_name', array(
                'header' => Mage::helper('catalog')->__('Name'),
                'align' => 'left',
                'index' => 'name',
            ));
        } else {
            $this->addColumn('product_name', array(
                'header' => Mage::helper('catalog')->__('Name'),
                'align' => 'left',
                'index' => 'product_name',
                'renderer' => 'supplier/adminhtml_purchaseorder_edit_tab_renderer_product',
            ));
        }

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();
        if ($this->_editable) {
            $this->addColumn('product_sku', array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku'
            ));
        } else {
            $this->addColumn('product_sku', array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'product_sku'
            ));
        }

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'filter' => false,
            'renderer' => 'supplier/adminhtml_renderer_productimage'
        ));

        $editable = $this->_editable;
        if ($this->_editable) {
            if(!$this->getRequest()->getParam('id')){
                 $this->addColumn('cost_product', array(
                'header' => Mage::helper('supplier')->__('Cost Price <br />(' . $currency . ')'),
                'name' => 'cost_product',
                'index' => 'cost_product',
                'type' => 'number',
                'filter' => false,
                'editable' => $editable,
                'edit_only' => $editable,
                ));
            } else {
                $this->addColumn('cost_product', array(
                'header' => Mage::helper('supplier')->__('Cost Price <br />(' . $currency . ')'),
                'name' => 'cost_product',
                'index' => 'cost_product_purchase',
                'type' => 'number',
                'filter' => false,
                'editable' => $editable,
                'edit_only' => $editable,
                ));
            }
        } else {
            $this->addColumn('cost', array(
                'header' => Mage::helper('supplier')->__('Cost Price <br />(' . $currency . ')'),
                'name' => 'cost',
                'type' => 'currency',
                'currency_code' => (string) $currency,
                'index' => 'cost',
                'filter' => false,
                'editable' => $editable,
                'edit_only' => $editable,
            ));
        }
        $this->addColumn('tax', array(
            'header' => Mage::helper('supplier')->__('Tax(%)'),
            'name' => 'tax',
            'type' => 'number',
            'index' => 'tax',
            'filter' => false,
            'editable' => $editable,
            'edit_only' => $editable,
        ));

        $this->addColumn('discount', array(
            'header' => Mage::helper('supplier')->__('Discount(%)'),
            'name' => 'discount',
            'type' => 'number',
            'index' => 'discount',
            'filter' => false,
            'editable' => $editable,
            'edit_only' => $editable,
        ));

        $this->addColumn('supplier_sku', array(
            'header' => Mage::helper('supplier')->__('Supplier SKU'),
            'name' => 'supplier_sku',
            'index' => 'supplier_sku',
            'filter' => false,
            'editable' => $editable,
            'edit_only' => $editable,
        ));
        if ($this->getRequest()->getParam('id')) {
            $this->addColumn('qty', array(
                'header' => Mage::helper('supplier')->__('Total Qty Ordered'),
                'name' => 'qty',
                'type' => 'number',
                'editable' => $editable,
                'edit_only' => $editable,
                'index' => 'qty',
                'filter' => false
            ));
        }else{
            $this->addColumn('qty', array(
                'header' => Mage::helper('supplier')->__('Qty order'),
                'name' => 'qty',
                'type' => 'number',
                'index' => 'qty',
                'filter' => false,
                'editable' => true,
                'edit_only' => true,
                'align' => 'right',
                'sortable' => false,
                'renderer' => 'supplier/adminhtml_purchaseorder_edit_tab_renderer_AvailableQty'
            ));
        }

        if ($this->getRequest()->getParam('id')) {
            $this->addColumn('qty_recieved', array(
                'header' => Mage::helper('supplier')->__('Total Qty Received'),
                'name' => 'qty_recieved',
                'type' => 'number',
                'index' => 'qty_recieved',
                'filter' => false,
                'sortable' => false
            ));
        }
        if ($this->getRequest()->getParam('id')) {
            $this->addColumn('qty_returned', array(
                'header' => Mage::helper('supplier')->__('Total Qty Returned'),
                'name' => 'qty_returned',
                'type' => 'number',
                'index' => 'qty_returned',
                'filter' => false,
                'sortable' => false
            ));
        }
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/productGrid', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
        ));
    }

    protected function _getSelectedProducts() {
        $productArrays = $this->getProducts();
        $products = '';
        $purchaseorderProducts = array();
        if ($productArrays) {
            $products = array();
            foreach ($productArrays as $productArray) {
                Mage::helper('supplier')->parseStr(urldecode($productArray), $purchaseorderProducts);
                if (count($purchaseorderProducts)) {
                    foreach ($purchaseorderProducts as $pId => $enCoded) {
                        $products[] = $pId;
                    }
                }
            }
        }
        if (!is_array($products) || Mage::getModel('admin/session')->getData('purchaseorder_product_import')) {
            $products = array_keys($this->getSelectedRelatedProducts());
        }
        return $products;

    }

    public function getSelectedRelatedProducts() {
        $products = array();
        $purchaseOrder = $this->getPurchaseOrder();
        $productCollection = Mage::getResourceModel('supplier/purchaseorder_product_collection')
                ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());

//        Zend_Debug::dump($productCollection->getData());
        foreach ($productCollection as $product) {
            $products[$product->getProductId()] = array('qty' => $product->getQty());
        }

        if ($purchaseOrderProductImports = Mage::getModel('admin/session')->getData('purchaseorder_product_import')) {
            $productModel = Mage::getModel('catalog/product');
            foreach ($purchaseOrderProductImports as $productImport) {
                $productId = $productModel->getIdBySku($productImport['SKU']);
                if ($productId) {
                    if (isset($productImport['QTY'])) {
                        $products[$productId]['qty'] = $productImport['QTY'];
                    } else {
                        $products[$productId]['qty'] = 0;
                    }

                    if (isset($productImport['COST'])) {
                        $products[$productId]['cost_product'] = $productImport['COST'];
                    } else {
                        $products[$productId]['cost_product'] = 0;
                    }
                    if (isset($productImport['TAX'])) {
                        $products[$productId]['tax'] = $productImport['TAX'];
                    } else {
                        $products[$productId]['tax'] = 0;
                    }
                    if (isset($productImport['DISCOUNT'])) {
                        $products[$productId]['discount'] = $productImport['DISCOUNT'];
                    } else {
                        $products[$productId]['discount'] = 0;
                    }
                    if (isset($productImport['SUPPLIER_SKU'])) {
                        $products[$productId]['supplier_sku'] = $productImport['SUPPLIER_SKU'];
                    } else {
                        $products[$productId]['supplier_sku'] = 0;
                    }
                    if (isset($productImport['QTY_ORDER'])) {
                        $products[$productId]['qty'] = $productImport['QTY_ORDER'];
                    } else {
                        $products[$productId]['qty'] = 0;
                    }
                }
            }
        }
        return $products;
    }

    public function getPurchaseOrder() {
        return Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('id'));
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

}
