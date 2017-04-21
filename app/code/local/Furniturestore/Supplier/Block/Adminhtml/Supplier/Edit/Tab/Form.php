<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 11:36
 */

class Furniturestore_Supplier_Block_Adminhtml_Supplier_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (Mage::getSingleton('adminhtml/session')->getSupplierData()) {
            $data = Mage::getSingleton('adminhtml/session')->getSupplierData();
            Mage::getSingleton('adminhtml/session')->setSupplierData(null);
        } elseif (Mage::registry('supplier_data')) {
            $data = Mage::registry('supplier_data')->getData();
        }
        $fieldset = $form->addFieldset('supplier_form', array(
            'legend' => Mage::helper('supplier')->__('General Information')
        ));

        if ($this->getRequest()->getParam('id'))
            $fieldset->addField('created_by', 'label', array(
                'label' => Mage::helper('supplier')->__('Created by'),
            ));

        $fieldset->addField('supplier_name', 'text', array(
            'label' => Mage::helper('supplier')->__('Supplier Name '),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'supplier_name',
        ));

        $fieldset->addField('contact_name', 'text', array(
            'label' => Mage::helper('supplier')->__('Contact Person'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'contact_name',
        ));

        $fieldset->addField('supplier_email', 'text', array(
            'label' => Mage::helper('supplier')->__('Email'),
            // 'class' => 'required-entry',
            // 'required' => true,
            'name' => 'supplier_email',
        ));

        $fieldset->addField('telephone', 'text', array(
            'label' => Mage::helper('supplier')->__('Telephone'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'telephone',
        ));
        $fieldset->addField('fax', 'text', array(
            'label' => Mage::helper('supplier')->__('Fax'),
            'required' => false,
            'name' => 'fax',
        ));
        $fieldset->addField('street', 'text', array(
            'label' => Mage::helper('supplier')->__('Street'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'street',
        ));
        $fieldset->addField('city', 'text', array(
            'label' => Mage::helper('supplier')->__('City'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'city',
        ));
        $fieldset->addField('country_id', 'select', array(
            'label' => Mage::helper('supplier')->__('Country'),
            'required' => true,
            'name' => 'country_id',
            'values' => Mage::helper('supplier')->getCountryListHash(),
        ));

        $fieldset->addField('stateEl', 'note', array(
            'label' => Mage::helper('supplier')->__('State/Province'),
            'name' => 'stateEl',
            'class' => 'required-entry',
            'required' => true,
            'text' => $this->getLayout()->createBlock('supplier/adminhtml_supplier_region')->setTemplate('supplier/supplier/region.phtml')->toHtml(),
        ));

        $fieldset->addField('postcode', 'text', array(
            'label' => Mage::helper('supplier')->__('Zip/Postal Code'),
            'name' => 'postcode',
        ));

        $fieldset->addField('website', 'text', array(
            'label' => Mage::helper('supplier')->__('Website'),
            'required' => false,
            'name' => 'website',
        ));
        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('supplier')->__('Description'),
            'title' => Mage::helper('supplier')->__('Description'),
            'style' => 'width:274px; height:200px;',
            'wysiwyg' => false,
            'required' => false,
        ));

        $fieldset->addField('supplier_status', 'select', array(
            'label' => Mage::helper('supplier')->__('Status'),
            'name' => 'supplier_status',
            'values' => Mage::getSingleton('supplier/status')->getOptionHash(),
        ));

        if (Mage::getStoreConfig('supplier/supplier_group/enable_dropship')) {
            $fieldset2 = $form->addFieldset('supplierpass_form', array(
                'legend' => Mage::helper('supplier')->__('Password Management')
            ));

            $fieldset2->addField('new_password', 'text', array(
                'label' => Mage::helper('supplier')->__('New Password'),
                'required' => false,
                'name' => 'new_password',
            ));

            $fieldset2->addField('auto_general_password', 'checkbox', array(
                'label' => Mage::helper('supplier')->__('Auto-generated password'),
                'required' => false,
                'name' => 'auto_general_password',
            ));

            $fieldset2->addField('send_mail', 'checkbox', array(
                'label' => Mage::helper('supplier')->__('Send new password to supplier'),
                'required' => false,
                'name' => 'send_mail',
            ));
        }

        $form->setValues($data);
        return parent::_prepareForm();
    }

   
}