<?php

class Horsebrands_Giftcard_Model_Observer {

	public function processGiftCard($observer) {

    if($observer->getEvent()->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
      return;
    }

    $order = $observer->getEvent()->getOrder();
		$giftcardHelper = Mage::helper('giftcard');

    // see if gift card is in it
    $hasGiftCard = $giftcardHelper->hasGiftCardItemInOrder($order);
		if($hasGiftCard) {
			$giftcardItems = $giftcardHelper->getGiftCardItems($order);
			$allGiftcardItems = array();
			$orderItems = array();

			$i = 1;
			foreach($giftcardItems as $item) {
				if($item->getQtyOrdered() == 1) {
					$allGiftcardItems = $giftcardHelper->prepareGiftCard($allGiftcardItems, $order, $item, $i);
				} elseif ($item->getQtyOrdered() > 1) {
					for ($j=0; $j < $item->getQtyOrdered(); $j++) {
						$allGiftcardItems = $giftcardHelper->prepareGiftCard($allGiftcardItems, $order, $item, intval($i.$j));
					}
				}

				$orderItems[$item->getId()] = $item->getQtyOrdered();
				$i++;
			}

			if($giftcardHelper->sendGiftCardToReceiverBundle($allGiftcardItems, $order)) {
				$shipmentId = Mage::getModel('sales/order_shipment_api')->create(
					$order->getIncrementId(), $orderItems);
			}
		}
		// possibility to resend the coupon
	}
}
