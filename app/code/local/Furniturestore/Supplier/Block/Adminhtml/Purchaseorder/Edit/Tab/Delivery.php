<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Tab_Delivery extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('deliveryGrid');
        $this->setDefaultSort('delivery_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if ($this->getPurchaseOrder() && $this->getPurchaseOrder()->getId()) {
            $this->setDefaultFilter(array('in_deliveries' => 1));
        }
    }

    protected function _prepareLayout() {
        if ($purchaseOrderId = $this->getRequest()->getParam('id')) {
            $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
//            $resource = Mage::getSingleton('core/resource');
//            $readConnection = $resource->getConnection('core_read');
//
//            $sql = 'SELECT purchase_order_product_id from ' . $resource->getTableName("erp_inventory_purchase_order_product") . ' WHERE (purchase_order_id = ' . $this->getRequest()->getParam("id") . ') AND (qty_recieved < qty)';
//            $results = $readConnection->fetchAll($sql);
            $pStatus = $purchaseOrder->getStatus();
            if ($pStatus != Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS){
                if ($this->checkCreateAllDelivery()
                    && ($pStatus == Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS
                        || $pStatus == Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS)
                ) {
                    $this->setChild('create_all_delivery_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('supplier')->__('Create all deliveries'),
                            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/alldelivery', array('purchaseorder_id' => $this->getRequest()->getParam('id'), 'action' => 'alldelivery', '_current' => false)) . '\')',
                            'class' => 'add',
                            'style' => 'float:right'
                        ))
                    );
                }
                if ($this->checkCreateNewDelivery()
                    && ($pStatus == Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS
                        || $pStatus == Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS)
                ) {
                    $this->setChild('create_delivery_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('supplier')->__('Create a new delivery'),
                            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/newdelivery', array('purchaseorder_id' => $this->getRequest()->getParam('id'), 'warehouse_ids' => $purchaseOrder->getWarehouseId(), 'action' => 'newdelivery', '_current' => false)) . '\')',
                            'class' => 'add',
                            'style' => 'float:right'
                        ))
                    );
                }
            }

            Mage::dispatchEvent('add_more_button_delivery', array('grid' => $this));
        }
        return parent::_prepareLayout();
    }

    protected function _addColumnFilterToCollection($column) {
        if ($column->getId() == 'in_deliveries') {
            $deliveryIds = $this->_getSelectedDeliveries();
            if (empty($deliveryIds))
                $deliveryIds = 0;
            if ($column->getFilter()->getValue())
                $this->getCollection()->addFieldToFilter('delivery_id', array('in' => $deliveryIds));
            elseif ($deliveryIds)
                $this->getCollection()->addFieldToFilter('delivery_id', array('nin' => $deliveryIds));
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection() {
        $resource = Mage::getSingleton('core/resource');
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('supplier/delivery')->getCollection()
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId);

        $filter = $this->getParam($this->getVarNameFilter(), null);
        
        if ($filter) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            foreach ($data as $value => $key) {
                if ($value == 'delivery_date') {
                    $condorder = $key;
                }
            }
        }
        
    if (isset($condorder['from']) || isset($condorder['to'])) {
            $condorder = Mage::helper('supplier')->filterDates($condorder, array('from', 'to'));
            if(isset($condorder['from']))
                $from = $condorder['from'];
            if(isset($condorder['to']))
                $to = $condorder['to'];
            if (isset($from)) {
                $from = date('Y-m-d', strtotime($from));
                $collection->addFieldToFilter('delivery_date', array('gteq' => $from));
            }
            if (isset($to)) {
                $to = date('Y-m-d', strtotime($to));
                $to .= ' 23:59:59';
                $collection->addFieldToFilter('delivery_date', array('lteq' => $to));
            }
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();

        $this->addColumn('delivery_id', array(
            'header' => Mage::helper('supplier')->__('ID'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'delivery_id',
        ));

        $this->addColumn('delivery_date', array(
            'header' => Mage::helper('catalog')->__('Delivery Date'),
            'sortable' => true,
            'width' => '60',
            'type' => 'date',
            'index' => 'delivery_date',
            'filter_condition_callback' => array($this, 'filterCreatedOn')
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Product Name'),
            'align' => 'left',
            'index' => 'product_name',
        ));


        $this->addColumn('product_sku', array(
            'header' => Mage::helper('catalog')->__('Product SKU'),
            'width' => '80px',
            'index' => 'product_sku'
        ));

        $this->addColumn('product_image', array(
            'header' => Mage::helper('catalog')->__('Image'),
            'width' => '90px',
            'renderer' => 'supplier/adminhtml_renderer_productimage',
            'filter' => false,
        ));

//

        $this->addColumn('qty_delivery', array(
            'header' => Mage::helper('supplier')->__('Total Qty Received'),
            'name' => 'qty_delivery',
            'type' => 'number',
            'index' => 'qty_delivery'
        ));

        $this->addColumn('created_by', array(
            'header' => Mage::helper('supplier')->__('Created by'),
            'name' => 'create_by',
            'index' => 'created_by'
        ));

        Mage::dispatchEvent('deliveried_product_grid_after_created', array('grid' => $this));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/deliveryGrid', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                ));
    }

    protected function _getSelectedDeliveries() {
        $deliveries = $this->getDeliveries();
        if (!is_array($deliveries)) {
            $deliveries = array_keys($this->getSelectedRelatedDeliveries());
        }
        return $deliveries;
    }

    public function getSelectedRelatedDeliveries() {
        $deliveries = array();
        $purchaseOrder = $this->getPurchaseOrder();
        $deliveryCollection = Mage::getResourceModel('supplier/delivery_collection')
                ->addFieldToFilter('purchase_order_id', $purchaseOrder->getId());
        foreach ($deliveryCollection as $delivery) {
            $deliveries[$delivery->getDeliveryId()] = array('qty' => $delivery->getQty());
        }
        return $deliveries;
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

    public function getResetFilterButtonHtml() {
        $allButtonShow = $this->getChildHtml('create_delivery_button') . $this->getChildHtml('create_all_delivery_button') . parent::getResetFilterButtonHtml();
        Mage::dispatchEvent('add_more_button_delivery_position', array('allbuttonshow' => &$allButtonShow, 'grid' => $this));
        return $allButtonShow;
    }

    public function checkCreateNewDelivery() {
        $canDelivery = true;
        $adminId = Mage::getModel('admin/session')->getUser()->getId();
        if (!$adminId)
            return null;

        return $canDelivery;
    }

    public function checkCreateAllDelivery() {	
		$canAllDelivery = true;
		$purchaseOrderId = $this->getRequest()->getParam('id');
		return $canAllDelivery;
    }

    public function filterCreatedOn($collection, $column) {
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return false;
    }

}