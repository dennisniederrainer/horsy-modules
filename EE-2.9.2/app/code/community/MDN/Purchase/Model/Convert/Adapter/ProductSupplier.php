<?php

class MDN_Purchase_Model_Convert_Adapter_ProductSupplier extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function saveRow(array $importData) {

        
        //load data
        $sku = $importData['sku'];
        $supplierCode = $importData['supplier_code'];

        //check if product exists
        $productId = mage::getModel('catalog/product')->getIdBySku($sku);
        if (!$productId) {
            throw new Exception('Unable to load product this sku ' . $sku);
        }

        //check if supplier exists
        $supplier = mage::getModel('Purchase/Supplier')->load($supplierCode, 'sup_code');
        if (!$supplier || !$supplier->getId()) {
            throw new Exception('Unable to load supplier with code ' . $supplierCode);
        }

        //create or update association
        $productSupplier = $this->getProductSupplierObject($productId, $supplier->getId());
        if (!$productSupplier) {
            $productSupplier = mage::getModel('Purchase/ProductSupplier');
            $productSupplier->setpps_product_id($productId)
                    ->setpps_supplier_num($supplier->getId());
        }


        //apply changes
        foreach($importData as $key => $value)
        {
            $productSupplier->setData($key, $value);
        }

        $productSupplier->save();

        return true;
    }

    /**
     * Enter description here...
     *
     */
    protected function getProductSupplierObject($productId, $supplierId) {
        $collection = mage::getModel('Purchase/ProductSupplier')
                        ->getCollection()
                        ->addFieldToFilter('pps_product_id', $productId)
                        ->addFieldToFilter('pps_supplier_num', $supplierId);
        foreach ($collection as $item) {
            return $item;
        }
        return null;
    }

}