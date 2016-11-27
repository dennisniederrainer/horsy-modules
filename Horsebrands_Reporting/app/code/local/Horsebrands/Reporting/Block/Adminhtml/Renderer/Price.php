<?php

class Horsebrands_Reporting_Block_Adminhtml_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        return number_format($row->getData($this->getColumn()->getIndex()), 2);
    }
}