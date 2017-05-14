<?php


class Furniturestore_Supplier_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCountryList() {
        $result = array();
        $collection = Mage::getModel('directory/country')->getCollection();
        foreach ($collection as $country) {
            $cid = $country->getId();
            $cname = $country->getName();
            $result[$cid] = $cname;
        }
        return $result;
    }

    public function getCountryListHash() {
        $options = array();
        foreach ($this->getCountryList() as $value => $label) {
            if ($label)
                $options[] = array(
                    'value' => $value,
                    'label' => $label
                );
        }
        return $options;
    }

    /**
     * Retrieve random password
     *
     * @param   int $length
     * @return  string
     */
    public function generatePassword($length = 8)
    {
        $chars = Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_UPPERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_SPECIALS;
        return Mage::helper('core')->getRandomString($length, $chars);
    }

    public function filterDates($array, $dateFields) {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    public function parseStr($str, array &$arr = null) {
        return parse_str($str, $arr);
    }

    public function importProduct($data) {
        if (count($data)) {
            Mage::getModel('admin/session')->setData('supplier_product_import', $data);
        }
    }

    /**
     *
     * @param string $data
     * @return string
     */
    public function base64Decode($data, $strict = false) {
        return base64_decode($data, $strict);
    }

    public function getProductSkuByProductId($productId) {
        $product = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id', $productId)
            ->setPageSize(1)->setCurPage(1)->getFirstItem();
        return $product->getSku();
    }
            
}
