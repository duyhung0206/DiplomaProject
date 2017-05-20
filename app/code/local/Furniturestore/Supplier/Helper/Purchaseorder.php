<?php

class Furniturestore_Supplier_Helper_Purchaseorder extends Mage_Core_Helper_Abstract {

    public function getStatusDeliveryByProductId($productId){
        $purchaserOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                ->addFieldToFilter('product_id', $productId);
        $purchaserOrderProducts->getSelect()->join('furniturestore_purchase_order as purchaseorder', 'purchaseorder.purchase_order_id=main_table.purchase_order_id', array('status' => 'status'));
        $purchaserOrderProducts->addFieldToFilter('status', array('in' => array(5,11)));
        $html = "";
        foreach ($purchaserOrderProducts as $purchaserOrderProduct){
            $qtyWait = $purchaserOrderProduct->getData('qty') - $purchaserOrderProduct->getData('qty_recieved');
            if($qtyWait > 0){
                $url_edit = Mage::helper('adminhtml')->getUrl('*/sup_purchaseorder/edit', array('id'=> $purchaserOrderProduct->getData('purchase_order_id')));
                $html.= "<a href=".$url_edit.">#PO".$purchaserOrderProduct->getData('purchase_order_id')."(".$qtyWait.")<br/>";
            }
        }
        return $html;
    }

    public function checkEnoughtdelivery($productId){
        $purchaserOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
            ->addFieldToFilter('product_id', $productId);
        $purchaserOrderProducts->getSelect()->join('furniturestore_purchase_order as purchaseorder', 'purchaseorder.purchase_order_id=main_table.purchase_order_id', array('status' => 'status'));
        $purchaserOrderProducts->addFieldToFilter('status', array('in' => array(5,11)));
        $total = 0;
        foreach ($purchaserOrderProducts as $purchaserOrderProduct){
            $qtyWait = $purchaserOrderProduct->getData('qty') - $purchaserOrderProduct->getData('qty_recieved');
            if($qtyWait > 0){
                $total+= $qtyWait;
            }
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        $stockQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        if(($stockQty + $total) > 0){
            return true;
        }else{
            return false;
        }
    }

    /* check this purchase order has delivery or not */
    public function haveDelivery($id = null) {
        if (!$id) {
            $id = Mage::app()->getRequest()->getParam('id');
        }
        if ($purchaseOrderId = $id) {
            $delivery = Mage::getModel('supplier/delivery')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                    ->setPageSize(1)->setCurPage(1)->getFirstItem();
            if ($delivery->getId())
                return true;
        }
        return false;
    }

    public function getReturnOrderStatus() {
        return array(
            Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS => Mage::helper('supplier')->__('Pending'),
            Furniturestore_Supplier_Model_Purchaseorder::WAITING_CONFIRM_STATUS => Mage::helper('supplier')->__('Waiting confirmation'),
            Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('supplier')->__('Waiting delivery'),
            Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS => Mage::helper('supplier')->__('Processing delivery'),
            Furniturestore_Supplier_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('supplier')->__('Canceled'),
            Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS => Mage::helper('supplier')->__('Completed')
        );
    }

    public function getMassPOStatus() {
        return array(
            Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('supplier')->__('Waiting delivery'),
            Furniturestore_Supplier_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('supplier')->__('Canceled')
        );
    }

    public function getShippingMethod() {
        $shippingMethods = Mage::getModel('inventorypurchasing/shippingmethod')
                ->getCollection()
                ->addFieldToFilter('shipping_method_status', 1);
        $shippingArray = array();
        $shippingArray[0] = $this->__('Select shipping method');
        if (count($shippingMethods)) {

            foreach ($shippingMethods as $shipping) {
                $shippingArray[$shipping->getId()] = $shipping->getShippingMethodName();
            }
        }
        $shippingArray['new'] = $this->__('Create a new shipping method');
        return $shippingArray;
    }

    public function getOrderPlaced() {
        return array(
            1 => Mage::helper('supplier')->__('Email'),
            2 => Mage::helper('supplier')->__('Fax'),
            3 => Mage::helper('supplier')->__('N/A'),
            4 => Mage::helper('supplier')->__('Phone'),
            5 => Mage::helper('supplier')->__('Vender website')
        );
    }

    public function getPurchaseOrderStatus() {
        return array(
            Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS => Mage::helper('supplier')->__('Pending'),
            Furniturestore_Supplier_Model_Purchaseorder::WAITING_CONFIRM_STATUS => Mage::helper('supplier')->__('Waiting confirmation'),
            Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('supplier')->__('Waiting delivery'),
            Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS => Mage::helper('supplier')->__('Processing delivery'),
            Furniturestore_Supplier_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('supplier')->__('Canceled'),
            Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS => Mage::helper('supplier')->__('Completed')
        );
    }

    public function getDataByPurchaseOrderId($purchaseOrderId, $column) {
        $purchaseOrderModel = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        $return = $purchaseOrderModel->getData($column);
        return $return;
    }

    public function getSupplierInfoByPurchaseOrderId($purchaseOrderId) {
        $purchaseOrderModel = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        $supplierId = $purchaseOrderModel->getSupplierId();
        $supplierModel = Mage::getModel('supplier/supplier')->load($supplierId);
        $supplierField = '';
        if ($supplierModel->getId()) {
            $data = $supplierModel->getData();

            $supplierField = "<br/>" . $data['street'];
            $supplierField .= "<br/>" . $data['city'] . ',';
            if (!$data['state']) {
                if ($data['state_id']) {
                    $state = Mage::getModel('directory/region')->load($data['state_id']);
                    $data['state'] = $state->getName();
                }
            }
            if ($data['state']) {
                $supplierField .= " " . $data['state'];
            }
            $supplierField .= ", " . $data['postcode'];

            $country = Mage::getModel('directory/country')->loadByCode($data['country_id']);

            $supplierField .= "<br/>" . $country->getName() . '.';

            $supplierField .= "<br/>" . $this->__('Telephone: ') . $data['telephone'];
            $supplierField .= "<br/>" . $this->__('Email: ') . $data['supplier_email'];
        }
        return $supplierField;
    }

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('purchaseorder_product_import', $data);
        }
    }

    public function importDeliveryProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('delivery_purchaseorder_product_import', $data);
        }
    }

    public function importReturnOrderProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('returnorder_product_import', $data);
        }
    }

    public function isPOCancelOutdate(Furniturestore_Supplier_Model_Purchaseorder $po) {
        if (empty($po)) {
            return false;
        }
        if (get_class($po) == "Furniturestore_Supplier_Model_Purchaseorder") {
            $cancelDate = $po->getCanceledDate();
            if (strtotime($cancelDate) > strtotime(now())) {
                return true;
            }
        } else {
            $purchase_order = Mage::getModel('supplier/purchaseorder')
                    ->load($po);
            $cancelDate = $po->getCanceledDate();
            if (strtotime($cancelDate) > strtotime(now())) {
                return true;
            }
        }
        return false;
    }

    public function getPaymentStatus() {
        return array(
            0 => Mage::helper('supplier')->__('Not Paid'),
            1 => Mage::helper('supplier')->__('Paid')
        );
    }

}
