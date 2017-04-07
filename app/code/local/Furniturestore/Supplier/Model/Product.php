<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:22
 */

class Furniturestore_Supplier_Model_Product extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('supplier/product');
    }
}