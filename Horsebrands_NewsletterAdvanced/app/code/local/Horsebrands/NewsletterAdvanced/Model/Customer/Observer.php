<?php
/**
 * User: Vitali Fehler
 * Date: 02.07.13
 */
class Horsebrands_NewsletterAdvanced_Model_Customer_Observer extends Conlabz_CrConnect_Model_Customer_Observer
{
    public function check_subscription_status($observer)
    {
        $apiKey = trim(Mage::getStoreConfig('crroot/crconnect/api_key'));
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $newEmail = $customer->getEmail();
        $oldEmail = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
        $subscribed = $customer->getIsSubscribed();

        try
        {
            $client = new SoapClient(Mage::helper('crconnect')->getWsdl(), array("trace" => true));
        }
        catch(Exception $e)
        {
            Mage::log("CleverReach_CrConnect: Error connecting to CleverReach server: ".$e->getMessage());
        }

        // if subscribed is NULL (i.e. because the form didn't set it one way
        // or the other), get the existing value from the database
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        if($subscribed === NULL)
        {
            $subscribed = $subscriber->isSubscribed();
        }
        if($apiKey)
        {
            //ist kunde generell für newsletter angemeldet (erste checkbox)
            if($subscribed)
            {
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
                        $hashToken = hash('md5', $customer->getId());
                        $crReceiver = Mage::helper('crconnect')->prepareUserdata($customer, array('newsletter' => 1, 'hash_token' => $hashToken), true);
                        try
                        {
                            $return = $client->receiverAdd($apiKey, $listID, $crReceiver);
                            if($return->status=="SUCCESS"){
                                Mage::log("CleverReach_CrConnect: subscribed - ".$crReceiver["email"]." to List ".$listID, null, "cr_subscribe.log");
                            }
                            else
                            {
                                if($return->statuscode=="50")
                                {
                                    //try update
                                    $crReceiver["deactivated"] = 0;
                                    $return = $client->receiverUpdate($apiKey, $listID, $crReceiver);
                                    if($return->status=="SUCCESS")
                                    {
                                        Mage::log("CleverReach_CrConnect: resubscribed - ".$crReceiver["email"]." to List ".$listID, null, "cr_subscribe.log");
                                    }
                                }
                                else
                                {
                                    Mage::log("CleverReach_CrConnect: error - ".$return->message, null, "cr_subscribe.log");
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
            else
            {
                // Unsubscribe from all Newsletter Lists
                $newsletterTypes = Mage::getModel('newsletteradvanced/type')->getCollection();
                foreach($newsletterTypes as $newsletterType)
                {
                    $listID = $newsletterType->getListId();
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
    }
}
?>