<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Returnorder_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('purchaseorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('supplier')->__('Order Return'));
    }

    protected function _beforeToHtml() {
        $this->addTab('newreturnorder_section', array(
            'label' => Mage::helper('supplier')->__('Products'),
            'title' => Mage::helper('supplier')->__('Products'),
            'url' => $this->getUrl('*/*/preparenewreturnorder', array(
                '_current' => true,
                'id' => $this->getRequest()->getParam('id'),
                'store' => $this->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
        ));
        return parent::_beforeToHtml();
    }

}
