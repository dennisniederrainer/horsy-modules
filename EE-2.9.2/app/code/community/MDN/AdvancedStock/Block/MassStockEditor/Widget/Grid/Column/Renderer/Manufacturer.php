<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_Manufacturer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $productId = $row->getproduct_id();
        $product = mage::getModel('catalog/product')->load($productId);
        return $product->getAttributeText('manufacturer');
    }

}