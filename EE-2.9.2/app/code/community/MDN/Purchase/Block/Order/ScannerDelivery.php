<?php

class MDN_Purchase_Block_Order_ScannerDelivery extends Mage_Adminhtml_Block_Widget_Form {

    protected $_purchaseOrder = null;

    /**
     * Return current purchase order
     * @return <type>
     */
    public function getPurchaseOrder() {
        if ($this->_purchaseOrder == null) {
            $poId = $this->getRequest()->getParam('po_num');
            $this->_purchaseOrder = Mage::getModel('Purchase/Order')->load($poId);
        }
        return $this->_purchaseOrder;
    }

    /**
     * Return PO data as json
     */
    public function getJsonData() {
        $data = array();
        $warehouse = $this->getPurchaseOrder()->getTargetWarehouse();

        foreach ($this->getPurchaseOrder()->getProducts() as $product) {
            $productId = $product->getpop_product_id();
            $productObj = Mage::getModel('catalog/product')->load($productId);
            $item = array();
            $item['pop_id'] = $product->getpop_num();
            $item['name'] = $product->getpop_product_name();
            $item['sku'] = $product->getsku();
            $item['expected_qty'] = $product->getRemainingQty();
            $item['scanned_qty'] = 0;
            $item['image_url'] = $this->getImageUrl($productObj);
            $item['serials'] = array();
            $item['barcode'] = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
            $item['new_barcode'] = '';
            $item['location'] = $warehouse->getProductLocation($productId);
            $item['new_location'] = '';

            $data[] = $item;
        }

        return Zend_Json::encode($data);
    }

    /**
     * Return image url for product
     * @param <type> $product
     * @return <type>
     */
    protected function getImageUrl($product) {
        if ($product->getSmallImage()) {
            return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
        } else {
            //try to find image from configurable product
            $configurableProduct = Mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getConfigurableProduct($product);
            if ($configurableProduct) {
                if ($configurableProduct->getSmallImage()) {
                    return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $configurableProduct->getSmallImage();
                }
            }
        }

        return '';
    }

    /**
     * Scan location setting
     * @return <type>
     */
    public function scanLocation() {
        return (Mage::getStoreConfig('purchase/scanner/location') ? '1' : '0');
    }

    /**
     *
     */
    public function getFormUrl() {
        return Mage::helper('adminhtml')->getUrl('*/*/CommitScannerDelivery');
    }

    public function getBackUrl() {
        return Mage::helper('adminhtml')->getUrl('Purchase/Orders/Edit', array('po_num' => $this->getPurchaseOrder()->getId()));
    }

}
