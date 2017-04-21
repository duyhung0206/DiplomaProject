<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:23
 */
class Furniturestore_Supplier_Model_Mysql4_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('supplier/product');
    }

    public function getReturnCollection($poId, $productIds) {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $productIds));
//            ->setIsGroupCountSql(true);
        $collection->addStoreFilter(0);
        $collection->getSelect()
            ->joinLeft(array('purchaseorderproduct' => $collection->getTable('furniturestore_purchase_order_product')), 'purchaseorderproduct.purchase_order_id IN (' . $poId . ') AND e.entity_id=purchaseorderproduct.product_id ', array(
                    'cost' => 'purchaseorderproduct.cost',
                    'tax' => 'purchaseorderproduct.tax',
                    'discount' => 'purchaseorderproduct.discount',
                    'qty' => 'purchaseorderproduct.qty',
                    'qty_recieved' => 'purchaseorderproduct.qty_recieved',
                    'qty_returned' => 'purchaseorderproduct.qty_returned'
                )
            )
            ->group('e.entity_id');
        return $collection;
    }
}