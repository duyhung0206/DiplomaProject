<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 06/04/2017
 * Time: 23:22
 */
class Furniturestore_Supplier_Adminhtml_Sup_PurchaseorderController extends Mage_Adminhtml_Controller_Action{

    public function indexAction() {
        $this->loadLayout()
            ->renderLayout();
    }

    public function newAction() {
        $this->_title($this->__('Furniturestore'))
            ->_title($this->__('Add New Purchase Order'));

        $data = $this->getRequest()->getPost();

        $supplier_id = $this->getRequest()->getParam('supplier_id');
        if (isset($supplier_id) && $supplier_id) {
            $this->_forward('edit');
        } else {

            $this->loadLayout();
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Purchase Order'), Mage::helper('adminhtml')->__('Purchase Order')
            );
            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Purchase Order News'), Mage::helper('adminhtml')->__('Purchase Order News')
            );
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_new'))
                ->_addLeft($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_new_tabs'));
            $this->renderLayout();
        }
    }

    public function editAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);

        $supplier_id = $this->getRequest()->getParam('supplier_id');
        if (isset($supplier_id) && $supplier_id) {
            $this->_title($this->__('Furniturestore'))
                ->_title($this->__('Add New Purchase Order'));
        }
        if ($purchaseOrderId) {
            $this->_title($this->__('Furniturestore'))
                ->_title($this->__('Edit Purchase Order'));
        }
        if ($model->getId() || $purchaseOrderId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('purchaseorder_data', $model);

            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
                ->removeItem('js', 'mage/adminhtml/grid.js')
                ->addItem('js', 'furniturestore/supplier/grid.js');
            $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_edit'))
                ->_addLeft($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('inventorypurchasing')->__('Purchase Order does not exist!')
            );
            $this->_redirect('*/*/');
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.products')
            ->setProducts($this->getRequest()->getPost('purchaseorder_products', null));

        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('cost_product');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('tax');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('discount');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('supplier_sku');
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qty_order');
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('purchaseorder_product_import')) {
            Mage::getModel('admin/session')->setData('purchaseorder_product_import', null);
        }
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.products')
            ->setProducts($this->getRequest()->getPost('purchaseorder_products', null));
        $this->renderLayout();
    }

    public function importproductAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $purchaseorderProduct = array();
                $purchaseorderProducts = array();
                $fields = array();
                $count = 0;
                $purchaseorderHelper = Mage::helper('supplier/purchaseorder');
                if (count($dataFile))
                    foreach ($dataFile as $col => $row) {
                        if ($col == 0) {
                            if (count($row))
                                foreach ($row as $index => $cell)
                                    $fields[$index] = (string) $cell;
                        }elseif ($col > 0) {
                            if (count($row))
                                foreach ($row as $index => $cell) {
                                    if (isset($fields[$index])) {
                                        $purchaseorderProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $purchaseorderProducts[] = $purchaseorderProduct;
                        }
                    }
                $purchaseorderHelper->importProduct($purchaseorderProducts);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'furniturestore_supplier.log');
            }
        }
    }

    public function checkproductAction() {
        $purchaseorder_products = $this->getRequest()->getPost('products');
        $checkProduct = 1;
        $next = false;
        if ($purchaseorder_products == 'false') {
            echo 1;
            return;
        }
        if (isset($purchaseorder_products)) {
            $purchaseorderProducts = array();
            $purchaseorderProductsExplodes = explode('&', urldecode($purchaseorder_products));
            if (count($purchaseorderProductsExplodes) <= 900) {
                Mage::helper('supplier')->parseStr(urldecode($purchaseorder_products), $purchaseorderProducts);
            } else {
                foreach ($purchaseorderProductsExplodes as $purchaseorderProductsExplode) {
                    $purchaseorderProduct = '';
                    Mage::helper('supplier')->parseStr($purchaseorderProductsExplode, $purchaseorderProduct);
                    $purchaseorderProducts = $purchaseorderProducts + $purchaseorderProduct;
                }
            }
            if (count($purchaseorderProducts)) {
                foreach ($purchaseorderProducts as $pId => $enCoded) {
                    $codeArr = array();
                    Mage::helper('supplier')->parseStr(Mage::helper('supplier')->base64Decode($enCoded), $codeArr);
                    foreach ($codeArr as $codeId => $code) {
                        if (in_array($codeId, array('cost_product', 'tax', 'discount'))) {
                            if ($codeId[1]) {
                                if (!is_numeric($code) || $code < 0) {
                                    $checkProduct = 0;
                                    $next = true;
                                    break;
                                }
                            }
                        }
                        if (in_array($codeId, array('qty_order'))) {
                            if ($codeId[1]) {
                                if (!is_numeric($code) || $code <= 0) {
                                    $checkProduct = 0;
                                    $next = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ($next)
                        break;
                }
            }
        }
        echo $checkProduct;
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            Zend_Debug::dump($data);
            if (!array_key_exists('send_mail', $data)) {
                $data['send_mail'] = 1;
            }
            $admin = Mage::getModel('admin/session')->getUser()->getUsername();

            $data = $this->_filterDateTime($data, array('purchase_on'));
            $data = $this->_filterDates($data, array('started_date', 'canceled_date', 'expected_date', 'payment_date'));

            if ($this->getRequest()->getParam('supplier_id'))
                $data['supplier_id'] = $this->getRequest()->getParam('supplier_id');
            if ($this->getRequest()->getParam('currency')) {
                $data['currency'] = $this->getRequest()->getParam('currency');
            }
            if ($this->getRequest()->getParam('change_rate')) {
                $data['change_rate'] = $this->getRequest()->getParam('change_rate');
            }

            $model = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('id'));
            if ($this->getRequest()->getParam('id')) {
                $data['created_by'] = $model->getData('created_by');
                $data['status'] = $model->getData('status');
                $data['shipping_tax'] = $model->getData('shipping_tax');
                $data['discount_tax'] = $model->getData('discount_tax');
            } else {
                if (Mage::getStoreConfig('supplier/purchasing/require_confirmation_from_supplier') || 1)
                    $data['status'] = Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS;
                $data['shipping_tax'] = Mage::getStoreConfig('supplier/purchasing/shipping_includes_tax');
                $data['discount_tax'] = Mage::getStoreConfig('supplier/purchasing/apply_after_discount');
            }

            $model->addData($data);
            $model->save();

            $purchaseOrderModel = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('id'));

            if (isset($data['paid_more']) && $data['paid_more']) {
                if ($this->getRequest()->getParam('id')) {
                    $data['paid'] = $purchaseOrderModel->getPaid() + $data['paid_more'];
                } else {
                    $data['paid'] = $data['paid_more'];
                }
                $model->setPaid($data['paid']);
                if ($data['paid'] > 0)
                    $model->setPaidAll(2);
            } else {
                if (!$this->getRequest()->getParam('id')) {
                    $data['paid'] = 0;
                }
            }

            $model->save();
            $supplier_id = $data['supplier_id'];
            $supplierProducts = Mage::getModel('supplier/product')
                ->getCollection()
                ->addFieldToFilter('supplier_id', $supplier_id);
            $supplierProductIds = array();
            foreach ($supplierProducts as $supplierProduct) {
                $supplierProductIds[] = $supplierProduct->getProductId();
            }
            try {
                if (!Mage::helper('supplier/purchaseorder')->haveDelivery($this->getRequest()->getParam('id'))) {
                    $supplierModel = Mage::getModel('supplier/supplier')->load($supplier_id);

                    if ($supplierModel->getId())
                        $model->setSupplierName($supplierModel->getSupplierName());
                }

                if (!$this->getRequest()->getParam('id')) {
                    $model->setData('created_by', $admin);
                }
                $model->save();
                $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($model->getId());
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $installer = Mage::getModel('core/resource');
                $sqlOlds = '';
                $countSqlOlds = 0;
                if (isset($data['purchaseorder_products'])) {
                    $purchaseorderProducts = array();
                    $purchaseorderProductsExplodes = explode('&', urldecode($data['purchaseorder_products']));
                    if (count($purchaseorderProductsExplodes) <= 900) {
                        Mage::helper('supplier')->parseStr(urldecode($data['purchaseorder_products']), $purchaseorderProducts);
                    } else {
                        foreach ($purchaseorderProductsExplodes as $purchaseorderProductsExplode) {
                            $purchaseorderProduct = '';
                            Mage::helper('supplier')->parseStr($purchaseorderProductsExplode, $purchaseorderProduct);
                            $purchaseorderProducts = $purchaseorderProducts + $purchaseorderProduct;
                        }
                    }

                    if (count($purchaseorderProducts)) {
                        $productIds = [];
                        $totalProducts = 0;
                        $totalAmounts = 0;
                        $sqlCount = 0;
                        $sqlNews = array();
                        foreach ($purchaseorderProducts as $pId => $enCoded) {
                            if (in_array($pId, $supplierProductIds)) {
                                $codeArr = array();
                                Mage::helper('supplier')->parseStr(Mage::helper('supplier')->base64Decode($enCoded), $codeArr);
                                $purchaseorderProductItem = Mage::getModel('supplier/purchaseorder_product')
                                    ->getCollection()
                                    ->addFieldToFilter('purchase_order_id', $model->getId())
                                    ->addFieldToFilter('product_id', $pId)
                                    ->setPageSize(1)->setCurPage(1)->getFirstItem();
                                $productModel = Mage::getModel('catalog/product')->load($pId);
                                $productIds[] = $pId;
                                if ($purchaseorderProductItem->getId()) {
                                    $codeArr['qty_order'] = ($codeArr['qty_order'] == null) ? 0 : $codeArr['qty_order'];
                                    $cost = isset($codeArr['cost_product']) ? $codeArr['cost_product'] : $purchaseorderProductItem->getCost();
                                    $tax = isset($codeArr['tax']) ? $codeArr['tax'] : $purchaseorderProductItem->getTax();
                                    $discount = isset($codeArr['discount']) ? $codeArr['discount'] : $purchaseorderProductItem->getDiscount();
                                    $supplierSku = isset($codeArr['supplier_sku']) ? $codeArr['supplier_sku'] : $purchaseorderProductItem->getSupplierSku();
                                    $countSqlOlds++;
                                    /* Michael 201602 */
                                    if ($data['discount_tax'] == 0) {
                                        $totalAmounts += $codeArr['qty_order'] * $cost * (1 + $tax / 100 - $discount / 100);
                                    } else {
                                        $totalAmounts += $codeArr['qty_order'] * $cost * (1 - $discount / 100) * (1 + $tax / 100);
                                    }
                                    Zend_Debug::dump($codeArr['qty_order'] );
                                    die('1');
                                    $totalProducts += $codeArr['qty_order'];
                                    $sqlOlds .= 'UPDATE ' . $installer->getTableName('supplier/purchaseorder_product') . ' 
                                                                            SET `qty` = \'' . $codeArr['qty_order'] . '\', `tax` = \'' . $tax . '\',`supplier_sku` = \'' . $supplierSku . '\', `cost` = \'' . $cost . '\', `discount` = \'' . $discount . '\'
                                                                                    WHERE `purchase_order_product_id` =' . $purchaseorderProductItem->getId() . ';';
                                    if ($countSqlOlds == 900) {
                                        $writeConnection->query($sqlOlds);
                                        $sqlOlds = '';
                                        $countSqlOlds = 0;
                                    }
                                } else {
                                    $sqlCount++;
                                    $product_id = $pId;
                                    $product_name = $productModel->getName();
                                    $product_sku = $productModel->getSku();
                                    $purchase_order_id = $model->getId();
                                    $codeArr['qty_order'] = ($codeArr['qty_order'] == null) ? 0 : $codeArr['qty_order'];
                                    $qty = $codeArr['qty_order'];
                                    $cost = $codeArr['cost_product'];
                                    $discount = $codeArr['discount'];
                                    $tax = $codeArr['tax'];
                                    $supplier_sku = $codeArr['supplier_sku'];
                                    /* Michael 201602 */
                                    if ($data['discount_tax'] == 0) {
                                        $totalAmounts += $codeArr['qty_order'] * $cost * (1 + $tax / 100 - $discount / 100);
                                    } else {
                                        $totalAmounts += $codeArr['qty_order'] * $cost * (1 - $discount / 100) * (1 + $tax / 100);
                                    }
//
                                    $sqlNews[] = array(
                                        'product_id' => $product_id,
                                        'product_name' => $product_name,
                                        'product_sku' => $product_sku,
                                        'purchase_order_id' => $purchase_order_id,
                                        'qty' => $qty,
                                        'cost' => $cost,
                                        'discount' => $discount,
                                        'tax' => $tax,
                                        'supplier_sku' => $supplier_sku
                                    );

                                    if (count($sqlNews) == 1000) {
                                        $writeConnection->insertMultiple($installer->getTableName('supplier/purchaseorder_product'), $sqlNews);
                                        $sqlNews = array();
                                    }
                                    $totalProducts += $codeArr['qty_order'];
                                }
                            }
                        }
                        if (!empty($sqlNews)) {
                            $writeConnection->insertMultiple($installer->getTableName('supplier/purchaseorder_product'), $sqlNews);
                        }

                        if (!empty($sqlOlds)) {
                            $writeConnection->query($sqlOlds);
                        }
                        $writeConnection->commit();
                        $productDeletes = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                            ->addFieldToFilter('purchase_order_id', $model->getId())
                            ->addFieldToFilter('product_id', array('nin' => $productIds));
                        if ($productDeletes->getSize() > 0) {
                            foreach ($productDeletes as $productDelete)
                                $productDelete->delete();
                        }
                    }
                    $model->setTotalProducts($totalProducts)
                        ->setTotalAmount($totalAmounts)
                        ->save();
                }

                /*need update*/
                if (array_key_exists('send_mail', $data)) {
//                    $this->sendEmail($data['supplier_id'], $sqlNews, $purchaseOrderId);
                }
                if (!$this->getRequest()->getParam('id')) {
                    if ($totalProducts <= 0) {
                        $model->delete();
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('supplier')->__('Please fill qty for product(s) to purchase order!')
                        );
                        $this->_redirect('*/*/new');
                        return;
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('supplier')->__('The purchase order has been saved successfully.')
                );
                if ($data['status'] == 6 && !$this->getRequest()->getParam('id')) {
                    $this->_redirect('*/*/allDelivery', array('purchaseorder_id' => $model->getId()));
                    return;
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('supplier')->__('Unable to find Purchase order to save!')
        );
        $this->_redirect('*/*/');
    }
}