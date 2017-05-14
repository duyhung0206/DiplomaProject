<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 13/05/2017
 * Time: 23:39
 */

class Furniturestore_Supplier_Block_Adminhtml_Report_Renderer_Supplier extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $productId = $row->getData('entity_id');
        $supplierProducts = Mage::getModel('supplier/product')->getCollection()
            ->addFieldToFilter('product_id', $productId);
        $suppliersId = array();
        foreach ($supplierProducts as $supplierProduct){
            $suppliersId[] = $supplierProduct->getSupplierId();
        }
        if(count($suppliersId) != 0){
            $content = '';
            $suppliers = Mage::getModel('supplier/supplier')->getCollection()
                ->addFieldToFilter('supplier_id', array('in' => $suppliersId));
            foreach ($suppliers as $supplier){
                $url_edit = Mage::helper('adminhtml')->getUrl('*/sup_index/edit', array('id'=> $supplier->getId()));
                $content .= "<div>
                            <div style='float:left;'>
                                <a href=".$url_edit.">
                                    <span style='margin-right:10px; text-align:center;'>".$supplier->getData('supplier_name')."</span>
                                </a>
                            </div>
                        </div>";
            }
            return $content;
        }else{
            return '<span style="color: red;">N/A</span>';
        }


    }
}
