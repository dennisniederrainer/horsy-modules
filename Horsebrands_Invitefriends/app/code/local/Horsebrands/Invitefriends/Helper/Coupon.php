<?php

class Horsebrands_Invitefriends_Helper_Coupon extends Mage_Core_Helper_Abstract {

  public function validateCouponcode($code, $customer) {
    $coupon = Mage::getModel('salesrule/coupon')->load($code, 'code');

    if($coupon->getInviteId() == null) {
      // if no inviteid is set, it is not an invitefriends coupon
      return true;
    }
    if($coupon->getCustomerId() != $customer->getId()) {
      return 'nocustomer';
    }

    // TODO is expired check!!
    if($coupon->getCustomerId() != $customer->getId()) {
      return 'nocustomer';
    }

    return true;
  }

  public function getCustomerCoupons($customer) {
    if(!$customer || !$customer->getId()) {
      return null;
    }

    $coupons = Mage::getModel('salesrule/coupon')->getCollection()
                  ->addFieldToFilter('times_used', 0)
                  ->addFieldToFilter('customer_id', $customer->getId());

    return $coupons;
  }

  public function processInviteeCoupon($invite, $customer) {
    $this->processCoupon($invite,
      $customer,
      Horsebrands_Invitefriends_Model_Invite::INVITE_TYPE_INVITED,
      Horsebrands_Invitefriends_Model_Invite::STATUS_REGISTERED);
  }

  public function processInvitingCoupon($invite, $customer) {
    $this->processCoupon($invite,
      $customer,
      Horsebrands_Invitefriends_Model_Invite::INVITE_TYPE_INVITING,
      Horsebrands_Invitefriends_Model_Invite::STATUS_CLOSED);
  }

  protected function processCoupon($invite, $customer, $inviteType, $inviteStatus) {
    $couponCode = $this->generateCouponcode();
    $expirationDate = time() + (30 * 24 * 3600);
    $expirationDate = date('Y-m-d', $expirationDate);

    $coupon = Mage::getModel('salesrule/coupon');
    // TODO RULE ID AUS CONFIG
    $coupon->setRuleId(Mage::getStoreConfig('customer/invitefriends/invitefriends_rule_id'))
            ->setCode($couponCode)
            ->setUsageLimit(1)
            ->setUsagePerCustomer(1)
            ->setExpirationDate($expirationDate)
            ->setType(1)
            ->setCustomerId($customer->getId())
            ->setInviteId($invite->getInviteId())
            ->setInviteType($inviteType)
            ->save();

    // update invite
    if($inviteType == Horsebrands_Invitefriends_Model_Invite::INVITE_TYPE_INVITING) {
      $invite->setCustomerCoupon($couponCode);
    } else {
      $invite->setInviteeCoupon($couponCode);
    }
    $invite->setStatus($inviteStatus);
    $invite->save();
  }

  protected function generateCouponcode() {
    return Mage::helper('core')->getRandomString(10);
  }

}
