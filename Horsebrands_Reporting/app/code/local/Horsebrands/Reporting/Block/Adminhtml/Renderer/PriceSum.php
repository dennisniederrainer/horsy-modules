<?php

class Horsebrands_Reporting_Block_Adminhtml_Renderer_PriceSum extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $price = $row->getData('product.price');
        $qty = $row->getData('qty_ordered');
        
        return number_format($price * $qty, 2);
    }
}