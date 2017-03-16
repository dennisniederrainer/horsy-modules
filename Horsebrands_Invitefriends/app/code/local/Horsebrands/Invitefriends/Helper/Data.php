<?php

class Horsebrands_Invitefriends_Helper_Data extends Mage_Core_Helper_Abstract {

  public function isAlreadyRegistered($email) {
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    $customer->loadByEmail($email);

    return ($customer->getId());
  }

  public function isAlreadyInvited($email) {
    $collection = Mage::getModel('invitefriends/invite')->getCollection()
          ->addFieldToFilter('invitee_email', $email);

    return (count($collection) > 0);
  }

  public function getInvite($email) {
    $collection = Mage::getModel('invitefriends/invite')->getCollection()
          ->addFieldToFilter('invitee_email', $email);

    return (count($collection) > 0 ? $collection->getFirstItem() : false);
  }

  public function createInvite($inviteCustomerId, $invitee) {

    if(!$inviteCustomerId || $inviteCustomerId == 0) {
      Mage::log($invitee->getEmail() . ' tried to register via invite. No inviteCustomerId', null, 'invite_error.log');
      return;
    }

    if($invite = $this->isAlreadyInvited($invitee->getEmail())) {
      Mage::log($invitee->getEmail() . ' already invited. InviteId: ' . $invite->getInviteId(), null, 'invite_error.log');
      return;
    }

    $invite = Mage::getModel('invitefriends/invite')
                ->setCustomerEmail($customer->getEmail())
                ->setInviteeEmail($invitee->getEmail())
                ->setStatus(Horsebrands_Invitefriends_Model_Invite::STATUS_NEW);

    $invite->save();
  }

  public function sendInviteEmail($email, $customer) {
    $templateId = Mage::getStoreConfig('customer/invitefriends/invitefriends_invite_email');
    $sender = Array('name' => Mage::getStoreConfig('trans_email/ident_support/name'),
                    'email' => Mage::getStoreConfig('trans_email/ident_support/email'));

    $inviteLink = Mage::getUrl('invitefriends/front/link', array('key' => $customer->getId()));
    $vars = Array('customer_firstname' => $customer->getFirstname(),
              'invite_link' => $inviteLink
            );

    $storeId = Mage::app()->getStore()->getId();
    $translate = Mage::getSingleton('core/translate');

    Mage::getModel('core/email_template')
      ->sendTransactional($templateId, $sender, $email, null, $vars, $storeId);
    $translate->setTranslateInline(true);
  }
}
