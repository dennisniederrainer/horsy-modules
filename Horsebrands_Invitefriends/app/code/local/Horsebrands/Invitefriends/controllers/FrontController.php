<?php

class Horsebrands_Invitefriends_FrontController extends Mage_Core_Controller_Front_Action
{

  public function testAction() {
    $customer = Mage::getModel('customer/customer')->load(47302);
    $invite = Mage::helper('invitefriends')->getInvite($customer->getEmail());

    if($invite
        && $invite->getStatus() == Horsebrands_Invitefriends_Model_Invite::STATUS_NEW) {

      Mage::helper('invitefriends/coupon')->processInviteeCoupon($invite, $customer);

      die('proceed');
      // send email to invitee
      Mage::getSingleton('core/session')->addSuccess('Dein Freunde-Einladen-Gutschein wurde erfolgreich deinem Kundenkonto zugeordnet.');
    }

  }

  public function linkAction()
  {
    $customerid = $this->getRequest()->getParam('key');
    $customer = Mage::getModel('customer/customer')->load($customerid);

    if($customer->getId()) {
      $this->loadLayout();
      $this->_initLayoutMessages('customer/session');
      Mage::getSingleton('core/session')->setInvitationCustomerId($customer->getId());
      $this->getLayout()->getBlock('invitefriends_register')->setData('customer', $customer);
      $this->renderLayout();
    } else {
      Mage::getSingleton('core/session')->addError('Invalid Invitation-Key');
      return $this->_redirect('/');
    }

  }
}
