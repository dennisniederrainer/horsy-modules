<?php

class Horsebrands_ProductReturn_Helper_CreateCreditmemo extends MDN_ProductReturn_Helper_CreateCreditmemo {

    /*
	Rewrite CreateCreditmemo (ggf. Email an Jochen, dass neuer Refund?)

	plus extra Methode, die als Action ausgeführt werden kann, wenn Refund complete.
	Diese Methode setzt CreditMemo auf refunded (register()) und sendet Email an Kunden
	*/

	/**
     * Rewrite of MDN_ProductReturn's createInvoiceCreditmemo
     * Does not register() the creditmemo, so it won't get the status 'refunded'
     * instead the Method sets the status 'pending'.
     *
     * @param      $invoiceId
     * @param      $rmaData
     * @param bool $isOnline
     *
     * @return
     * @internal param \unknown_type $object
     */
    protected function createInvoiceCreditmemo($invoiceId, $rmaData, $object, $isOnline = false)
    {
        $invoice = mage::getModel('sales/order_invoice')->load($invoiceId);

        if (!$invoice->getId()) {
            Mage::throwException($this->__('Unable to load sales invoice (%s)', $invoiceId));
        }

        $order = $invoice->getOrder();

        if (!$order->canCreditmemo()) {
            Mage::throwException($this->__('Can not do credit memo for order'));
        }

        //init credit memo
        $convertor        = Mage::getModel('sales/convert_order');
        $creditmemo       = $convertor->toCreditmemo($order)->setInvoice($invoice);
        $adjustmentRefund = 0;

        // add items
        if (isset($rmaData['items'])) {
            foreach ($rmaData['items'] as $itemData) {
                $orderItem = $itemData['item'];
                $orderItem  = $order->getItemById($orderItem->getitem_id());
                $qty       = $itemData['qty'];

                if ($qty == 0) {
                    if ($orderItem->isDummy()) {
                        if ($orderItem->getParentItem() && ($qty > 0)) {
                            /*
                             * this part of code will never run, bacaouse of conditions: $qty==0 && $qty > 0 !!!
                             */
                            $parentItemNewQty  = $this->getQtyForOrderItemId($object, $orderItem->getParentItem()->getId());
                            $parentItemOrigQty = $orderItem->getParentItem()->getQtyOrdered();
                            $itemOrigQty       = $orderItem->getQtyOrdered() / $parentItemOrigQty;
                            $qty               = $itemOrigQty * $parentItemNewQty;
                        }
                    }
                }
                $item = $convertor->itemToCreditmemoItem($orderItem);
                $item->setQty($qty);

                //customize price if partial refund
                $price = $this->getPriceForOrderItemId($object, $orderItem->getId());
                if ($price > 0) {
                    $adjustmentRefund += $item->getPrice() - $price;
                }


                $creditmemo->addItem($item);
            }
        }

        $rmaData['refund'] += $adjustmentRefund;

        //refund shipping fees
        if (isset($rmaData['refund_shipping_amount'])) {
            if ($order->getshipping_tax_amount() > 0)
                $shippingTaxCoef = $order->getshipping_amount() / $order->getshipping_tax_amount();
            else
                $shippingTaxCoef = 0;
            $refundAmountInclTax = $rmaData['refund_shipping_amount'];
            if ($shippingTaxCoef > 0 )
                $refundAmountTax = $refundAmountInclTax / $shippingTaxCoef;
            else
                $refundAmountTax = 0;
            $refundAmountExclTax = $refundAmountInclTax - $refundAmountTax;

            Mage::log('soll: '.$refundAmountInclTax, null, 'createcreditmemo.log');

            $creditmemo->setShippingAmount($refundAmountInclTax);
            $creditmemo->setBaseShippingAmount($refundAmountInclTax);
        } else {
            $creditmemo->setBaseShippingAmount(0.00);
        }


        //manage adjustement
        $creditmemo->setAdjustmentPositive($rmaData['fee']);
        $creditmemo->setAdjustmentNegative($rmaData['refund']);


        $creditmemo->setRefundRequested(true);
        // $creditmemo->setOfflineRequested(!$isOnline);
        $creditmemo->setOfflineRequested(true);

        $creditmemo->collectTotals();
        $creditmemo->register();
        $creditmemo->setPendingState();

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder())
            ->addObject($creditmemo->getInvoice());
        $transactionSave->save();


        return $creditmemo;
    }

    /**
     * Rewrite of MDN_ProductReturn's afterCreateCreditmemo
     * Does not notify the customer, as long the Creditmemo is not really refunded (amount transfered)
     *
     * @param      $creditmemo
     * @param      $object
     *
     * @return
     */
    public function afterCreateCreditmemo($creditmemo, $object)
    {
        $rma = mage::getModel('ProductReturn/Rma')->load($object['rma_id']);

        //store creditmemo creation in history
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_creditmemo/view', array('creditmemo_id' => $creditmemo->getId(), 'key' => '[key]'));
        $rma->addHistoryRma('<a href="' . $url . '">' . mage::helper('ProductReturn')->__('Credit memo #%s created', $creditmemo->getincrement_id()) . '</a>');

        //store credit memo in rma/products
        foreach ($object['products'] as $product) {
            if ((isset($product['rp_id'])) && ($product['rp_id'] != null)) {
                //update information

                /**
                 * datas setrp_object_id && setrp_associated_object can be wrong storaged,
                 * because 1 rma product can be splitted into more then 1 creditmemos
                 */

                $rmaProduct = mage::getModel('ProductReturn/RmaProducts')->load($product['rp_id']);
                $rmaProduct->setrp_action_processed(1)
                    ->setrp_associated_object(mage::helper('ProductReturn')->__('Credit memo #%s', $creditmemo->getincrement_id()))
                    ->setrp_object_type('creditmemo')
                    ->setrp_object_id($creditmemo->getId())
                    ->setrp_action('refund')
                    ->setrp_destination($product['destination'])
                    ->save();
            }

        }

        $comment = mage::helper('ProductReturn')->__('Created for Product return #%s', $rma->getrma_ref());
        $creditmemo->addComment($comment, false);
        $creditmemo->save();
    }
}
