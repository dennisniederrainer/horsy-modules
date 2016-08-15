<?php

class Horsebrands_Rewrites_Block_Checkout_Cart extends Mage_Checkout_Block_Cart {

  public function getItemsForStoreId($storeid) {
    $collection = $this->getQuote()->getItemsCollection();

    $items = array();
    foreach ($collection as $item) {
        if (!$item->isDeleted() && !$item->getParentItemId() && $item->getStoreId() == $storeid) {
            $items[] =  $item;
        }
    }
    return $items;
  }

  public function getTotalsInclTaxForStoreId($storeid) {
    $subtotalIncTax = 0;
    $items = $this->getItemsForStoreId($storeid);

    foreach ($items as $item) {
      $subtotalIncTax += $item->getRowTotalInclTax();
    }

    return $subtotalIncTax;
  }

}
