<?php
include "Mage/Newsletter/controllers/ManageController.php";

class Horsebrands_NewsletterAdvanced_ManageController extends Mage_Newsletter_ManageController
{
    protected $client = null;

    public function initCleverReachApi()
    {
        $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));
        $this->client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
    }

    public function indexAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));

        if($apiKey && $customer && $subscriber){
            try
            {
                $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
            }
            catch(Exception $e)
            {
                Mage::log("CleverReach_CrConnect: Error connecting to Server: ".$e->getMessage());
            }

            $collection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
            $collection->addFieldToFilter('subscriber_id', $subscriber->getId());

            $subscriberNewsletterTypeIDs = array();
            foreach($collection as $typeSubscriber)
            {
                $subscriberNewsletterTypeIDs[] = $typeSubscriber->getTypeId();
            }

            $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
            foreach($newsletterTypes as $newsletterType)
            {
                $listID = $newsletterType->getListId();
                $groupDetails = $client->groupGetDetails($apiKey, $listID);
                if($groupDetails->status == "SUCCESS")
                {
                    $groupAttributes = $groupDetails->data->attributes;
                    // Prüfe, ob hash_tocken attribute existiert. Relevant für automatische Anmeldung aus dem Newsletter
                    if(!isset($groupAttributes->hash_token))
                    {
                        $client->groupAttributeAdd($apiKey, $listID, "hash token", "text", "");
                    }
                }
                // Der Kunde ist zu diesem Newsletter Typ angemeldet. Melde ihn bei CR an.
                if(in_array($newsletterType->getId(), $subscriberNewsletterTypeIDs))
                {
                    $return = $client->receiverGetByEmail($apiKey, $listID, $customer->getEmail());
                    try
                    {
                        if($return->status=="SUCCESS"){
                            if(!$return->data->active)
                            {
                                $client->receiverSetActive($apiKey, $listID, $customer->getEmail());
                                Mage::log("CleverReach_CrConnect: subscribed - ".$customer->getEmail()." to List ".$listID, null, "cr_subscribe.log");
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_subscribe.log");
                    }
                }
                else
                {
                    try
                    {
                        $return = $client->receiverSetInactive($apiKey, $listID, $customer->getEmail());
                        if($return->status=="SUCCESS")
                        {
                            Mage::log("CleverReach_CrConnect: unsubscribed - ".$customer->getEmail(), null, "cr_unsubscribe.log");
                        }
                        else
                        {
                            //call failed
                            Mage::log("CleverReach_CrConnect: error - ".$return->message, null, "cr_unsubscribe.log");
                        }
                    }
                    catch(Exception $e)
                    {
                        Mage::log("CleverReach_CrConnect: Error in SOAP call: ".$e->getMessage(), null, "cr_unsubscribe");
                    }
                }
            }
        }

        parent::indexAction();
    }

    public function saveAction()
    {
        if($this->getRequest()->getParam('isAjax') == 1) {
            return $this->saveAjaxAction();
        } else {
            if (!$this->_validateFormKey()) {
                Mage::getSingleton('customer/session')->addError('Übertragungsschlüssel stimmt nicht überein. Bitte erneut versuchen.');
                return $this->_redirect('newsletter/manage/');
            }
            
            try {
                //load POST parameter
                $is_subscribed = $this->getRequest()->getParam('is_subscribed', false);
                $newsletterType = $this->getRequest()->getParam('newsletter_type', null);

                //if newslettertype is 0, is_subscribed becomes false because user checked the unsubscribed radiobutton
                if (is_null($newsletterType) || $newsletterType == 0) {
                    $is_subscribed = false;
                //else if a newslettertype is set, the customer becomes subscribes status TRUE
                } else if(!is_null($newsletterType)) {
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

                    $subscriberType = Mage::getModel('newsletteradvanced/typesubscriber');
                    $subscriberType->setTypeId($newsletterType)
                            ->setSubscriberId($subscriber->getId())
                            ->save();

                    Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));

                //if customer wants to unsibscribe, the typesubscriber entries will be reseted
                } else {
                    $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
                    $typeSubscriberCollection->addFieldToFilter('subscriber_id', $subscriber->getId())->load();
                    foreach($typeSubscriberCollection as $typeSubscriber) {
                        $typeSubscriber->delete();
                    }

                    Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('customer/session')->addError($e->getMessage());
                Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
            }
            
            $this->syncHobraCleverreachAction($customer, $subscriber);
        }
        
        $this->_redirect('newsletter/manage/');
    }

    protected function saveAjaxAction() {
        $response = array();
        $messagetext = "";

        if (!$this->_validateFormKey()) {
            $response['success'] = false;
            $messagetext = 'Übertragungsschlüssel stimmt nicht überein. Bitte erneut versuchen.';
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }
        
        try {
            //load POST parameter
            $is_subscribed = $this->getRequest()->getParam('is_subscribed', false);
            $newsletterType = $this->getRequest()->getParam('newsletter_type', null);

            //if newslettertype is 0, is_subscribed becomes false because user checked the unsubscribed radiobutton
            if (is_null($newsletterType) || $newsletterType == 0) {
                $is_subscribed = false;
            //else if a newslettertype is set, the customer becomes subscribes status TRUE
            } else if(!is_null($newsletterType)) {
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

                $subscriberType = Mage::getModel('newsletteradvanced/typesubscriber');
                $subscriberType->setTypeId($newsletterType)
                        ->setSubscriberId($subscriber->getId())
                        ->save();

                $response['success'] = true;
                $messagetext = $this->__('The subscription has been saved.');

            //if customer wants to unsibscribe, the typesubscriber entries will be reseted
            } else {
                $typeSubscriberCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection();
                $typeSubscriberCollection->addFieldToFilter('subscriber_id', $subscriber->getId())->load();
                foreach($typeSubscriberCollection as $typeSubscriber) {
                    $typeSubscriber->delete();
                }

                $response['success'] = true;
                $messagetext = $this->__('The subscription has been removed.');
            }
        }
        catch (Exception $e) {
            // Mage::getSingleton('customer/session')->addError($e->getMessage());
            // Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
            $response['success'] = false;
            $messagetext = $this->__('An error occurred while saving your subscription.');
        }
        
        $this->syncHobraCleverreachAction($customer, $subscriber);

        if($response['success']) {
            $response['messageblock'] = Mage::helper('hello')->getAjaxSuccessMessageBlock($messagetext);
        } else {
            $response['messageblock'] = Mage::helper('hello')->getAjaxErrorMessageBlock($messagetext);
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }

    protected function syncHobraCleverreachAction($customer, $subscriber) {
        $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));

        if($apiKey && $customer && $subscriber) {
            try {
                $client = Mage::helper('crconnect')->getSoapClient();
            } catch(Exception $e) {
                Mage::log("Horsebrands_NewsletterAdvanced_ManageController: Error connecting to Server: ".$e->getMessage(), null, 'horsy_newsletter.log');
                return;
            }

            $newslettertypeCollection = Mage::getModel('newsletteradvanced/typesubscriber')->getCollection()
                ->addFieldToFilter('subscriber_id', $subscriber->getId());

            $subscriberNewsletterTypeIDs = array();
            foreach($newslettertypeCollection as $typeSubscriber) {
                $subscriberNewsletterTypeIDs[] = $typeSubscriber->getTypeId();
            }

            $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
            foreach($newsletterTypes as $newsletterType) {
                
                $listID = $newsletterType->getListId();
                
                try {
                    $groupDetails = $client->groupGetDetails($apiKey, $listID);
                    if($groupDetails->status == "SUCCESS")
                    {
                        $groupAttributes = $groupDetails->data->attributes;
                        // Prüfe, ob hash_tocken attribute existiert. Relevant für automatische Anmeldung aus dem Newsletter
                        if(!isset($groupAttributes->hash_token))
                        {
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
                        if($return->status=="SUCCESS"){
                            if(!$return->data->active)
                            {
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