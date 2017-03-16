<?php

class Horsebrands_Invitefriends_FrontController extends Mage_Core_Controller_Front_Action
{

  // public function testAction() {
  //   $invite = Mage::helper('invitefriends')->getInvite('dennis.niederrainer@gmail.com');
  //   Mage::helper('invitefriends/coupon')->processInvitingCoupon($invite, $invite->getInvitingCustomer());
  //   die('bums.');
  // }

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
