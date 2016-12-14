<?php

require_once(Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php');

class Horsebrands_NewsletterAdvanced_ManageController extends Mage_Newsletter_ManageController {

    protected $client = null;

    public function initCleverReachApi() {
      $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));
      $this->client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
    }

    public function indexAction() {
      parent::indexAction();
      return;

      $customer = Mage::getSingleton('customer/session')->getCustomer();
      $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
      $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));

      if($apiKey && $customer && $subscriber) {

        try {
          $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
        } catch(Exception $e) {
          Mage::log("CleverReach_CrConnect: Error connecting to Server: ".$e->getMessage());
        }

        $collection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
        $collection->addFieldToFilter('subscriber_id', $subscriber->getId());

        $subscriberNewsletterTypeIDs = array();
        foreach($collection as $typeSubscriber) {
          $subscriberNewsletterTypeIDs[] = $typeSubscriber->getTypeId();
        }

        $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
        foreach($newsletterTypes as $newsletterType) {
          $listID = $newsletterType->getListId();
          $groupDetails = $client->groupGetDetails($apiKey, $listID);
          if($groupDetails->status == "SUCCESS") {
            $groupAttributes = $groupDetails->data->attributes;
            // Prüfe, ob hash_tocken attribute existiert. Relevant für automatische Anmeldung aus dem Newsletter
            if(!isset($groupAttributes->hash_token)) {
              $client->groupAttributeAdd($apiKey, $listID, "hash token", "text", "");
            }
          }

          // Der Kunde ist zu diesem Newsletter Typ angemeldet. Melde ihn bei CR an.
          if(in_array($newsletterType->getId(), $subscriberNewsletterTypeIDs)) {
            $return = $client->receiverGetByEmail($apiKey, $listID, $customer->getEmail());

            try {
              if($return->status=="SUCCESS") {
                if(!$return->data->active) {
                  $client->receiverSetActive($apiKey, $listID, $customer->getEmail());
                  Mage::log("CleverReach_CrConnect: subscribed - ".$customer->getEmail()." to List ".$listID, null, "cr_subscribe.log");
                }
              }
            } catch(Exception $e) {
              Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_subscribe.log");
            }
          } else {
            try {
              $return = $client->receiverSetInactive($apiKey, $listID, $customer->getEmail());

              if($return->status=="SUCCESS") {
                Mage::log("CleverReach_CrConnect: unsubscribed - ".$customer->getEmail(), null, "cr_unsubscribe.log");
              } else {
                //call failed
                Mage::log("CleverReach_CrConnect: error - ".$return->message, null, "cr_unsubscribe.log");
              }
            } catch(Exception $e) {
              Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_unsubscribe");
            }
          }
        }
      }

      parent::indexAction();
    }

    public function saveAction() {
      parent::saveAction();
      return;

      if (!$this->_validateFormKey()) {
        Mage::getSingleton('customer/session')->addError('Formkey ist abgelaufen. Bitte versuche es erneut.');
        return $this->_redirect('newsletter/manage/');
      }

      try {
        //load POST parameter
        $is_subscribed = $this->getRequest()->getParam('is_subscribed', false);
        $newsletterTypes = $this->getRequest()->getParam('newsletter_types', null);

        //if newslettertype is 0, is_subscribed becomes false because user checked the unsubscribed radiobutton
        if(!$newsletterTypes || count($newsletterTypes) == 0) {
          $is_subscribed = false;
        //else if a newslettertype is set, the customer becomes subscribes status TRUE
        } else if($newsletterTypes && count($newsletterTypes) > 0) {
          $is_subscribed = true;
        }

        //load customer from session
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer->setStoreId(Mage::app()->getStore()->getId())
            ->setIsSubscribed((boolean)$is_subscribed)
            ->save();

        //load subscriber object
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        //if subscriber will become subscribed, delete all/reset typesubscriber settings and set up the new type
        if ((boolean)$is_subscribed) {
          $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
          $typeSubscriberCollection->addFieldToFilter('subscriber_id', $subscriber->getId())->load();
          foreach($typeSubscriberCollection as $typeSubscriber) {
              $typeSubscriber->delete();
          }

          foreach ($newsletterTypes as $nlType) {
            $subscriberType = Mage::getModel('newsletteradvanced/typesubscriber');
            $subscriberType->setTypeId($nlType)
            ->setSubscriberId($subscriber->getId())
            ->save();
          }

          Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));

        //if customer wants to unsibscribe, the typesubscriber entries will be reseted
        } else {
            $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
                                          ->addFieldToFilter('subscriber_id', $subscriber->getId())->load();

            foreach($typeSubscriberCollection as $typeSubscriber) {
                $typeSubscriber->delete();
            }

            Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
        }
      } catch (Exception $e) {
          Mage::getSingleton('customer/session')->addError($e->getMessage());
          Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
      }

      $this->syncHobraCleverreachAction($customer, $subscriber);
      $this->_redirect('newsletter/manage/');
    }

    protected function syncHobraCleverreachAction($customer, $subscriber) {
      $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));

      if($apiKey && $customer && $subscriber) {
        try {
            $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));//Mage::helper('crconnect')->getSoapClient();
        } catch(Exception $e) {
            Mage::log("Horsebrands_NewsletterAdvanced_ManageController: Error connecting to Server: ".$e->getMessage(), null, 'horsy_newsletter.log');
            return;
        }

        $newslettertypeCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
            ->addFieldToFilter('subscriber_id', $subscriber->getId())->load();

        $subscriberNewsletterTypeIDs = array();
        foreach($newslettertypeCollection as $typeSubscriber) {
            $subscriberNewsletterTypeIDs[] = $typeSubscriber->getTypeId();
        }

        $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
        foreach($newsletterTypes as $newsletterType) {
          $listID = $newsletterType->getListId();

          try {
            $groupDetails = $client->groupGetDetails($apiKey, $listID);
            if($groupDetails->status == "SUCCESS") {
              $groupAttributes = $groupDetails->data->attributes;
              // Prüfe, ob hash_tocken attribute existiert. Relevant für automatische Anmeldung aus dem Newsletter
              if(!isset($groupAttributes->hash_token)) {
                $client->groupAttributeAdd($apiKey, $listID, "hash token", "text", "");
              }
            }
          } catch (Exception $e) {
            Mage::log("Horsebrands_NewsletterAdvanced_ManageController: Fehler bei Group Details (Hashtoken): ".$e->getMessage(), null, 'horsy_newsletter.log');
          }

          // Der Kunde ist zu diesem Newsletter Typ angemeldet. Melde ihn bei CR an.
          if(in_array($newsletterType->getId(), $subscriberNewsletterTypeIDs)) {
            $return = $client->receiverGetByEmail($apiKey, $listID, $customer->getEmail());

            try {
              if($return->status=="SUCCESS") {
                if(!$return->data->active) {
                  $client->receiverSetActive($apiKey, $listID, $customer->getEmail());
                  Mage::log("CleverReach_CrConnect: subscribed - ".$customer->getEmail()." to List ".$listID, null, "cr_subscribe.log");
                }
              }
            } catch(Exception $e) {
              Mage::log("ManageController: Error in SOAP call: ".$e->getMessage(), null, 'horsy_newsletter.log');
              Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_subscribe.log");
            }
          } else {
            try {
              $return = $client->receiverSetInactive($apiKey, $listID, $customer->getEmail());
              if($return->status=="SUCCESS") {
                Mage::log("CleverReach_CrConnect: unsubscribed - ".$customer->getEmail(), null, "cr_unsubscribe.log");
              } else {
                //call failed
                Mage::log("ManageController: Fehler beim Inaktiv setzen: ".$e->getMessage(), null, 'horsy_newsletter.log');
                Mage::log("CleverReach_CrConnect: error - ".$return->message, null, "cr_unsubscribe.log");
              }
            }
            catch(Exception $e) {
              Mage::log("ManageController: Error in SOAP call: ".$e->getMessage(), null, 'horsy_newsletter.log');
              Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_unsubscribe");
            }
          }
        }
      }
    }
}
?>
