<?php
class Furniturestore_Supplier_Block_Adminhtml_Supplier_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('supplier_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('supplier')->__('Supplier'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => Mage::helper('supplier')->__('General Information'),
            'title' => Mage::helper('supplier')->__('General Information'),
            'content' => $this->getLayout()->createBlock('supplier/adminhtml_supplier_edit_tab_form')->toHtml()
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
            $this->addTab('purchaseorder_section', array(
                'label' => Mage::helper('supplier')->__('Purchase Orders'),
                'title' => Mage::helper('supplier')->__('Purchase Orders'),
                'url' => $this->getUrl('*/*/purchaseorder', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                )),
                'class' => 'ajax',
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
            ));
            $this->addTab('dropshipments_section', array(
                'label' => Mage::helper('supplier')->__('Drop Shipments'),
                'title' => Mage::helper('supplier')->__('Drop Shipments'),
                'url' => $this->getUrl('*/*/returnorder', array(
                    '_current' => true,
                    'id' => $this->getRequest()->getParam('id'),
                    'store' => $this->getRequest()->getParam('store')
                )),
                'class' => 'ajax',
            ));
        }
        return parent::_beforeToHtml();
    }
}