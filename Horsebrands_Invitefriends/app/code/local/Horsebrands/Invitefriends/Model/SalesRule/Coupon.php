<?php

class Horsebrands_Invitefriends_Model_SalesRule_Coupon extends Mage_SalesRule_Model_Coupon {

  public function getCustomer() {
    if($customerid = $this->getCustomerId()) {
      return Mage::getModel('customer/customer')->load($customerid);
    }

    return null;
  }

  public function getInvite() {
    if($inviteid = $this->getInviteId()) {
      return Mage::getModel('invitefriends/invite')->load($inviteid);
    }

    return null;
  }
}
