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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Model_Observer {

    private $_maxOrder;

    /**
     * Function to process orders and upadte ordered qty, reserved qty ....
     *
     */
    public function UpdateStocksForOrders() {
        $debug = '<h1>Update stocks for orders</h1>';

        //collect orders with stocks_updated = 0 and status not finished (complete or canceled)
        $collection = mage::getModel('sales/order')
                        ->getCollection()
                        ->addFieldToFilter('stocks_updated', '0')
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('state', array('nin' => array('complete', 'canceled')));

        //max orders
        $this->_maxOrder = (int) mage::getStoreConfig('advancedstock/cron/order_update_stocks_max');
        $collection->getSelect()->limit($this->_maxOrder);
        
        $count = 0;
        foreach ($collection as $order) {
            $debug .= '<p><b>Processing order #' . $order->getId() . ' at (' . date('Y-m-d H:i:s') . ')</b>';

            try {
                //parse each product
                foreach ($order->getAllItems() as $item) {
                    $productId = $item->getproduct_id();
                    
                    $debug .= '<br>Process product '.$item->getName().' : ';

                    //get preparation warehouse
                    $preparationWarehouseId = mage::helper('AdvancedStock/Router')->getWarehouseForOrderItem($item, $order);
                    if (!$preparationWarehouseId)
                        $preparationWarehouseId = 1;
                    $debug .= 'Affect warehouse #'.$preparationWarehouseId;

                    //Affect order item to warehouse
                    Mage::helper('AdvancedStock/Router')->affectWarehouseToOrderItem(array('order_item_id' => $item->getId(), 'warehouse_id' => $preparationWarehouseId));
                }

                //update stocks_updated
                if ($order->getPayment()) { {
                        $debug .= '<br>Set stocks updated = 1 for order #' . $order->getId();
                        $this->setStocksAsUpdated($order);
                        Mage::dispatchEvent('advancedstock_order_considered_by_cron', array('order_id' => $order->getId()));
                    }
                }
                else
                    $debug .= '<br>--->Unable to retrieve payment for order #' . $order->getId();

                //execut X orders at once
                if ($count > $this->_maxOrder) {
                    $debug .= '<br>Exit after ' . $this->_maxOrder . ' loops';
                    break;
                }
                $count++;
            } catch (Exception $ex) {
                mage::logException($ex);
                $debug .= '<p>Error updating stocks for order #' . $order->getId() . ' : ' . $ex->getMessage() . '</p>';
            }
        }

        //print debug informaiton
        if (Mage::getStoreConfig('advancedstock/cron/debug'))
            echo $debug;
        mage::log($debug, null, 'erp_new_orders_consideration.log');
        
    }

    /**
     * Set stocks udpate to 1 in order
     * Use sql query instead of models to avoid to rewrite data as this process is executed by the cron
     */
    protected function setStocksAsUpdated($order)
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'update '.$prefix.'sales_flat_order set stocks_updated = 1 where entity_id = '.$order->getId();
        mage::getResourceModel('catalog/product')->getReadConnection()->query($sql);
    }

    /**
     * Set payment validated to true when invoice is created
     *
     */
    public function sales_order_invoice_pay(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('advancedstock/general/auto_validate_payment') == 1) {
            try {
                //recupere les infos
                $order = $observer->getEvent()->getInvoice()->getOrder();
                $order->setpayment_validated(1);    //intentionnaly do not save, save is done by the event dispatcher !!!

                mage::log('payment_validated set to true for order #' . $order->getId());
            } catch (Exception $ex) {
                mage::log('Error when validating payment_validated: ' . $ex->getMessage());
            }
        }
    }

    /**
     * Called when an order is placed
     *
     * @param Varien_Event_Observer $observer
     * @return none
     */
    public function sales_order_afterPlace(Varien_Event_Observer $observer) {

        try {
            $order = $observer->getEvent()->getOrder();

            //init payment validated
            if ($order->getpayment_validated() != 1)
                $order->setpayment_validated(0);

            //copy cost
            foreach ($order->getAllItems() as $item) {
                $productId = $item->getproduct_id();
                $product = mage::getModel('catalog/product')->load($productId);
                if ($product) {

                    //store cost
                    switch ($product->gettype_id()) {
                        case 'simple':
                            $item->setData(mage::helper('purchase/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName(), $product->getcost());
                            break;
                        case 'configurable':
                        case 'bundle':
                            $item->setData(mage::helper('purchase/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName(), $this->computeCostFromSubProducts($item, $order->getAllItems()));
                            break;
                    }
                }
            }

        } catch (Exception $ex) {
            Mage::logException($ex);
        }
    }

    /**
     * Change stock column in rma product reservation to display information for every warehouse
     *
     * @param Varien_Event_Observer $observer
     */
    public function productreturn_reservationgrid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('qty', array(
            'header' => Mage::helper('ProductReturn')->__('Stock'),
            'index' => 'qty',
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => false,
            'sortable' => false
        ));
    }

    /**
     * Compute cost from the sum of the costs of subproducts
     *
     * @param unknown_type $parentItem
     * @param unknown_type $items
     */
    private function computeCostFromSubProducts($parentItem, $items) {
        $retour = 0;
        $parentQuoteItemId = $parentItem->getquote_item_id();
        $parentItemQty = $parentItem->getqty_ordered();

        foreach ($items as $item) {
            if ($item->getquote_parent_item_id() == $parentQuoteItemId) {
                $product = mage::getModel('catalog/product')->load($item->getproduct_id());
                $retour += $product->getCost() * ($item->getqty_ordered() / $parentItemQty);
            }
        }

        return $retour;
    }

    /**
     * Update sales history for every products
     * Added here just to get an entry in models for cron
     * Called every sunday night
     */
    public function updateAllSalesHistory() {
        //if auto update enabled
        if (mage::getStoreConfig('advancedstock/sales_history/enable_auto_update') == 1)
            mage::helper('AdvancedStock/Sales_History')->scheduleUpdateForAllProducts();
    }

    /**
     * Called when sales history is updated
     */
    public function advancedstock_sales_history_change(Varien_Event_Observer $observer) {
        $salesHistory = $observer->getEvent()->getsales_history();

        //if auto calculate prefered stock level is enabled, refresh it
        if (mage::getStoreConfig('advancedstock/prefered_stock_level/enable_auto_calculation') == 1) {
            $productId = $salesHistory->getsh_product_id();
            mage::helper('AdvancedStock/Product_PreferedStockLevel')->updateForProduct($productId);
        }
    }

}

