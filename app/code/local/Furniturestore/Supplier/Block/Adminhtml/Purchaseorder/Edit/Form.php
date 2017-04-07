<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        if (!$this->getRequest()->getParam('purchaseorder_id')) {
            $form = new Varien_Data_Form(array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save', array(
                            'id' => $this->getRequest()->getParam('id'),
                            'supplier_id' => $this->getRequest()->getParam('supplier_id'),
                            'currency' => $this->getRequest()->getParam('currency'),
                            'change_rate' => $this->getRequest()->getParam('change_rate'),
                        )),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ));
        } else {
            $action = $this->getRequest()->getParam('action');
            if ($action == 'newdelivery') {
                $form = new Varien_Data_Form(array(
                            'id' => 'edit_form',
                            'action' => $this->getUrl('*/*/savedelivery', array(
                                'purchaseorder_id' => $this->getRequest()->getParam('purchaseorder_id'),
                            )),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data'
                        ));
            }
            if ($action == 'newreturnorder') {
                $form = new Varien_Data_Form(array(
                            'id' => 'edit_form',
                            'action' => $this->getUrl('*/*/savereturnorder', array(
                                'purchaseorder_id' => $this->getRequest()->getParam('purchaseorder_id'),
                            )),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data'
                        ));
            }
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}