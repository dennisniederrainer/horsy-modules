<?php

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'CustomerController.php');

class Horsebrands_NewsletterAdvanced_Adminhtml_CustomerController extends Mage_Adminhtml_CustomerController {

  public function saveAction() {
    parent::saveAction();

    $customer = Mage::registry('current_customer');
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);

    $data = $this->getRequest()->getPost();
    if(Mage::getSingleton('admin/session')->isAllowed('customer/newsletter')) {
      if(isset($data['subscription'])) {
        $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
        $typeSubscriberCollection->addFieldToFilter('subscriber_id', $subscriber->getId())->load();
        foreach($typeSubscriberCollection as $typeSubscriber) {
            $typeSubscriber->delete();
        }

        $newsletterTypes = $data['newsletterTypes'];
        foreach($newsletterTypes as $newsletterType) {
          $subscriberType = Mage::getModel('newsletteradvanced/typesubscriber');
          $subscriberType->setTypeId($newsletterType)
              ->setSubscriberId($subscriber->getId())
              ->save();
        }

        $subscriber->setIsSubscribed(true)->save();
      }
      else {
        $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
        $typeSubscriberCollection->addFieldToFilter('subscriber_id', $subscriber->getId())->load();
        foreach($typeSubscriberCollection as $typeSubscriber) {
          $typeSubscriber->delete();
        }
      }

      Mage::helper('newsletteradvanced')->syncCustomerWithCleverreach($customer);
    }
  }

}
