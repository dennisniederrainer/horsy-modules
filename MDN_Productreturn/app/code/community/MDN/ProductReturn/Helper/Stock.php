<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_Stock extends Mage_Core_Helper_Abstract
{

    public function manageProductDestination($product, $websiteId, $description, $rma)
    {
        $productId   = $product['product_id'];
        $qty         = $product['qty'];
        $destination = $product['destination'];
        $rpId = $product['rp_id'];

        $debug = 'Manage inventory for product #'.$productId.' with quantity '.$qty.' for destination : '.$destination.' ('.$description.')';
        Mage::helper('ProductReturn')->log($debug);

        $this->productBackInStock($productId, $qty, $destination, $websiteId, $description, $rma->getId());

        $rmaProduct = Mage::getModel('ProductReturn/RmaProducts')->load($rpId);
        $rmaProduct->setrp_destination_processed(1)->save();

        $product = Mage::getModel('catalog/product')->load($productId);
        $historyMsg = $qty.'x '.$product->getName().' '.$this->__($destination);
        $rma->addHistoryRma($historyMsg);
    }

    /**
     * Product back in stock
     * This helper is designed to be rewritten per stock management extension
     */
    public function productBackInStock($productId, $qty, $destination, $websiteId, $description, $rmaId = null)
    {

        switch ($destination) {
            case MDN_ProductReturn_Model_RmaProducts::kDestinationCustomer:
                //nothing
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationDestroy:
                //nothing
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationStock:
                //increase product stock (magento way)
                $product = mage::getModel('catalog/product')->load($productId);

                if ($product->getId()) {
                    $stockItem = $product->getStockItem();
                    if ($stockItem) {
                        $stockItem->setqty($stockItem->getqty() + $qty);
                        $stockItem->save();
                    }
                }
                break;
            case MDN_ProductReturn_Model_RmaProducts::kDestinationSupplier:
                //nothing
                break;
        }
    }

}