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

        $suppliers = Mage::helper('supplier/supplier')->returnArrAllRowOfcolumnOftableSupplier('supplier_name');
        $content = "";
        foreach ($suppliers as $supplierId => $value){
            $supplierProducts = Mage::getModel('supplier/product')->getCollection()
                ->addFieldToFilter('supplier_id', $supplierId);
            $productSuppliers = array();
            foreach ($supplierProducts as $supplierProduct){
                $productSuppliers[] = $supplierProduct->getProductId();
            }
            $storeId = Mage::app()->getStore()->getId();
            /** @var $collection Mage_Reports_Model_Resource_Product_Lowstock_Collection  */
            $collection = Mage::getResourceModel('reports/product_lowstock_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $productSuppliers))
                ->setStoreId($storeId)
                ->filterByIsQtyProductTypes()
                ->joinInventoryItem('qty')
                ->useManageStockFilter($storeId)
                ->useNotifyStockQtyFilter($storeId)
                ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

            if( $storeId ) {
                $collection->addStoreFilter($storeId);
            }
            if(count($collection) == 0){
                $html = "";
            }else{
                $html = "<table id='lowstock_supplier_".$supplierId."' class='table_lowstock'>
                <caption>Product low stock in supplier</caption>
                <thead><tr><th>Product name</th><th>Sku</th><th>Qty</th><th>Qty wait delivery</th></tr></thead>
                <tbody>";
                foreach ($collection as $product){
                    if(Mage::helper('supplier/purchaseorder')->checkEnoughtdelivery($product->getData('entity_id'))){
                        $html.= "<tr><td>".$product->getData('name')."</td>";
                        $html.= "<td>".$product->getData('sku')."</td>";
                        $html.= "<td>".$product->getData('qty')."</td>";
                        $html.= "<td>".Mage::helper('supplier/purchaseorder')->getStatusDeliveryByProductId($product->getData('entity_id'))."</td>";

                    }else{
                        $html.= "<tr style='color: red;'><td>".$product->getData('name')."</td>";
                        $html.= "<td>".$product->getData('sku')."</td>";
                        $html.= "<td>".$product->getData('qty')."</td>";
                        $html.= "<td>".Mage::helper('supplier/purchaseorder')->getStatusDeliveryByProductId($product->getData('entity_id'))."</td>";

                    }
                 }
                $html.="</tbody></table>";
            }
            $content .= $html;

        }

        $fieldset->addField('supplier_id', 'select', array(
            'label' => Mage::helper('supplier')->__('Supplier'),
            'class' => 'required-entry',
            'name' => 'supplier_id',
            'disabled' => false,
            'values' => Mage::helper('supplier/supplier')->returnArrAllRowOfcolumnOftableSupplier('supplier_name'),
            'after_element_html' => $content.'
                <style>
                    caption {
                        font-size: 15px;
                        padding-top: 8px;
                        padding-bottom: 8px;
                        color: #777;
                        text-align: left;
                    }
                    .table_lowstock{
                        width:204%;
                    }
                    .table_lowstock>caption+thead>tr:first-child>td, .table_lowstock>caption+thead>tr:first-child>th, .table_lowstock>colgroup+thead>tr:first-child>td, .table_lowstock>colgroup+thead>tr:first-child>th, .table_lowstock>thead:first-child>tr:first-child>td, .table_lowstock>thead:first-child>tr:first-child>th {
                        border-top: 0;
                    }
                    .table_lowstock>thead>tr>th {
                        vertical-align: bottom;
                        border-bottom: 2px solid #ddd;
                    }
                    .table_lowstock>tbody>tr>td, .table_lowstock>tbody>tr>th, .table_lowstock>tfoot>tr>td, .table_lowstock>tfoot>tr>th, .table_lowstock>thead>tr>td, .table_lowstock>thead>tr>th {
                        padding: 8px;
                        line-height: 1.42857143;
                        vertical-align: top;
                        border-top: 1px solid #ddd;
                    }
                </style>
            
                <script type="text/javascript">
                    Event.observe($("supplier_id"),"change", function(){
                        $$(".table_lowstock").forEach(function(element) {
                            $(element).hide();
                        });
                        if($("lowstock_supplier_"+$("supplier_id").value) != null)
                            $("lowstock_supplier_"+$("supplier_id").value).show();
                    })
                    $$(".table_lowstock").forEach(function(element) {
                            $(element).hide();
                        });
                    $("lowstock_supplier_'.key($suppliers).'").show();
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
