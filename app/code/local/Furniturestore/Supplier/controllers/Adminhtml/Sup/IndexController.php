<?php

class Furniturestore_Supplier_Adminhtml_Sup_IndexController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/supplier/list_supplier');
    }
    /**
     * index action
     */
    public function indexAction() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $roleData = Mage::getModel('admin/user')->load($admin->getUserId())->getRole();
        $supplier = Mage::getModel('supplier/supplier')->getCollection()
            ->addFieldToFilter('user_id', $admin->getUserId())
            ->getFirstItem();
        if($roleData->getRoleName() == 'Role for supplier'){
            return $this->_redirect('adminhtml/sup_index/edit/', array('id' => $supplier->getId()));
        }
        $this->loadLayout()
            ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * Grid action
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('supplier/adminhtml_supplier_grid')->toHtml()
        );
    }

    public function importproductAction() {
        if (isset($_FILES['fileToUpload']['name']) && $_FILES['fileToUpload']['name'] != '') {
            try {
                $fileName = $_FILES['fileToUpload']['tmp_name'];
                $Object = new Varien_File_Csv();
                $dataFile = $Object->getData($fileName);
                $supplierProduct = array();
                $supplierProducts = array();
                $fields = array();
                $count = 0;
                $supplierHelper = Mage::helper('supplier');
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
                                        $supplierProduct[$fields[$index]] = $cell;
                                    }
                                }
                            $supplierProducts[] = $supplierProduct;
                        }
                    }

                $supplierHelper->importProduct($supplierProducts);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'furniturestore_supplier.log');
            }
        }
    }

    public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('furniturestore.supplier.edit.tab.products')
            ->setProducts($this->getRequest()->getPost('supplier_products', null));
        $this->renderLayout();
        if (Mage::getModel('admin/session')->getData('supplier_product_import'))
            Mage::getModel('admin/session')->setData('supplier_product_import', null);
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('furniturestore.supplier.edit.tab.products')
            ->setProducts($this->getRequest()->getPost('supplier_products', null));
        $this->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        $supplierId = $this->getRequest()->getParam('id');
        $model = Mage::getModel('supplier/supplier')->load($supplierId);

        if ($model->getId() || $supplierId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('supplier_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('supplier/supplier');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Supplier Manager'),
                Mage::helper('adminhtml')->__('Supplier Manager')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_supplier_edit'))
                ->_addLeft($this->getLayout()->createBlock('supplier/adminhtml_supplier_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('supplier')->__('Supplier does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * save item action
     */
    public function saveAction() {


        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('supplier/supplier');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                $admin = Mage::getModel('admin/session')->getUser()->getUsername();
                if (!$this->getRequest()->getParam('id')) {
                    $model->setData('created_by', $admin);
                    try {
                        $newPassword = Mage::helper('supplier')->generatePassword();
                        $user = Mage::getModel('admin/user')
                            ->setData(array(
                                'username'  => $data['user_admin'],
                                'firstname' => '[Supplier] ',
                                'lastname'  => $data['supplier_name'],
                                'email'     => $data['supplier_email'],
                                'is_active' => 1
                            ))->save();
                        $model->setUserId($user->getId());
                        $user->setNewPassword($newPassword)->setPasswordConfirmation($newPassword)->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/*/new');
                        return;
                    }

                }else{
                    $data['send_mail'] = 1;
                    $user_id = Mage::getModel('supplier/supplier')
                        ->load($this->getRequest()->getParam('id'))
                        ->getUserId();

                    $user = Mage::getModel('admin/user')->load($user_id);

                    $user->setUsername($data['user_admin'])
                        ->setEmail($data['supplier_email'])
                        ->setLastname($data['supplier_name'])
                        ->setFirstname('[Supplier]')
                        ->save();
                }



                /*Add rold for user admin supplier*/
                $role = Mage::getModel('admin/roles')->getCollection()
                    ->addFieldToFilter('role_name', 'Role for supplier')
                    ->getFirstItem();

                $user->setRoleIds(array($role->getId()))
                    ->setRoleUserId($user->getUserId())
                    ->saveRelations();

                /*Reset password for user admin supplier*/
                if(isset($data['auto_general_password'])){
                    $data['new_password'] = Mage::helper('supplier/supplier')->generatePassword();
                }
                if ($data['new_password']) {
                    $newPassword = $data['new_password'];
                    $user->setNewPassword($newPassword)->setPasswordConfirmation($newPassword)->save();

                }
                $model->save();
                if(isset($data['send_mail']) && isset($newPassword)){

                    $this->sendEmailNewPass($model->getId(), $newPassword);
                }
                $resource = Mage::getSingleton('core/resource');

                $writeConnection = $resource->getConnection('core_write');

                $installer = Mage::getModel('core/resource');

                $sqlNews = array();
                $sqlOlds = '';
                $countSqlOlds = 0;
                $productChangeds = array();
                $productNews = array();
                $productDeleteds = '';

                if (isset($data['supplier_products'])) {
                    $supplierProducts = array();
                    $supplierProductsExplodes = explode('&', urldecode($data['supplier_products']));
                    if (count($supplierProductsExplodes) <= 900) {
                        Mage::helper('supplier')->parseStr(urldecode($data['supplier_products']), $supplierProducts);
                    } else {
                        foreach ($supplierProductsExplodes as $supplierProductsExplode) {
                            $supplierProduct = '';
                            Mage::helper('supplier')->parseStr($supplierProductsExplode, $supplierProduct);
                            $supplierProducts = $supplierProducts + $supplierProduct;
                        }
                    }
                    if (count($supplierProducts)) {
                        $productIds = array();
                        $qtys = '';
                        $count = 0;
                        foreach ($supplierProducts as $pId => $enCoded) {
                            $codeArr = array();
                            Mage::helper('supplier')->parseStr(Mage::helper('supplier')->base64Decode($enCoded), $codeArr);
                            $supplierProductItem = Mage::getModel('supplier/product')
                                ->getCollection()
                                ->addFieldToFilter('supplier_id', $model->getId())
                                ->addFieldToFilter('product_id', $pId)
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
                            $productIds[] = $pId;
                            if ($supplierProductItem->getId()) {
                                $countSqlOlds++;
                                if (($codeArr['cost'] == $supplierProductItem->getCost()) && ($codeArr['discount'] == $supplierProductItem->getDiscount()) && ($codeArr['tax'] == $supplierProductItem->getTax()) && ($codeArr['supplier_sku'] == $supplierProductItem->getSupplierSku()))
                                    continue;


                                $productChangeds[$pId]['old_cost'] = $supplierProductItem->getCost();
                                $productChangeds[$pId]['new_cost'] = $codeArr['cost'];
                                $productChangeds[$pId]['old_discount'] = $supplierProductItem->getDiscount();
                                $productChangeds[$pId]['new_discount'] = $codeArr['discount'];
                                $productChangeds[$pId]['old_tax'] = $supplierProductItem->getDiscount();
                                $productChangeds[$pId]['new_tax'] = $codeArr['tax'];
                                $productChangeds[$pId]['old_suppliersku'] = $supplierProductItem->getSupplierSku();

                                $productChangeds[$pId]['new_suppliersku'] = $codeArr['supplier_sku'];

                                $sqlOlds .= 'UPDATE ' . $installer->getTableName('supplier/product') . ' 
                                                                        SET `cost` = \'' . $codeArr['cost'] . '\',
                                                                                `discount` = \'' . $codeArr['discount'] . '\',
                                                                                `tax` = \'' . $codeArr['tax'] . '\',
                                                                                `supplier_sku` = \'' . $codeArr['supplier_sku'] . '\'
                                                                                WHERE `supplier_product_id` =' . $supplierProductItem->getId() . ';';

                                if ($countSqlOlds == 900) {
                                    $writeConnection->query($sqlOlds);
                                    $countSqlOlds = 0;

                                }

                            } else {
                                $productNews[$pId]['new_cost'] = $codeArr['cost'];
                                $productNews[$pId]['new_discount'] = $codeArr['discount'];
                                $productNews[$pId]['new_tax'] = $codeArr['tax'];
                                $productNews[$pId]['new_suppliersku'] = $codeArr['supplier_sku'];
                                $sqlNews[] = array(
                                    'product_id' => $pId,
                                    'supplier_id' => $model->getId(),
                                    'discount' => $codeArr['discount'],
                                    'tax' => $codeArr['tax'],
                                    'cost' => $codeArr['cost'],
                                    'supplier_sku' => $codeArr['supplier_sku']
                                );
                                if (count($sqlNews) == 1000) {
                                    $writeConnection->insertMultiple($installer->getTableName('supplier/product'), $sqlNews);
                                    $sqlNews = array();
                                }
                            }
                        }
                        if (!empty($sqlNews)) {
                            $writeConnection->insertMultiple($installer->getTableName('supplier/product'), $sqlNews);
                        }
                        if (!empty($sqlOlds)) {
                            $writeConnection->query($sqlOlds);
                        }
                        $writeConnection->commit();
                        $productDeletes = Mage::getModel('supplier/product')->getCollection()
                            ->addFieldToFilter('supplier_id', $model->getId())
                            ->addFieldToFilter('product_id', array('nin' => $productIds));
                        if ($productDeletes->getSize() > 0) {
                            $i = 0;
                            foreach ($productDeletes as $productDelete) {
                                if ($i != 0)
                                    $productDeleteds .= ', ';
                                $productDeleteds .= Mage::helper('supplier')->getProductSkuByProductId($productDelete->getProductId());
                                $productDelete->delete();
                            }
                        }
                    }else {
                        $productDeletes = Mage::getModel('supplier/product')->getCollection()
                            ->addFieldToFilter('supplier_id', $model->getId());
                        if ($productDeletes->getSize() > 0) {
                            $i = 0;
                            foreach ($productDeletes as $productDelete) {
                                if ($i != 0)
                                    $productDeleteds .= ', ';
                                $productDeleteds .= Mage::helper('supplier')->getProductSkuByProductId($productDelete->getProductId());
                                $productDelete->delete();
                            }
                        }
                    }
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('supplier')->__('Supplier was successfully saved')
                );
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
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'supplier.csv';
        $content = $this->getLayout()
            ->createBlock('supplier/adminhtml_supplier_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'supplier.xml';
        $content = $this->getLayout()
            ->createBlock('supplier/adminhtml_supplier_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('supplier/supplier')->load($this->getRequest()->getParam('id'));
                Mage::getModel('admin/user')->load($model->getData('user_id'))->delete();
                $model->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Supplier was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /*Tab purchase order in edit supplier*/
    public function purchaseorderAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.supplier.edit.tab.purchaseorder');
        $this->renderLayout();
    }

    /*Tab purchase order in edit supplier _ grid*/
    public function purchaseorderGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('supplier.supplier.edit.tab.purchaseorder');
        $this->renderLayout();
    }

    /*Tab return order in edit supplier*/
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

    public function sendEmailNewPass($supplierId, $newpass){
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $storeId = Mage::app()->getStore()->getId();

        $templateId = Mage::getStoreConfig('supplier/remind_password/template', $storeId);
        $supplier = Mage::getModel('supplier/supplier')->load($supplierId);

        $user = Mage::getSingleton('admin/session');
        $userEmail = $user->getUser()->getEmail();
        $userUsername = $user->getUser()->getUsername();

        $emailTemplateVariables = array(
            'name' => $supplier->getSupplierName(),
            'password' => $newpass,
            'account' => $supplier->getData('user_admin')
        );

        $sender  = array(
            'name' => $userUsername,
            'email' => $userEmail
        );
        $transaction = Mage::getSingleton('core/email_template');
        $transaction->sendTransactional(
            $templateId, //Template config
            $sender, $supplier->getData('supplier_email'), $supplier->getData('contact_name'), $emailTemplateVariables
        );
        if (!$transaction->getSentSuccess()) {
            throw new Exception();
        }
        $translate->setTranslateInline(true);
    }

}