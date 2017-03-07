<?php

class Horsebrands_NewsletterAdvanced_Helper_Data extends Conlabz_CrConnect_Helper_Data {

  public function getSubscriberTypeIds($subscriberId) {
    $listids = array();

    $collection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
                    ->addFieldToFilter('subscriber_id', $subscriberId)->load();
    foreach($collection as $typeSubscriber) {
      array_push($listids, $typeSubscriber->getTypeId());
    }

    return (count($listids) > 0 ? $listids : false);
  }

  public function getTypesubscriber($subscriberId, $typeId) {
    return Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
                ->addFieldToFilter('subscriber_id', $subscriberId)
                ->addFieldToFilter('type_id', $typeId)
                ->getFirstItem();
  }

  public function syncSubscriberCleverreach() {
    $customer = Mage::getSingleton('customer/session')->getCustomer();
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
    $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));
    $client = null;

    $subscriberTypeIds = $this->getSubscriberTypeIds($subscriber->getId());

    try {
      $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
    } catch(Exception $e) {
      echo 'client init error: ' . $e->getMessage();
      return;
    }

    $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
    foreach($newsletterTypes as $newslettertype) {
      $listId = $newslettertype->getListId();

      // get customer by email
      $result = $client->receiverGetByEmail($apiKey, $listId, $customer->getEmail(), 0);
      if($result->status == "ERROR") {
        if($subscriberTypeIds && in_array($newslettertype->getId(), $subscriberTypeIds)) {
          $typesubscriber = $this->getTypesubscriber($subscriber->getId(), $newslettertype->getId());
          if($typesubscriber) $typesubscriber->delete();
        }
      } elseif($result->status == "SUCCESS") {
        if($result->data->active) {
          if(!$subscriberTypeIds || !in_array($newslettertype->getId(), $subscriberTypeIds)) {
            Mage::getModel('newsletteradvanced/typesubscriber')
            ->setSubscriberId($subscriber->getId())
            ->setTypeId($newslettertype->getId())
            ->save();
          }
        } else {
          if($subscriberTypeIds && in_array($newslettertype->getId(), $subscriberTypeIds)) {
            $typesubscriber = $this->getTypesubscriber($subscriber->getId(), $newslettertype->getId());
            if($typesubscriber) $typesubscriber->delete();
          }
        }
      }
    }
  }

  public function syncCustomerWithCleverreach($customer) {
    $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));
    $email = $customer->getEmail();
    $subscribed = $customer->getIsSubscribed();

    try {
      $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
    } catch(Exception $e) {
      Mage::log("CleverReach_CrConnect: Error connecting to CleverReach server: ".$e->getMessage());
    }

    // if subscribed is NULL (i.e. because the form didn't set it one way
    // or the other), get the existing value from the database
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
    if(!$subscriber) {
      $subscribed = false;
    }
    if($subscribed === NULL) {
      $subscribed = $subscriber->isSubscribed();
    }

    if($apiKey) {
        //ist kunde generell für newsletter angemeldet (erste checkbox)
      if($subscribed) {
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

            // Prüfe, ob hash_token attribute existiert. Relevant für automatische Anmeldung aus dem Newsletter
            if(!isset($groupAttributes->hash_token)) {
              $client->groupAttributeAdd($apiKey, $listID, "hash token", "text", "");
            }
          }

          // Der Kunde ist zu diesem Newsletter Typ angemeldet. Melde ihn bei CR an.
          if(in_array($newsletterType->getId(), $subscriberNewsletterTypeIDs)) {
            $hashToken = hash('md5', $customer->getId());
            $crReceiver = Mage::helper('crconnect')->prepareUserdata($customer, array('newsletter' => 1, 'hash_token' => $hashToken), true);
            $crReceiver["deactivated"] = 0;
            try {
              $return = $client->receiverAdd($apiKey, $listID, $crReceiver);
              if($return->status=="SUCCESS") {
                Mage::log("CleverReach_CrConnect: subscribed - ".$crReceiver["email"]." to List ".$listID, null, "cr_subscribe.log");
              } else {
                if($return->statuscode=="50") {
                  //try update
                  $crReceiver["deactivated"] = 0;
                  $return = $client->receiverUpdate($apiKey, $listID, $crReceiver);
                  if($return->status=="SUCCESS") {
                    Mage::log("CleverReach_CrConnect: resubscribed - ".$crReceiver["email"]." to List ".$listID, null, "cr_subscribe.log");
                  }
                } else {
                  Mage::log("CleverReach_CrConnect: error - ".$return->message, null, "cr_subscribe.log");
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
      } else {
        // Unsubscribe from all Newsletter Lists
        $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
        foreach($newsletterTypes as $newsletterType) {
          $listID = $newsletterType->getListId();
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
  }
}
