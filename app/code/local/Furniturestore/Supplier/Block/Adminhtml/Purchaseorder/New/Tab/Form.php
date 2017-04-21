<?php

class Furniturestore_Supplier_Block_Adminhtml_Purchaseorder_New_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareLayout() {
        $this->setChild('continue_button', $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('supplier')->__('Continue'),
                'onclick' => "setSettings('" . $this->getContinueUrl() . "','supplier_id')",
                'class' => 'save'
            ))
        );
        return parent::_prepareLayout();
    }

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $data = '';
        if (Mage::getSingleton('adminhtml/session')->getPurchaseorderData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPurchaseorderData();
            Mage::getSingleton('adminhtml/session')->setPurchaseorderData(null);
        } elseif (Mage::registry('purchaseorder_data')) {
            $data = Mage::registry('purchaseorder_data')->getData();
        }

        if (is_null($data)) {
            $post = $this->getRequest()->getPost();

            if (isset($post['supplier_select']))
                $data['supplier_id'] = $post['supplier_select'];
        }

        $fieldset = $form->addFieldset('purchaseorder_form', array(
            'legend' => Mage::helper('supplier')->__('Select Supplier')
        ));

        $fieldset->addField('supplier_id', 'select', array(
            'label' => Mage::helper('supplier')->__('Supplier'),
            'class' => 'required-entry',
            'name' => 'supplier_id',
            'disabled' => false,
            'values' => Mage::helper('supplier/supplier')->returnArrAllRowOfcolumnOftableSupplier('supplier_name'),
            'after_element_html' => '<script type="text/javascript">
                var productTemplateSyntax = /(^|.|\r|\n)({{(\w+)}})/;                   
                    function setSettings(urlTemplate, setElement) {
                        var supplier_id = $("supplier_id").value;
                        var currency = $("currency").value;                        
                        var change_rate = $("change_rate").value;
                        var wSelected = "";

                        if(!change_rate){
                            alert("Please fill Currency Change Rate to continue!");
                            return false;
                        }

                     
                        setLocation(urlTemplate+"supplier_id/"+supplier_id+"/currency/"+currency+"/change_rate/"+change_rate);
                    } 
                </script>'
        ));

        $fieldset->addField('currency', 'select', array(
            'label' => Mage::helper('supplier')->__('Currency'),
            'class' => 'required-entry',
            // 'required'    => true,
            'name' => 'currency',
            'values' => Mage::app()->getLocale()->getOptionCurrencies(),
            'after_element_html' => '<script type="text/javascript">$("currency").value=\'' . Mage::app()->getStore()->getBaseCurrencyCode() . '\'</script>',
        ));

        $fieldset->addField('change_rate', 'text', array(
            'label' => Mage::helper('supplier')->__('Currency Exchange Rate'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'change_rate',
            'after_element_html' => '<br /><div id="change_rate_comment"></div>
                                    <script type="text/javascript">
                                            var base_currency = \'' . Mage::app()->getStore()->getBaseCurrencyCode() . '\';
                                            var select_currency = $("currency").value;
                                            var change_rate = $("change_rate").value;
                                            if(!change_rate){
                                                $("change_rate").value = 1;
                                            }
                                            var comment = "(1 "+ base_currency +" = "+ $("change_rate").value +" "+select_currency +")";
                                            $("change_rate_comment").innerHTML = comment;
                                    </script>',
        ));


        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }

    public function getContinueUrl() {
        return $this->getUrl('*/*/new', array(
                    '_current' => true,
        ));
    }

}
