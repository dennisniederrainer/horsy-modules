<?php

class Horsebrands_Reporting_Block_Adminhtml_Renderer_CostSum extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $cost = $row->getData('product.cost');
        $qty = $row->getData('qty_ordered');
        
        return number_format($cost * $qty, 2);
    }
}