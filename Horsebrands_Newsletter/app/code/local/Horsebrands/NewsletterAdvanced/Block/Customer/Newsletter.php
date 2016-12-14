<?php

class Horsebrands_NewsletterAdvanced_Block_Customer_Newsletter extends Mage_Customer_Block_Newsletter {

  protected $newsletterTypes;

  public function __construct() {
      $this->newsletterTypes  = Mage::getModel('newsletteradvanced/type')->getCollection();
      parent::__construct();
  }

  public function getCustomer() {
      return Mage::getSingleton('customer/session')->getCustomer();
  }

  public function getNewsletterTypes() {
      return $this->newsletterTypes;
  }

  public function isSubscribedToType($newsletterType) {
      $subscriber = $this->getSubscriptionObject();
      $typeSubscriberCollection =
        Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
          ->addFieldToFilter('type_id', $newsletterType->getId())
          ->addFieldToFilter('subscriber_id', $subscriber->getId())
          ->load();

      if($typeSubscriberCollection->getSize() > 0) {
          return true;
      } else {
          return false;
      }
  }
}
