<?php

class Furniturestore_Supplier_Helper_Purchaseorder extends Mage_Core_Helper_Abstract {
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
//
//    public function canCancel($id = null) {
//        if (!$id) {
//            $id = Mage::app()->getRequest()->getParam('id');
//        }
//        if ($purchaseOrderId = $id) {
//            $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
//            $cancelDate = $purchaseOrder->getCanceledDate();
//            $canCancel = 0;
//            if (strtotime($cancelDate) <= strtotime(now())) {
//                return false;
//            }
//            if ($this->haveDelivery($purchaseOrderId)) {
//                return false;
//            }
//            $poStatus = $purchaseOrder->getStatus();
//            if ($poStatus == 6) {
//                return false;
//            }
//        }
//        return true;
//    }
//
//    public function canWaittingDelivery($id = null) {
//        if (!$id) {
//            $id = Mage::app()->getRequest()->getParam('id');
//        }
//        if ($purchaseOrderId = $id) {
//            $purchaseOrder = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
//            $poStatus = $purchaseOrder->getStatus();
//            if ($poStatus == 6) {
//                return false;
//            }
//        }
//        return true;
//    }
//
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
//
//    public function getMassPOStatus() {
//        return array(
//            Magestore_Inventorypurchasing_Model_Purchaseorder::AWAITING_DELIVERY_STATUS => Mage::helper('inventorypurchasing')->__('Waiting delivery'),
//            Magestore_Inventorypurchasing_Model_Purchaseorder::CANCELED_STATUS => Mage::helper('inventorypurchasing')->__('Canceled')
//        );
//    }
//
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
//
//    public function getPaymentTerms() {
//        $paymentTerms = Mage::getModel('inventorypurchasing/paymentterm')
//                ->getCollection()
//                ->addFieldToFilter('payment_term_status', 1);
//        $paymentArray = array();
//        $paymentArray[0] = $this->__('Select payment term');
//        if (count($paymentTerms)) {
//
//            foreach ($paymentTerms as $payment) {
//                $paymentArray[$payment->getId()] = $payment->getPaymentTermName();
//            }
//        }
//        $paymentArray['new'] = $this->__('Create a new payment term');
//        return $paymentArray;
//    }
//
//    public function getShippingMethodForSupplier() {
//        $shippingMethods = Mage::getModel('inventorypurchasing/shippingmethod')
//                ->getCollection()
//                ->addFieldToFilter('shipping_method_status', 1);
//        if (count($shippingMethods)) {
//            $shippingArray = array();
//            $shippingArray[0] = $this->__('Select shipping method');
//            foreach ($shippingMethods as $shipping) {
//                $shippingArray[$shipping->getId()] = $shipping->getShippingMethodName();
//            }
//            return $shippingArray;
//        } else {
//            return '';
//        }
//    }
//
//    public function getPaymentTermsForSupplier() {
//        $paymentTerms = Mage::getModel('inventorypurchasing/paymentterm')
//                ->getCollection()
//                ->addFieldToFilter('payment_term_status', 1);
//        if (count($paymentTerms)) {
//            $paymentArray = array();
//            $paymentArray[0] = $this->__('Select payment term');
//            foreach ($paymentTerms as $payment) {
//                $paymentArray[$payment->getId()] = $payment->getPaymentTermName();
//            }
//            return $paymentArray;
//        } else {
//            return '';
//        }
//    }
//
    public function getOrderPlaced() {
        return array(
            1 => Mage::helper('supplier')->__('Email'),
            2 => Mage::helper('supplier')->__('Fax'),
            3 => Mage::helper('supplier')->__('N/A'),
            4 => Mage::helper('supplier')->__('Phone'),
            5 => Mage::helper('supplier')->__('Vender website')
        );
    }
//
//    public function getPurchaseOrderStatus() {
//        return array(
//            //1 => 'New',
//            //2 => 'Inquiry',
//            //3 => 'Awaiting payment',
//            //4 => 'Awaiting supplier',
//            5 => 'Awaiting delivery',
//            6 => 'Complete',
//            7 => 'Canceled'
//        );
//    }
//
    public function getDataByPurchaseOrderId($purchaseOrderId, $column) {
        $purchaseOrderModel = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        $return = $purchaseOrderModel->getData($column);
        return $return;
    }
//
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
//
//    public function getWarehouseOption() {
//        $adminId = Mage::getModel('admin/session')->getUser()->getId();
//        if (!$adminId)
//            return null;
//        $warehouseAssigneds = Mage::getModel('inventoryplus/warehouse_permission')->getCollection()
//                ->addFieldToFilter('admin_id', $adminId)
//                ->addFieldToFilter('can_purchase_product', 1);
//        $warehouseIds = array();
//        foreach ($warehouseAssigneds as $warehouseAssigned) {
//            $warehouseIds[] = $warehouseAssigned->getWarehouseId();
//        }
//        if (!count($warehouseIds) || count($warehouseIds) <= 0)
//            return null;
//        $warehouseCollections = Mage::getModel('inventoryplus/warehouse')->getCollection()
//                ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds))
//                ->addFieldToFilter('status', 1)
//        ;
//        $warehouseArr = array();
//        foreach ($warehouseCollections as $warehouse) {
//            $warehouseArr[] = array('value' => $warehouse->getId(), 'label' => $warehouse->getWarehouseName());
//        }
//        return $warehouseArr;
//    }
//
    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('purchaseorder_product_import', $data);
        }
    }
