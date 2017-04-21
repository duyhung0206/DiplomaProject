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
                Mage::helper('supplier')->__('Purchase Order does not exist!')
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
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qty');
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
                        if (in_array($codeId, array('qty'))) {
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

            if (!array_key_exists('send_mail', $data)) {
                $data['send_mail'] = 0;
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
                                    $codeArr['qty'] = ($codeArr['qty'] == null) ? 0 : $codeArr['qty'];
                                    $cost = isset($codeArr['cost_product']) ? $codeArr['cost_product'] : $purchaseorderProductItem->getCost();
                                    $tax = isset($codeArr['tax']) ? $codeArr['tax'] : $purchaseorderProductItem->getTax();
                                    $discount = isset($codeArr['discount']) ? $codeArr['discount'] : $purchaseorderProductItem->getDiscount();
                                    $supplierSku = isset($codeArr['supplier_sku']) ? $codeArr['supplier_sku'] : $purchaseorderProductItem->getSupplierSku();
                                    $countSqlOlds++;
                                    /* Michael 201602 */
                                    if ($data['discount_tax'] == 0) {
                                        $totalAmounts += $codeArr['qty'] * $cost * (1 + $tax / 100 - $discount / 100);
                                    } else {
                                        $totalAmounts += $codeArr['qty'] * $cost * (1 - $discount / 100) * (1 + $tax / 100);
                                    }
                                    $totalProducts += $codeArr['qty'];
                                    $sqlOlds .= 'UPDATE ' . $installer->getTableName('supplier/purchaseorder_product') . ' 
                                                                            SET `qty` = \'' . $codeArr['qty'] . '\', `tax` = \'' . $tax . '\',`supplier_sku` = \'' . $supplierSku . '\', `cost` = \'' . $cost . '\', `discount` = \'' . $discount . '\'
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
                                    $codeArr['qty'] = ($codeArr['qty'] == null) ? 0 : $codeArr['qty'];
                                    $qty = $codeArr['qty'];
                                    $cost = $codeArr['cost_product'];
                                    $discount = $codeArr['discount'];
                                    $tax = $codeArr['tax'];
                                    $supplier_sku = $codeArr['supplier_sku'];
                                    /* Michael 201602 */
                                    if ($data['discount_tax'] == 0) {
                                        $totalAmounts += $codeArr['qty'] * $cost * (1 + $tax / 100 - $discount / 100);
                                    } else {
                                        $totalAmounts += $codeArr['qty'] * $cost * (1 - $discount / 100) * (1 + $tax / 100);
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
                                    $totalProducts += $codeArr['qty'];
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
                    $this->sendEmail($data['supplier_id'], $sqlNews, $model->getId());
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

    public function requestconfirmAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        try {
            $purchaseOrder->setStatus(Furniturestore_Supplier_Model_Purchaseorder::WAITING_CONFIRM_STATUS)
                ->save();
            if (Mage::getStoreConfig('supplier/purchasing/send_email_to_supplier_after_request_confirmation')) {
                $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
                $sqlNews = $purchaseOrderProducts->getData();
                $this->sendEmail($purchaseOrder->getSupplierId(), $sqlNews, $purchaseOrderId);
                $purchaseOrder->setSendMail(1)->save();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('supplier')->__('Purchase order has been requested for confirmation.')
            );
            $this->_redirect('*/*/edit', array('id' => $purchaseOrderId));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('There is error while confirming purchase order.')
            );
            Mage::getSingleton('adminhtml/session')->addError(
                $e->getMessage()
            );
            $this->_redirect('*/*/edit', array('id' => $purchaseOrderId));
        }
    }

    public function sendEmail($supplierId, $sqlNews, $purchaseOrderId) {
        $store = Mage::app()->getStore();

        $templateId = Mage::getStoreConfig('supplier/email_supplier/template', $store->getId());

        if ($supplierId) {
            $supplierInfo = Mage::helper('supplier/supplier')->getBillingAddressBySupplierId($supplierId);
        }
        if (!$supplierId) {
            $supplierInfo = Mage::helper('supplier/purchaseorder')->getBilingAddressByPurchaseOrderId($purchaseOrderId);
        }
        $supplierCollection = Mage::getResourceModel('supplier/supplier_collection')
            ->addFieldToFilter('supplier_id', $supplierId);
        $supplierdata = $supplierCollection->setPageSize(1)->setCurPage(1)->getFirstItem()->getData();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $transaction = Mage::getSingleton('core/email_template');


        $top_email = Mage::getStoreConfig('supplier/email_supplier/top_email', $store->getId());
        $storeName = Mage::getStoreConfig('general/store_information/name');

        $senderEmail = Mage::getStoreConfig('trans_email/ident_general/email', $store->getId());

        $senderName = Mage::getStoreConfig('trans_email/ident_general/name', $store->getId());

        $emailSubject = Mage::helper('supplier')->__('Purchase Order #%s', $purchaseOrderId);

        $top_email = Mage::helper('supplier')->__(
            '<p style="font-size:12px; line-height:16px; margin:0;"> We are from %s <br/> We want to purchase order some product from you. And below are our information and list product that we want to purchase.</p>', $storeName);
        $sender = array(
            'name' => $senderName,
            'email' => $senderEmail,
        );
        $items = '';
        $count = 0;
        foreach ($sqlNews as $item) {
            if ($count % 2 == 0)
                $items = $items . '<tbody bgcolor="#F6F6F6">';
            else
                $items = $items . '<tbody>';
            $items = $items . '<tr>
                                                                    <td align="left" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">
                                                                            <strong style="font-size:11px;">' . $item["product_name"] . '</strong>
                                                                    </td>
                                                                    <td align="left" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">' . $item["product_sku"] . '</td>
                                                                    <td align="left" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">' . Mage::helper('core')->currency($item["cost"]) . '</td>
                                                                    <td align="left" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">' . $item["tax"] . '</td>
                                                                    <td align="center" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">' . $item["discount"] . '</td>
                                                                         <td align="center" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">' . $item["supplier_sku"] . '</td>
                                                                    <td align="right" valign="top" style="font-size:11px; padding:3px 9px; border-bottom:1px dotted #CCCCCC;">
                                                                                                                                                            <span class="price">' . $item["qty"] . '</span>            
                                                                    </td>
                                                            </tr>
                                                    </tbody>';
            $count++;
        }

        $purchaseOrder = 'Our Purchase Order #' . $purchaseOrderId;
        $transaction->sendTransactional(
            $templateId, //Template config
            $sender, $supplierdata['supplier_email'], $supplierdata['supplier_name'], array(
                'store' => $store,
                'top_email' => $top_email,
                'order_id' => $purchaseOrder,
                'billing' => $supplierInfo,
                'email_subject' => $emailSubject,
                'items' => $items,
                'purchaseorderid' => $purchaseOrderId,
                'sqlnews' => $sqlNews
            )
        );

        $admin = Mage::getModel('admin/session')->getUser()->getUsername();
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('supplier')->__('The purchase order email has been sent to the supplier.')
        );
    }

    public function resendemailtosupplierAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
            ->addFieldToFilter('purchase_order_id', $purchaseOrderId);

        $sqlNews = $purchaseOrderProducts->getData();
        $this->sendEmail($purchaseOrder->getSupplierId(), $sqlNews, $purchaseOrderId);
        $purchaseOrder->setSendMail(1)->save();
        $this->_redirect('*/*/edit', array('id' => $purchaseOrderId));
    }

    /*cancel purchaseorder*/
    public function cancelOrderAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $deliveryModel = Mage::getModel('supplier/delivery')->getCollection()->addFieldToFilter('purchase_order_id', $purchaseOrderId);
        if (!$deliveryModel->setPageSize(1)->setCurPage(1)->getFirstItem()->getData()) {
            $purchaseOrderModel = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
            $purchaseOrderModel->setStatus(7);
            $purchaseOrderModel->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('supplier')->__('Purchase Order was successfully canceled.')
            );
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to cancel order because it has been on delivery!')
            );
        }
        $this->_redirect('*/*/');
    }

    public function exportcsvpurchaseorderAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
            ->addFieldToFilter('purchase_order_id', $purchaseOrderId);

        $sqlNews = $purchaseOrderProducts->getData();
