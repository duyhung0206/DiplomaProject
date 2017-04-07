<?php
/**
 * Created by PhpStorm.
 * User: duyhung
 * Date: 03/04/2017
 * Time: 10:22
 */

class Furniturestore_Supplier_Model_Purchaseorder extends Mage_Core_Model_Abstract
{
    /**
     * Purchase order status
     */
    const AWAITING_DELIVERY_STATUS = 5;
    const COMPLETE_STATUS = 6;
    const CANCELED_STATUS = 7;
    const PENDING_STATUS = 8;
    const WAITING_APPROVE_STATUS = 9; //waiting approval from Warehouse Manager
    const WAITING_CONFIRM_STATUS = 10; //waiting confirmation from Supplier
    const RECEIVING_STATUS = 11; //after created the first delivery
    
    public function _construct(){
        parent::_construct();
        $this->_init('supplier/purchaseorder');
    }
}