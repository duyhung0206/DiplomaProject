<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:22
 */

class Furniturestore_Supplier_Model_Mysql4_Delivery extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('supplier/delivery', 'delivery_id');
    }
}