//        $img = Mage::getDesign()->getSkinUrl('images/logo_email.png', array('_area' => 'frontend'));
        $img = 'https://jvdeh29369.i.lithium.com/html/survey_icons/magento_Full_color_horizontal.jpg';
        $contents = '<div><img style="height:60px" src="' . $img . '" /></div>';
        $contents .= $this->getLayout()->createBlock('supplier/adminhtml_purchaseorder')
            ->setPurchaseorderid($purchaseOrderId)
            ->setSqlnews($sqlNews)
            ->setTemplate('supplier/purchaseorder/sendtosupplier.phtml')
            ->toHtml();
        include("lib/Mpdf/mpdf.php");
        $mpdf = new mPDF('', 'A4');
        $mpdf->WriteHTML($contents);
        $fileName = 'purchase-order-' . $purchaseOrderId . '-' . Mage::helper('core')->formatDate(now(), 'short');
        $mpdf->Output($fileName . '.pdf', 'I');
        exit;
    }

    public function deliveryAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.delivery')
            ->setDeliveries($this->getRequest()->getPost('isdeliveries', null));
        $this->renderLayout();
    }

    public function deliveryGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.delivery')
            ->setDeliveries($this->getRequest()->getPost('isdeliveries', null));
        $this->renderLayout();
    }

    /**
     * Confirm purchase order
     *
     */
    public function confirmAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('supplier/purchaseorder')->load($id);
        try {
            $model->setStatus(Furniturestore_Supplier_Model_Purchaseorder::AWAITING_DELIVERY_STATUS)
                ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('supplier')->__('Purchase order has been confirmed.')
            );
            $this->_redirect('*/*/edit', array('id' => $id));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('There is error while confirming purchase order.')
            );
            Mage::getSingleton('adminhtml/session')->addError(
                $e->getMessage()
            );
            $this->_redirect('*/*/edit', array('id' => $id));
        }
    }

    public function newDeliveryAction() {
        $purchaseOrderId = $this->getRequest()->getParam('purchaseorder_id');
        $model = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        $this->_title($this->__('Furniturestore'))
            ->_title($this->__('Add New Delivery'));
        if ($model->getId() || $purchaseOrderId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('purchaseorder_data', $model);

            $this->loadLayout()->_setActiveMenu($this->_menu_path);

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_editdelivery'))
                ->_addLeft($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_editdelivery_tabs'));
            $this->renderLayout();

            if (Mage::getModel('admin/session')->getData('delivery_purchaseorder_product_import')) {
                Mage::getModel('admin/session')->setData('delivery_purchaseorder_product_import', null);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function prepareDeliveryAction() {
        $this->_title($this->__('Furniturestore'))
            ->_title($this->__('Add New Delivery'));
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.preparedelivery')
            ->setProducts($this->getRequest()->getPost('isproducts', null));

        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('purchaseorder_id'));
//        $warehouseIds = $purchaseOrder->getWarehouseId();
//        $warehouseIds = explode(',', $warehouseIds);
//        foreach ($warehouseIds as $warehouseId) {
//            $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('warehouse_' . $warehouseId);
//        }


        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('delivery_purchaseorder_product_import')) {
            Mage::getModel('admin/session')->setData('delivery_purchaseorder_product_import', null);
        }
    }

    public function prepareDeliveryGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.preparedelivery')
            ->setProducts($this->getRequest()->getPost('isproducts', null))
        ;
        $this->renderLayout();
    }

    public function saveDeliveryAction() {
        $purchaseOrderId = $this->getRequest()->getPost('purchaseorder_id');

        if (!$purchaseOrderId) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to find delivery to save!')
            );
            $this->_redirect('*/*/');
        }
        try {
            if ($data = $this->getRequest()->getPost()) {
                $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
                if (isset($data['delivery_products'])) {
                    $deliveryProducts = array();
                    Mage::helper('supplier')->parseStr(urldecode($data['delivery_products']), $deliveryProducts);

                    if (count($deliveryProducts)) {
                        $totalProductDelivery = 0;
                        Mage::getModel('admin/session')->unsetData('delivery_create_at');
                        foreach ($deliveryProducts as $pId => $enCoded) {
                            $codeArr = array();
                            Mage::helper('supplier')->parseStr(Mage::helper('supplier')->base64Decode($enCoded), $codeArr);

                            $purchaseorderProductItem = Mage::getModel('supplier/purchaseorder_product')
                                ->getCollection()
                                ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                                ->addFieldToFilter('product_id', $pId)
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
                                if ($purchaseorderProductItem->getId()) {
                                    $sametime = strtotime(now());
                                    $totalmaxQtyReceive = $purchaseorderProductItem->getQty() - $purchaseorderProductItem->getQtyRecieved();
                                    if ($codeArr['qty_delivery'] > $totalmaxQtyReceive)
                                        $codeArr['qty_delivery'] = $totalmaxQtyReceive;
                                    if (!$codeArr['qty_delivery'] || $codeArr['qty_delivery'] <= 0)
                                        continue;
                                    $purchaseorderProductItem->setQtyRecieved($purchaseorderProductItem->getQtyRecieved() + $codeArr['qty_delivery'])
                                        ->save();
                                    $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                                    $delivery = Mage::getModel('supplier/delivery');
                                    /*save delivery*/
                                    $delivery->setDeliveryDate($data['delivery_date'])
                                        ->setQtyDelivery($codeArr['qty_delivery'])
                                        ->setPurchaseOrderId($purchaseOrderId)
                                        ->setProductId($pId)
                                        ->setProductName($purchaseorderProductItem->getProductName())
                                        ->setProductSku($purchaseorderProductItem->getProductSku())
                                        ->setSametime($sametime)
                                        ->setCreatedBy($admin)
                                        ->save();
                                    $totalProductDelivery += $codeArr['qty_delivery'];
                                }

                        }
                    }

                        /*Qty delivery = 0 error*/
                        if ($totalProductDelivery == 0) {
                            Mage::getSingleton('adminhtml/session')->addError(
                                Mage::helper('supplier')->__('Please select product and enter Qty greater than 0 to create delivery!')
                            );

                            $this->_redirect('*/*/newdelivery', array(
                                'purchaseorder_id' => $purchaseOrderId,
                                'action' => 'newdelivery',
                                'active' => 'delivery'
                            ));
                            return;
                        }
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('supplier')->__('Please select product(s) to create delivery!')
                        );

                        $this->_redirect('*/*/newdelivery', array(
                            'purchaseorder_id' => $purchaseOrderId,
                            'action' => 'newdelivery',
                            'active' => 'delivery'
                        ));
                        return;
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('supplier')->__('Please select product(s) to create delivery!')
                    );
                    $this->_redirect('*/*/newdelivery', array(
                        'purchaseorder_id' => $purchaseOrderId,
                        'action' => 'newdelivery',
                        'active' => 'delivery'
                    ));
                    return;
                }

                $totalProductOrder = 0;
                $totalProductReceived = 0;
                $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId);

                $purchaseOrder->setStatus(Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS);


                foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
                    if ($purchaseOrderProduct->getQtyRecieved() < $purchaseOrderProduct->getQty()) {
                        //$purchaseOrder->setStatus(5);
                        $purchaseOrder->setStatus(Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS);
                    }
                    $totalProductOrder += $purchaseOrderProduct->getQty();
                    $totalProductReceived += $purchaseOrderProduct->getQtyRecieved();
                }
                $process = round(($totalProductReceived / $totalProductOrder) * 100, 2);
                $purchaseOrder->setDeliveryProcess($process)->save();
                $totalProduct_Recieved = $purchaseOrder->getData('total_products_recieved') + $totalProductDelivery;
                $purchaseOrder->setTotalProductsRecieved($totalProduct_Recieved);
                $purchaseOrder->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('supplier')->__('The delivery has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('adminhtml/sup_purchaseorder/edit', array('id' => $this->getRequest()->getParam('purchaseorder_id'), 'active' => 'delivery'));
                return;
//            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/');
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('supplier')->__('Unable to find delivery to save!')
        );
        $this->_redirect('*/*/');
    }

    public function importproductforcreatedeliveryAction() {
        $getParams = $this->getRequest()->getParams();
        Mage::getModel('admin/session')->setData('delivery_create_at', null);
        if (isset($getParams['create_at'])) {
            Mage::getModel('admin/session')->setData('delivery_create_at', $getParams['create_at']);
        }
        $checkField = array(
            'SKU', 'QTY'
        );
        $purchaseOrderId = $getParams['purchaseorder_id'];
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $newDeliveryProduct = array();
                $newDeliveryProducts = array();
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
                                        $newDeliveryProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $newDeliveryProducts[] = $newDeliveryProduct;
                        }
                    }//end foreach

                $purchaseorderHelper->importDeliveryProduct($newDeliveryProducts);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'furniturestore_supplier.log');
            }
        }
    }

    public function allDeliveryAction() {
        $purchaseOrderId = $this->getRequest()->getParam('purchaseorder_id');
        if (!$purchaseOrderId) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to find delivery to save!')
            );
            $this->_redirect('*/*/');
        }
        try {
            $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
            $purchaseOrderProducts = Mage::getResourceModel('supplier/purchaseorder_product_collection')
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
            $totalProductDelivery = 0;
            if ($purchaseOrderProducts->getSize()) {
                $deliveryIds = array();

                foreach ($purchaseOrderProducts as $product) {
                    $sametime = strtotime(now());
                    $qtyDeliveries = 0;
                    $maxQtyReceive = $product->getQty() - $product->getQtyRecieved();

                    if ($qtyDeliveries < $maxQtyReceive)
                        $qtyDeliveries = $maxQtyReceive;
                    if (!$qtyDeliveries || $qtyDeliveries <= 0)
                        continue;
                    $product->setQtyRecieved($product->getQtyRecieved() + $qtyDeliveries)
                        ->save();
                    $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                    $delivery = Mage::getModel('supplier/delivery');
                    $delivery->setDeliveryDate(now())
                        ->setQtyDelivery($qtyDeliveries)
                        ->setPurchaseOrderId($purchaseOrderId)
                        ->setProductId($product->getProductId())
                        ->setProductName($product->getProductName())
                        ->setProductSku($product->getProductSku())
                        ->setSametime($sametime)
                        ->setCreatedBy($admin)
                        ->save();
                    $deliveryIds[] = $delivery->getId();
                    $totalProductDelivery += $qtyDeliveries;
                }


                if ($totalProductDelivery == 0) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('supplier')->__('Please select product and enter qty delivery for product to create delivery')
                    );

                    $this->_redirect('adminhtml/sup_purchaseorder/edit',
                        array(
                            'id' => $purchaseOrderId,
                            'active' => 'delivery'));
                    return;
                }

                $totalProductOrder = 0;
                $totalProductReceived = 0;
                $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                    ->addFieldToFilter('purchase_order_id', $purchaseOrderId);
                $purchaseOrder->setStatus(6);

                foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
                    if ($purchaseOrderProduct->getQtyRecieved() < $purchaseOrderProduct->getQty()) {
                        $purchaseOrder->setStatus(5);
                    }
                    $totalProductOrder += $purchaseOrderProduct->getQty();
                    $totalProductReceived += $purchaseOrderProduct->getQtyRecieved();
                }
                $process = round(($totalProductReceived / $totalProductOrder) * 100, 2);

                $purchaseOrder->setDeliveryProcess($process)->save();

                $totalProduct_Recieved = $purchaseOrder->getData('total_products_recieved') + $totalProductDelivery;
                $purchaseOrder->setTotalProductsRecieved($totalProduct_Recieved);
                $purchaseOrder->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('supplier')->__('All deliveries have been saved. The purchase order has been completed.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('adminhtml/sup_purchaseorder/edit', array('id' => $purchaseOrderId, 'active' => 'delivery'));
                return;
            }

            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to find delivery to save! 111')
            );
            $this->_redirect('adminhtml/sup_purchaseorder/edit', array('id' => $purchaseOrderId, 'active' => 'delivery'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to find delivery to save!')
            );
            $this->_redirect('adminhtml/sup_purchaseorder/edit', array('id' => $purchaseOrderId, 'active' => 'delivery'));
            return;
        }
    }

    public function markaspaidAction() {
        $purchaseOrderId = $this->getRequest()->getParam('id');
        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
        try {
            $purchaseOrder->setPaidAll(1)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('supplier')->__('Purchase order has been paid.')
            );
            $this->_redirect('*/*/edit', array('id' => $purchaseOrderId));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('There is error while mark as paid purchase order.')
            );
            Mage::getSingleton('adminhtml/session')->addError(
                $e->getMessage()
            );
            $this->_redirect('*/*/edit', array('id' => $purchaseOrderId));
        }
    }

    /*Return order*/
    public function returnOrderAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.returnorder');
        $this->renderLayout();
    }

    public function returnOrderGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.returnorder');
        $this->renderLayout();
    }

    /*New return order*/
    public function newReturnOrderAction() {
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Add New Return Order'));
        $purchaseOrderId = $this->getRequest()->getParam('purchaseorder_id');
        $model = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);

        if ($model->getId() || $purchaseOrderId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('purchaseorder_data', $model);

            $this->loadLayout();

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_returnorder'))
                ->_addLeft($this->getLayout()->createBlock('supplier/adminhtml_purchaseorder_returnorder_tabs'));

            $this->renderLayout();
            if (Mage::getModel('admin/session')->getData('returnorder_product_import')) {
                Mage::getModel('admin/session')->setData('returnorder_product_import', null);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function prepareNewReturnOrderAction() {
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Add New Return Order'));
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.preparenewreturnorder')
            ->setProducts($this->getRequest()->getPost('isproducts', null));

        $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('purchaseorder_id'));
        $this->getLayout()->getBlock('related_grid_serializer')->addColumnInputName('qty_returned');

        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('returnorder_product_import')) {
            Mage::getModel('admin/session')->setData('returnorder_product_import', null);
        }
    }

    public function prepareNewReturnOrderGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.purchaseorder.edit.tab.preparenewreturnorder')
            ->setProducts($this->getRequest()->getPost('isproducts', null));
        $this->renderLayout();
    }

    /*Save return order */
    public function saveReturnAction() {
        $purchaseOrderId = $this->getRequest()->getPost('purchaseorder_id');

        if (!$purchaseOrderId) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Unable to find return to save!')
            );
            $this->_redirect('*/*/');
        }
        try {
            if ($data = $this->getRequest()->getPost()) {
//                die();
                $purchaseOrder = Mage::getModel('supplier/purchaseorder')->load($purchaseOrderId);
                if (isset($data['returnorder_products'])) {
                    $returnProducts = array();
                    Mage::helper('supplier')->parseStr(urldecode($data['returnorder_products']), $returnProducts);

                    if (count($returnProducts)) {
                        $totalProductReturn = 0;
                        Mage::getModel('admin/session')->unsetData('return_create_at');
                        foreach ($returnProducts as $pId => $enCoded) {
                            $codeArr = array();
                            Mage::helper('supplier')->parseStr(Mage::helper('supplier')->base64Decode($enCoded), $codeArr);

                            $purchaseorderProductItem = Mage::getModel('supplier/purchaseorder_product')
                                ->getCollection()
                                ->addFieldToFilter('purchase_order_id', $purchaseOrderId)
                                ->addFieldToFilter('product_id', $pId)
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
                            if ($purchaseorderProductItem->getId()) {
                                $sametime = strtotime(now());
                                $totalmaxQtyReturn = $purchaseorderProductItem->getQtyRecieved() - $purchaseorderProductItem->getQtyReturned();
                                if ($codeArr['qty_returned'] > $totalmaxQtyReturn)
                                    $codeArr['qty_returned'] = $totalmaxQtyReturn;
                                if (!$codeArr['qty_returned'] || $codeArr['qty_returned'] <= 0)
                                    continue;
                    /*save*/   
                                $purchaseorderProductItem->setQtyReturned($purchaseorderProductItem->getQtyReturned() + $codeArr['qty_returned'])
                                    ->save();
                                $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                                $return = Mage::getModel('supplier/returnorder');
                                /*save return*/
                                $return->setReturnDate($data['return_date'])
                                    ->setQtyReturned($codeArr['qty_returned'])
                                    ->setPurchaseOrderId($purchaseOrderId)
                                    ->setReason($data['reason'])
                                    ->setProductId($pId)
                                    ->setProductName($purchaseorderProductItem->getProductName())
                                    ->setProductSku($purchaseorderProductItem->getProductSku())
                                    ->setSametime($sametime)
                                    ->setCreatedBy($admin)
                                    ->save();
                                $totalProductReturn += $codeArr['qty_returned'];
                                Zend_Debug::dump($purchaseorderProductItem->getData());
                                Zend_Debug::dump($return->getData());
                            }

                        }
                    }
                    Zend_Debug::dump($totalProductReturn);

                    /*Qty return = 0 error*/
                    if ($totalProductReturn == 0) {
                        Mage::getSingleton('adminhtml/session')->addError(
                            Mage::helper('supplier')->__('Please select product and enter Qty greater than 0 to create return!')
                        );

                        $this->_redirect('*/*/newreturnorder', array(
                            'purchaseorder_id' => $purchaseOrderId,
                            'action' => 'newreturn',
                            'active' => 'return'
                        ));
                        return;
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('supplier')->__('Please select product(s) to create return!')
                    );

                    $this->_redirect('*/*/newreturnorder', array(
                        'purchaseorder_id' => $purchaseOrderId,
                        'action' => 'newreturn',
                        'active' => 'return'
                    ));
                    return;
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('supplier')->__('Please select product(s) to create return!')
                );
                $this->_redirect('*/*/newreturnorder', array(
                    'purchaseorder_id' => $purchaseOrderId,
                    'action' => 'newreturn',
                    'active' => 'return'
                ));
                return;
            }

            $totalProductOrder = 0;
            $totalProductReceived = 0;
            $purchaseOrderProducts = Mage::getModel('supplier/purchaseorder_product')->getCollection()
                ->addFieldToFilter('purchase_order_id', $purchaseOrderId);

            $purchaseOrder->setStatus(Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS);


            foreach ($purchaseOrderProducts as $purchaseOrderProduct) {
                if ($purchaseOrderProduct->getQtyRecieved() < $purchaseOrderProduct->getQty()) {
                    //$purchaseOrder->setStatus(5);
                    $purchaseOrder->setStatus(Furniturestore_Supplier_Model_Purchaseorder::RECEIVING_STATUS);
                }
                $totalProductOrder += $purchaseOrderProduct->getQty();
                $totalProductReceived += $purchaseOrderProduct->getQtyRecieved();
            }
            $process = round(($totalProductReceived / $totalProductOrder) * 100, 2);
            $purchaseOrder->setDeliveryProcess($process)->save();
            $totalProduct_Recieved = $purchaseOrder->getData('total_products_recieved') + $totalProductDelivery;
            $purchaseOrder->setTotalProductsRecieved($totalProduct_Recieved);
            $purchaseOrder->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('supplier')->__('The delivery has been saved.')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);
            $this->_redirect('adminhtml/sup_purchaseorder/edit', array('id' => $this->getRequest()->getParam('purchaseorder_id'), 'active' => 'delivery'));
            return;
//            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/');
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('supplier')->__('Unable to find delivery to save!')
        );
        $this->_redirect('*/*/');
    }

}