<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_AvailableQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $productId = $row->getproduct_id();
        $productAvailabilityStatus = Mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->getForOneProduct($productId);
        return $productAvailabilityStatus->getpa_available_qty();
    }

}
