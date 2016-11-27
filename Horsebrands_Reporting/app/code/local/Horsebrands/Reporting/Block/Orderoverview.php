<?php

class Horsebrands_Reporting_Block_Orderoverview extends Mage_Core_Block_Template {
 
    // public function _prepareLayout() {
    //     return parent::_prepareLayout();
    // }
 
    public function getOrderoverview() {
        if (!$this->hasData('orderoverview')) {
            $this->setData('orderoverview', Mage::registry('orderoverview'));
        }
        return $this->getData('orderoverview');
    }
}