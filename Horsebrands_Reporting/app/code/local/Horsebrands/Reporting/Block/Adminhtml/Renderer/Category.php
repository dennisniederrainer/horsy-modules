<?php

class Horsebrands_Reporting_Block_Adminhtml_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $data = $row->getData('sku');
        
        if($data != null) {
           $data = substr($data, 0, 10);
        }
        
        return $data;
    }
}