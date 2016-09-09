<?php


class MDN_OrderPreparation_Block_Packing_Products extends Mage_Core_Block_Template {

    public function getProducts()
    {
        $orderId = $this->getOrder()->getId();
        $products = Mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($orderId);
        return $products;
    }

    public function getProductImageUrl($orderToPrepareItem)
    {
        $productId = $orderToPrepareItem->getproduct_id();
        $product = Mage::getModel('catalog/product')->load($productId);

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
     * return true if product manage stocks
     * 
     * @param type $orderToPrepareItem
     * @return type 
     */
    public function productManageStock($orderToPrepareItem)
    {
        $productId = $orderToPrepareItem->getproduct_id();
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getStockItem()->getManageStock();
    }
    
    /**
     * return true if user must confirm weight 
     */
    public function askForWeight()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/ask_for_weight')  ? '1' : '0');
    }
    
    /**
     * return weight 
     */
    public function getWeight()
    {
        $orderId = $this->getOrder()->getId();
        $orderToPrepare = Mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
        return $orderToPrepare->getreal_weight();
        
    }
    
    /**
     * return if we must Display + / - buttons
     */
    public function displayQuantityButtons()
    {
        return (Mage::getStoreConfig('orderpreparation/packing/display_quantity_button')  ? '1' : '0');
    }

}