<?php

class Horsebrands_NewsletterAdvanced_Block_Adminhtml_Customer_Edit_Tab_Newsletter extends Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter {

    protected $subscriber;
    protected $subscriberTypes;

    public function __construct() {
      parent::__construct();

      $customer = Mage::registry('current_customer');
      $this->subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
      $this->subscriberTypes = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
      $this->subscriberTypes->addFieldToFilter('subscriber_id', $this->subscriber->getId())->load();
    }

    public function initForm() {
      parent::initForm();

      $form = $this->getForm();
      $fieldset = $form->addFieldset('newslettertypes_fieldset', array('legend'=>Mage::helper('customer')->__('Newsletter Listen')));

      $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
      foreach($newsletterTypes as $key => $newsletterType) {
        $fieldset->addField('newsletterType_'.$key, 'checkbox',
          array(
            'label' => $newsletterType->getTypeName(),
            'class' => "newsletterType",
            'name'  => 'newsletterTypes[]',
            'value' => $newsletterType->getId()
          )
        );

        $subscribedToType = $this->checkSubscriptionToNewsletterType($newsletterType);
        $isSubscribed = false;
        if($subscribedToType) {
          $isSubscribed = true;
          $form->getElement('newsletterType_'.$key)->setIsChecked(true);
        }

        if($isSubscribed) {
          $form->getElement('subscription')->setIsChecked(true);
        }
      }

      return $this;
    }

    private function checkSubscriptionToNewsletterType($newsletterType)
    {
        $subscribed = false;

        foreach($this->subscriberTypes as $subscriberType)
        {
            if($subscriberType->getTypeId() == $newsletterType->getId())
            {
                $subscribed = true;
                break;
            }
        }

        return $subscribed;
    }
}
