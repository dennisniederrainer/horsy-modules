<?php

class Horsebrands_Invitefriends_Model_Invite extends Mage_Core_Model_Abstract {

  const STATUS_NEW = 0;
  const STATUS_REGISTERED = 10;
  const STATUS_CLOSED = 100;

  const INVITE_TYPE_INVITED = 10;
  const INVITE_TYPE_INVITING = 20;

  public function _construct() {
    $this->_init('invitefriends/invite');
  }

  public function getInvitingCustomer() {
    return
      Mage::getModel('customer/customer')
        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
        ->loadByEmail($this->getCustomerEmail());
  }

  public function save() {
    $this->setUpdatedAt(Mage::getModel('core/date')->timestamp(time()));
    return parent::save();
  }
}
