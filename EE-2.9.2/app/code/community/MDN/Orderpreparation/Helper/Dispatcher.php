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
class MDN_Orderpreparation_Helper_Dispatcher extends Mage_Core_Helper_Abstract {

    /**
     * Dispatch order in fullstock / stockless / ignored tab
     */
    public function DispatchOrder($order) {
        
        //delete old record(s)
        $debug = '##Dispatch order #' . $order->getId();
        $this->removeOrderFromOrderToPreparePending($order);

        //status check
        if (($order->getStatus() == 'complete') || ($order->getStatus() == 'canceled'))
        {
            $debug .= ', status is not supported !';
            return $debug;
        }

        //get all preparation warehouses for this order
        $warehouses = $order->getPreparationWarehouses();

        //apply dispatch for every warehouse
        foreach ($warehouses as $warehouse) {
            //Dispatch order only if it doesn't belong to selected orders
            if (!$this->orderBelongsToSelectedOrders($order, $warehouse)) {
                if (!$order->IsCompletelyShipped()) {

                    //dispatch order depending of stock state
                    $opp_type = 'stockless';
                    if ($order->IsFullStock($warehouse->getId()))
                        $opp_type = 'fullstock';
                    if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($order))
                        $opp_type = 'ignored';
                    $ShipToName = '';
                    if ($order->getShippingAddress() != null)
                        $ShipToName = $order->getShippingAddress()->getName();

                    //insert record
                    $sortOrderValue = mage::getModel('Orderpreparation/ordertopreparepending')->calculateSortValue($order);
                    $OrderToPreparePending = mage::getModel('Orderpreparation/ordertopreparepending')
                                    ->setopp_preparation_warehouse($warehouse->getId())
                                    ->setopp_order_id($order->getId())
                                    ->setopp_remain_to_ship($this->getRemainToShipForOrder($order, $warehouse))
                                    ->setopp_shipto_name($ShipToName)
                                    ->setopp_details(mage::getModel('Orderpreparation/Ordertoprepare')->getDetailsForOrder($order, false))
                                    ->setopp_order_increment_id($order->getIncrementId())
                                    ->setopp_type($opp_type)
                                    ->setopp_shipping_method($order->getshipping_description())
                                    ->setopp_payment_validated($order->getpayment_validated())
                                    ->setopp_sort_value($sortOrderValue)
                                    ->save();

                    Mage::dispatchEvent('orderpreparartion_after_dispatch_order', array('order' => $order, 'order_to_prepare_pending' => $OrderToPreparePending));
                    $debug .= ' (added to list ' . $opp_type . ') ';
                }
                else
                    $debug .= ' (order completely shipped) ';
            }
            else
                $debug .= ' (order belong to selected orders) ';
        }

        //mage::log($debug);
        return $debug;
    }

    /**
     * remove order from OrderToPreparePending (table containing fullstock & stockless orders)
     *
     * @param unknown_type $order
     */
    public function removeOrderFromOrderToPreparePending($order, $warehouseId = null) {

        //use collection because we can have the same order twice or more (depending of order items preparation warehouse)
        $orders = mage::getModel('Orderpreparation/ordertopreparepending')
                        ->getCollection()
                        ->addFieldToFilter('opp_order_id', $order->getId());
        if ($warehouseId)
            $orders->addFieldToFilter('opp_preparation_warehouse', $warehouseId);
        foreach ($orders as $order) {
            if ($order->getId())
                $order->delete();
        }
    }

    /**
     * Function to know if an order belong to selected orders
     *
     * @param unknown_type $order
     */
    public function orderBelongsToSelectedOrders($order, $warehouse) {
        $retour = true;
        $OrderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('preparation_warehouse', $warehouse->getId())
                        ->getFirstItem();
        $retour = ($OrderToPrepare->getId() > 0);
        return $retour;
    }

    /**
     * Return order items with colors according to their preparation state
     *
     * @param unknown_type $order
     */
    public function getRemainToShipForOrder($order, $warehouse) {
        $retour = '';
        $websiteId = $order->getStore()->getwebsite_id();
        //parcourt la liste des produits
        foreach ($order->getItemsCollection() as $item) {

            $remaining_qty = $item->getRemainToShipQty();
            $productId = $item->getproduct_id();
            $name = $item->getName();
            $name .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);

            if ($item->getpreparation_warehouse() == $warehouse->getId()) {
                if ($remaining_qty > 0) {

                    $productStockManagement = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                    if ($productStockManagement->getManageStock()) {
                        if ($item->getreserved_qty() >= $remaining_qty) {
                            $retour .= "<font color=\"green\">" . ((int) $remaining_qty) . 'x ' . $name . "</font>";
                        } else {
                            if (($item->getreserved_qty() < $remaining_qty) && ($item->getreserved_qty() > 0)) {
                                $retour .= "<font color=\"orange\">" . ((int) $remaining_qty) . 'x ' . $name . " (" . $item->getreserved_qty() . '/' . $remaining_qty . ")</font>";
                            } else {
                                $retour .= "<font color=\"red\">" . ((int) $remaining_qty) . 'x ' . $name . "</font>";
                            }
                        }
                        $retour .= "<br>";
                    }
                    else
                        $retour .= "<i>" . $name . "</i><br>";
                }
                else {
                    $retour .= "<s>" . ((int) $item->getqty_ordered()) . 'x ' . $name . "</s><br>";
                }
            } else {
                $retour .= "<font color=\"#bbbbbb\">" . ((int) $remaining_qty) . 'x ' . $name . "</font><br>";
            }
        }

        return $retour;
    }

}