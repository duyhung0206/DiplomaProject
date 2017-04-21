<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Inventory Supplier Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Inventory
 * @author      Magestore Developer
 */
class Magestore_Inventorypurchasing_Block_Adminhtml_Purchaseorder_Edit_Tab_Renderer_Delivery_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $columnName = $this->getColumn()->getName();
        $columnName = explode('_', $columnName);
        if ($columnName[1]) {
            $warehouseId = $columnName[1];
            $purchase_order_id = $this->getRequest()->getParam('id');

            $delivery = Mage::getResourceModel('inventorypurchasing/purchaseorder_delivery_warehouse_collection')
                            ->addFieldToFilter('purchase_order_id', $purchase_order_id)
                            ->addFieldToFilter('product_id', $row->getProductId())
                            ->addFieldToFilter('warehouse_id', $warehouseId)
                            ->addFieldToFilter('sametime', $row->getData('sametime'))
                            ->setPageSize(1)->setCurPage(1)->getFirstItem()
            ;
            /* get location product */
            $location = Mage::helper('inventoryplus/warehouse')->getProductLocation($warehouseId, $row->getProductId());
            if (!$location) {
                $location = $this->__('N/A Location');
            }

            if ($delivery->getData('qty_delivery')) {
                $qty = $delivery->getData('qty_delivery');
                $content = "" . $qty . "<br><span title='Product Location'>(" . $location . ")</span>";
            } else {
                $content = "0<br><span title='Product Location'>(" . $location . ")</span>";
            }
            return $content;
        } else {
            return parent::render($row);
        }
    }

}
