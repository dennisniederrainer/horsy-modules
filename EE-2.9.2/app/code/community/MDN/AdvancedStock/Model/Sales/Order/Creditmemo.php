<?php

class MDN_AdvancedStock_Model_Sales_Order_Creditmemo extends Mage_Sales_Model_Order_Creditmemo {

    /**
     * Dispatch order and update planning
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        $order = $this->getOrder();

        //plan update for ordered and reserved qty for products
        foreach ($this->getAllItems() as $item) {
            $productId = $item->getProductId();

            //unreserve products
            $orderItem = $item->getOrderItem();
            $oldReservedQty = $orderItem->getreserved_qty();
            $newReservedQty = $oldReservedQty - $item->getqty();
            if ($newReservedQty > 0)
                $newReservedQty = 0;
            $orderItem->getErpOrderItem()->setreserved_qty($newReservedQty)->save();

            //plan stock updates
            mage::helper('BackgroundTask')->AddTask('Update stock for product ' . $productId . ' (from credit memo aftersave)',
                    'AdvancedStock/Product_Base',
                    'updateStocksFromProductId',
                    $productId
            );
        }

        //dispatch event to allow other extension to catch creditmemo after save event
        Mage::dispatchEvent('advancedstock_creditmemo_aftersave', array('creditmemo' => $this));
    }

}