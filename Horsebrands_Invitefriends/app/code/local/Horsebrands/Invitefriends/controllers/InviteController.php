<?php

class Horsebrands_Invitefriends_InviteController extends Mage_Core_Controller_Front_Action {

  public function preDispatch()
  {
      parent::preDispatch();
      if (!Mage::getSingleton('customer/session')->authenticate($this)) {
          $this->setFlag('', 'no-dispatch', true);
      }
  }

  public function indexAction()
  {
    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');
    $this->renderLayout();
  }

  public function invitePostAction()
  {
    if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
      return $this->_redirect('*/*');
    }

    if (!$this->_validateFormKey()) {
      return $this->_redirect('*/*');
    }

    // get POSTDATA
    $emails = $this->getRequest()->getPost('email');

    $customer = Mage::getSingleton('customer/session')->getCustomer();
    $helper = Mage::helper('invitefriends');

    // check if emails not already registered
    foreach ($emails as $email) {

      if($email == "") continue;

      if($helper->isAlreadyRegistered($email)) {
        Mage::getSingleton('customer/session')->addError($email . ' is already registered.');
      } elseif($helper->isAlreadyInvited($email)) {
        Mage::getSingleton('customer/session')->addError($email . ' is already invited.');
      } else {
        try {
          $invite = Mage::getModel('invitefriends/invite');

          $invite->setCustomerEmail($customer->getEmail());
          $invite->setInviteeEmail($email);
          $invite->setStatus(Horsebrands_Invitefriends_Model_Invite::STATUS_NEW);
          $invite->save();

          $helper->sendInviteEmail($email, $customer);

          Mage::getSingleton('customer/session')->addSuccess('Eine Einladung an ' .$email. ' wurde erfolgreich versendet.');

        } catch (Exception $e) {
          Mage::getSingleton('customer/session')->addError('Während der Einladung von ' .$email. ' ist ein Fehler aufgetreten.');
        }
      }
    }

    return $this->_redirect('invitefriends/invite');
  }
}
