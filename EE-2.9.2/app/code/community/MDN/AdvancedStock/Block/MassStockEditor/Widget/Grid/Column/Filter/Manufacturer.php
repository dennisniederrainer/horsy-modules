<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Filter_Manufacturer extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select {

    protected function _getOptions() {
        return $this->getManufacturers();
    }

    public function getCondition() {
        $manufacturerId = $this->getValue();

        $productIds =  array();

        //todo: optimize query !!!!!!!!!
        $collection = mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToFilter('manufacturer', $manufacturerId);
        foreach($collection as $product)
        {
            $productIds[] = $product->getId();
        }

        if ($this->getValue()) {
            return array('in' => $productIds);
        }
    }


    private function getManufacturers() {
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($product->getResource()->getTypeId())
                        ->addFieldToFilter('attribute_code', 'manufacturer');
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        $retour = array();
         $retour[] = array('label' => '', 'value' => '');
        foreach ($manufacturers as $manufacturer) {
            $retour[] = array('label' => $manufacturer['label'], 'value' => $manufacturer['value']);
        }

        return $retour;
    }

}