//
//    public function getBilingAddressByPurchaseOrderId($purchaseOrderId) {
//        $purchaseOrderModel = Mage::getModel('inventorypurchasing/purchaseorder')->load($purchaseOrderId);
//        $supplierId = $purchaseOrderModel->getSupplierId();
//        $supplierModel = Mage::getModel('inventorypurchasing/supplier')->load($supplierId);
//        $supplierField = '';
//        if ($supplierModel->getId()) {
//            $data = $supplierModel->getData();
//            $countryLists = Mage::getModel('directory/country')->getResourceCollection()->loadByStore()->toOptionArray(true);
//            $countryList = array();
//            foreach ($countryLists as $county) {
//                $countryList[$county['value']] = $county['label'];
//            }
//            $supplierField = "<br/>" . $data['street'];
//            $supplierField .= "<br/>" . $data['city'];
//            if (isset($data['state'])) {
//                $supplierField .= ", " . $data['state'];
//            }
//            $supplierField .= ", " . $data['postcode'];
//            $supplierField .= "<br/>" . $countryList[$data['country_id']];
//            $supplierField .= "<br />" . $this->__('T: ') . $data['telephone'];
//        }
//        return $supplierField;
//    }
//
//    public function importDeliveryProduct($data) {
//        if (count($data)) {
//            Mage::getModel('admin/session')->setData('delivery_purchaseorder_product_import', $data);
//        }
//    }
//
//    public function importReturnOrderProduct($data) {
//        if (count($data)) {
//            Mage::getModel('admin/session')->setData('returnorder_product_import', $data);
//        }
//    }
//
//    /**
//     * Get receiving purchase order Ids
//     *
//     * @return array
//     */
//    public function getAwaitingPOids($supplierIds = array()) {
//        $poIds = array();
//        $pos = Mage::getResourceModel('inventorypurchasing/purchaseorder_collection')
//                ->addFieldToFilter('status', array('in' => array(
//                    Magestore_Inventorypurchasing_Model_Purchaseorder::PENDING_STATUS,
//                    Magestore_Inventorypurchasing_Model_Purchaseorder::WAITING_APPROVE_STATUS,
//                    Magestore_Inventorypurchasing_Model_Purchaseorder::WAITING_CONFIRM_STATUS,
//                    Magestore_Inventorypurchasing_Model_Purchaseorder::AWAITING_DELIVERY_STATUS,
//                    Magestore_Inventorypurchasing_Model_Purchaseorder::RECEIVING_STATUS
//                )))
//                ->addFieldToFilter('trash', Magestore_Inventorypurchasing_Model_Purchaseorder::IS_NOT_TRASH);
//        if(count($supplierIds)) {
//            $pos->addFieldToFilter('supplier_id', array('in' => $supplierIds));
//        }
//        if(count($pos)) {
//            foreach($pos as $po) {
//                $poIds[] = $po->getId();
//            }
//        }
//        return $poIds;
//    }
//
//    /**
//     * Check if PO is out date or not (split canCancel function)
//     * @param int , PO model
//     * @return boolean
//     */
//    public function isPOCancelOutdate(Magestore_Inventorypurchasing_Model_Purchaseorder $po) {
//        if (empty($po)) {
//            return false;
//        }
//        if (get_class($po) == "Magestore_Inventorypurchasing_Model_Purchaseorder") {
//            $cancelDate = $po->getCanceledDate();
//            if (strtotime($cancelDate) > strtotime(now())) {
//                return true;
//            }
//        } else {
//            $purchase_order = Mage::getModel('inventorypurchasing/purchaseorder')
//                    ->load($po);
//            $cancelDate = $po->getCanceledDate();
//            if (strtotime($cancelDate) > strtotime(now())) {
//                return true;
//            }
//        }
//        return false;
//    }
//
//    /**
//     * get payment status
//     */
//    public function getPaymentStatus() {
//        return array(
//            0 => Mage::helper('inventorypurchasing')->__('Not Paid'),
//            2 => Mage::helper('inventorypurchasing')->__('Partial Paid'),
//            1 => Mage::helper('inventorypurchasing')->__('Paid')
//        );
//    }

}
