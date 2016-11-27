<?php

class Horsebrands_Reporting_Block_Checklist extends Mage_Core_Block_Template {
 
    /*public function _prepareLayout() {
        return parent::_prepareLayout();
    }*/
 
    public function getOrderoverview() {
        if (!$this->hasData('checklist')) {
            $this->setData('checklist', Mage::registry('checklist'));
        }
        return $this->getData('checklist');
    }
}