<?php


class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('purchaseorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('supplier')->__('Purchase Order Information'));
    }

    public function getNoticeText() {
        $purchaseOrder = Mage::registry('purchaseorder_data');
        if (!$purchaseOrder || !$purchaseOrder->getId())
            return;

        $message = '';
        if ($purchaseOrder->getStatus() == Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS && Mage::getStoreConfig('supplier/purchasing/require_confirmation_from_supplier')) {
            $message = $this->__('This purchase order is pending. You must click on Request Confirmation button to move to next step.');
        } elseif ($purchaseOrder->getStatus() == Furniturestore_Supplier_Model_Purchaseorder::PENDING_STATUS && !Mage::getStoreConfig('supplier/purchasing/require_confirmation_from_supplier')) {
            $message = $this->__('This purchase order is pending. You must click on Confirm Purchase Order button to move to next step.');
        } elseif ($purchaseOrder->getStatus() == Furniturestore_Supplier_Model_Purchaseorder::WAITING_CONFIRM_STATUS) {
            $message = $this->__('This purchase order is waiting for confirmation. You must click on Confirm Purchase Order button to move to next step.');
        }
        /* end Michael 201602 */
        if ($message) {
            $html = '<div id="peding_purchaseorder_notice">
                        <ul class="messages">
                            <li id="purchase_order_notice" class="notice-msg">
                                <ul>
                                    <li>
                                        <span>' .
                    //$this->__('This purchase order still is pending. You must click on Confirm Purchase Order button to process it.')
                    $message
                    . '</span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>';
            return $html;
        }
    }

    protected function _beforeToHtml() {
        $deliveryActive = false;
        $returnActive = false;
        $active = $this->getRequest()->getParam('active');
        if ($active == 'delivery') {
            $deliveryActive = true;
        } elseif ($active == 'return') {
            $returnActive = true;
        }

        $this->addTab('form_section', array(
            'label' => Mage::helper('supplier')->__('General Information'),
            'title' => Mage::helper('supplier')->__('General Information'),
            'content' => $this->getNoticeText() .
                    $this->getLayout()
                    ->createBlock('supplier/adminhtml_purchaseorder_edit_tab_form')
                    ->toHtml(),
        ));

        $this->addTab('products_section', array(
            'label' => Mage::helper('supplier')->__('Products'),
            'title' => Mage::helper('supplier')->__('Products'),
            'url' => $this->getUrl('*/*/product', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));

        if ($this->getRequest()->getParam('id')) {
            $purchaseorder = Mage::getModel('supplier/purchaseorder')->load($this->getRequest()->getParam('id'));
            if ($purchaseorder->getStatus() != Furniturestore_Supplier_Model_Purchaseorder::CANCELED_STATUS) {
                $this->addTab('delivery_section', array(
                    'label' => Mage::helper('supplier')->__('Deliveries'),
                    'title' => Mage::helper('supplier')->__('Deliveries'),
                    'url' => $this->getUrl('*/*/delivery', array(
                        '_current' => true,
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => $this->getRequest()->getParam('store')
                    )),
                    'class' => 'ajax',
                    'active' => $deliveryActive
                ));
                $this->addTab('returnorder_section', array(
                    'label' => Mage::helper('supplier')->__('Return Orders'),
                    'title' => Mage::helper('supplier')->__('Return Orders'),
                    'url' => $this->getUrl('*/*/returnorder', array(
                        '_current' => true,
                        'id' => $this->getRequest()->getParam('id'),
                        'store' => $this->getRequest()->getParam('store')
                    )),
                    'class' => 'ajax',
                    'active' => $returnActive
                ));
            }

//            if ($purchaseorder->getStatus() == Furniturestore_Supplier_Model_Purchaseorder::COMPLETE_STATUS && $purchaseorder->getCompleteBefore()) {
//                $this->addTab('notreceive_section', array(
//                    'label' => Mage::helper('supplier')->__('Shortfall Items'),
//                    'title' => Mage::helper('supplier')->__('Shortfall Items'),
//                    'url' => $this->getUrl('*/*/productNotReceive', array(
//                        '_current' => true,
//                        'id' => $this->getRequest()->getParam('id'),
//                        'store' => $this->getRequest()->getParam('store')
//                    )),
//                    'class' => 'ajax',
////                    'active' => $returnActive
//                ));
//            }
        }
        return parent::_beforeToHtml();
    }

}
