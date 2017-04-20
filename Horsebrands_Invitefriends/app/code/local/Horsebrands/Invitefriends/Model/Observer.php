<?php

class Horsebrands_Invitefriends_Model_Observer {

  public function processInvitation($observer) {
    mage::log('dong',null,'ding.log');
    return; 
    $customer = $observer->getEvent()->getCustomer();
    $invite = Mage::helper('invitefriends')->getInvite($customer->getEmail());

    if($invite
        && $invite->getStatus() == Horsebrands_Invitefriends_Model_Invite::STATUS_NEW) {

      Mage::helper('invitefriends/coupon')->processInviteeCoupon($invite, $customer);

      // send email to invitee
      Mage::getSingleton('core/session')->addSuccess('Dein Freunde-Einladen-Gutschein wurde erfolgreich deinem Kundenkonto zugeordnet.');
    }
  }

  public function processInviteeOrder($observer) {
    $order = $observer->getEvent()->getOrder();

    if($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING){
      if($code = $order->getCouponCode()) {
        $coupon = Mage::getModel('salesrule/coupon')->load($code, 'code');
        if($coupon->getInviteType() == Horsebrands_Invitefriends_Model_Invite::INVITE_TYPE_INVITED) {
          $invite = $coupon->getInvite();

          if($invite->getStatus() != Horsebrands_Invitefriends_Model_Invite::STATUS_CLOSED) {
            Mage::helper('invitefriends/coupon')->processInvitingCoupon($invite, $invite->getInvitingCustomer());

            // sendmail
            Mage::log('INVITING CUSTOMER got a coupon!', null, 'fratz.log');
          }
        }
      }
    }
  }
}
