<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_Editdelivery_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('purchaseorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('supplier')->__('Create new delivery'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('delivery_section',array(
                'label'     => Mage::helper('supplier')->__('Create new delivery'),
                'title'     => Mage::helper('supplier')->__('Create new delivery'),
                'url'       => $this->getUrl('*/*/preparedelivery',array(
                  '_current'	=> true,
                  'id'			=> $this->getRequest()->getParam('id'),
                  'store'		=> $this->getRequest()->getParam('store')
                )),
                'class'     => 'ajax',
        ));
        return parent::_beforeToHtml();
    }
}