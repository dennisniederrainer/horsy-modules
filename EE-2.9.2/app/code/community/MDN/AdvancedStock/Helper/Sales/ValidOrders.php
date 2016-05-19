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
class MDN_AdvancedStock_Helper_Sales_ValidOrders extends Mage_Core_Helper_Abstract {

    /**
     * Add conditions to an order collection
     *
     * @param unknown_type $collection
     * @return unknown
     */
    public function addConditionToCollection($collection) {
        $collection->addFieldToFilter('is_valid', 1);

        return $collection;
    }

    /**
     * Check if an order is valid
     *
     * @param unknown_type $order
     * @return unknown
     */
    public function orderIsValid($order) {
        if ($order->getis_valid() == 1)
            return true;
        else
            return false;
    }

    /**
     * Update is_valid value for sales order
     *
     * @param unknown_type $order
     */
    public function updateIsValid(&$order, $save = false) {
        $isValid = true;
        $debug = '';
        $continue = true;

        //check customer group
        if ($customerId = $order->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customerGroup = $customer->getgroup_id();
            $forcedCustomerGroups = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/force_customer_group'));
            if (in_array($customerGroup, $forcedCustomerGroups))
                $continue = false;
        }


        //check shipping method
        if ($continue)
        {
            $shippingMethod = $order->getshipping_method();
            $forcedShippingMethods = explode(',', Mage::getStoreConfig('advancedstock/valid_orders/force_shipping_method'));
            if (in_array($shippingMethod, $forcedShippingMethods))
                    $continue = false;
        }
        
        //check payment
        if ($continue && mage::getStoreConfig('advancedstock/valid_orders/require_payment_validated')) {
            if ($order->getpayment_validated() == 0) {
                $isValid = false;
                $debug .= 'Order is not valid because payment is not validated';
            }
        }

        //check status
        $excludeStatuses = mage::getStoreConfig('advancedstock/valid_orders/exclude_status');
        if ($continue && ($excludeStatuses != '')) {
            $orderStatus = $order->getStatus();
            $t = explode(',', $excludeStatuses);
            if (in_array($orderStatus, $t)) {
                $isValid = false;
                $debug .= 'Order is not valid because status is excluded';
            }
        }

        //update value
        if ($isValid) {
            $order->setis_valid(1);
        } else {
            $order->setis_valid(0);
        }

        //save
        if ($save)
            $order->save();
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $orderId
     */
    public function UpdateIsValidWithSave($orderId) {
        $order = mage::getModel('sales/order')->load($orderId);
        $this->updateIsValid($order, true);
    }

}