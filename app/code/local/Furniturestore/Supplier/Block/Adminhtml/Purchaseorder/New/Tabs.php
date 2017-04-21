<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_New_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('purchaseorder_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('supplier')->__('Select Supplier'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('supplier')->__('Select Supplier'),
            'title'     => Mage::helper('supplier')->__('Select Supplier'),
            'content'   => $this->getLayout()
                                ->createBlock('supplier/adminhtml_purchaseorder_new_tab_form')
                                ->toHtml(),
        ));
		
        
        return parent::_beforeToHtml();
    }
}