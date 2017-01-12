<?php

class Horsebrands_Giftcard_Model_Observer {

	public function processGiftCard($observer) {

    if($observer->getEvent()->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
      return;
    }

    $order = $observer->getEvent()->getOrder();
		$giftcardHelper = Mage::helper('giftcard');

    // see if gift card is in it
		$giftcardItems = $giftcardHelper->getGiftCardItems($order);
		if( $giftcardItems && count($giftcardItems) > 0 ) {
			$giftcardItemsBundle = array();
			$shippingitemsQuantity = array();

			$i = 1;
			foreach($giftcardItems as $item) {
				if($item->getQtyOrdered() == 1) {
					$giftcardItemsBundle = $giftcardHelper->prepareGiftCard($giftcardItemsBundle, $order, $item, $i);
				} elseif ($item->getQtyOrdered() > 1) {
					for ($j=0; $j < $item->getQtyOrdered(); $j++) {
						$giftcardItemsBundle = $giftcardHelper->prepareGiftCard($giftcardItemsBundle, $order, $item, intval($i.$j));
					}
				}

				$shippingitemsQuantity[$item->getId()] = $item->getQtyOrdered();
				$i++;
			}

			if($giftcardHelper->sendGiftCardToReceiverBundle($giftcardItemsBundle, $order)) {
				Mage::log('Ordernumber: ' . $order->getIncrementId() . ' -- Email sent.', null, 'GIFTCARDS-donot-delete.log');
				$shipmentId = Mage::getModel('sales/order_shipment_api')->create(
					$order->getIncrementId(), $shippingitemsQuantity);
			}
		}
	}
}
