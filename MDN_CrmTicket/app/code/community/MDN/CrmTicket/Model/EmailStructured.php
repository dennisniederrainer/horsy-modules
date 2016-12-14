<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_EmailStructured extends Mage_Core_Model_Abstract {

  //Parsed Email Structure
  public $response;
  public $responseContentType;
  public $attachements = array();
  public $attachementsType = array();
  public $fromEmail;
  public $fromName;
  public $to;
  public $tos = array();
  public $ccs= array();
  public $toName;
  public $subject;

  public $spam;

  /**
   * A mail is valid if he get a fromEmail + a to
   *
   * @return boolean
   */
  public function isValid() {
    $status = false;
    if (strlen($this->fromEmail) > 0 && strlen($this->to) > 0) {
      $status = true;
    }
    return $status;
  }

  /**
   * Give the info from parsing if this mil was flagguer as Spam
   * @return type
   */
  public function isSpam() {   
    return $this->spam;
  }

  /**
   * Rules to flag a mail as spam
   */
  public function identifyAsSpam($identifiedAsSpam) {

    if ($this->fromEmail) {

      
      $helper = Mage::helper('CrmTicket/String');
      $spamModel = Mage::getModel('CrmTicket/EmailSpam');

      //email
      $emailToCheck = trim(str_replace(' ','',$this->fromEmail));

      //Domain
      $domainToCheck = $helper->getDomainFromEmail($emailToCheck);
      $domainToCheck = $helper->prepareFieldforComparison($domainToCheck, true);

      $log = "\n\n".'Begin spam detection for email='.$emailToCheck.' domain='.$domainToCheck;

      //------------------------------------------------------
      //FIRST : force as spam if the email has been identified as spam by mail provider (flag X-Spam-Tag)
      if($identifiedAsSpam){
        $this->spam = true;
        $log .= "\n"."marked as SPAM because of flag X-Spam-Tag in the mail recived from teh mail carrier";
      }else{
        $this->spam = false;
      }

      //------------------------------------------------------
      //THEN FORCE ALLOW MAILS

      //1) NOT A SPAM if is part of Allowed emails
      if ($this->spam) {
        $allowedEmails = $spamModel->getAllowedEmails();
        if(count($allowedEmails)>0){
            $log .= "\n".'allowedEmails='.$helper->getVarDumpInString($allowedEmails);

            foreach ($allowedEmails as $allowedEmail) {
              if ($emailToCheck === $helper->prepareFieldforComparison($allowedEmail, true)){
                $this->spam = false;
                $log .= "\n"."marked as NOT SPAM because email $emailToCheck is ALLOWED : email ".$domainToCheck;
                break;
              }
            }
        }
      }
      
      //2) NOT A SPAM  it from is part of Allowed domains
      if ($this->spam && $domainToCheck) {
        $allowedDomains = $spamModel->getAllowedDomains();
        if(count($allowedDomains)>0){
            $log .= "\n".'allowedDomains='.$helper->getVarDumpInString($allowedDomains);
            
            foreach ($allowedDomains as $allowedDomain) {
              if ($domainToCheck === $helper->prepareFieldforComparison($allowedDomain, true)){
                $this->spam = false;
                $log .= "\n"."marked as NOT SPAM because domain $allowedDomain is ALLOWED : domain ".$domainToCheck;
                break;
              }
            }
        }
      }

      //------------------------------------------------------
      //THEN FORCE EXCLUDE MAILS

      //3) FLAGGUED AS SPAM if is  part of Forbidden Emails
      if (!$this->spam) {
        $forbiddenEmails = $spamModel->getForbiddenEmails();
        if(count($forbiddenEmails)>0){
            $log .= "\n".'forbiddenEmails='.$helper->getVarDumpInString($forbiddenEmails);

            foreach ($forbiddenEmails as $forbiddenEmail) {
              if ($emailToCheck === $helper->prepareFieldforComparison($forbiddenEmail,true)){
                $this->spam = true;
                $log .= "\n"."marked as SPAM because email $forbiddenEmail is forbidden : mail from ".$emailToCheck;
                break;
              }
            }
        }
      }

      //4) FLAGGUED AS SPAM it from is part of Forbidden domains
      if (!$this->spam && $domainToCheck) {
        $forbiddenDomains = $spamModel->getForbiddenDomains();
        if(count($forbiddenDomains)>0){
            $log .= "\n".'forbiddenDomains='.$helper->getVarDumpInString($forbiddenDomains);

            foreach ($forbiddenDomains as $forbiddenDomain) {
              if ($domainToCheck === $helper->prepareFieldforComparison($forbiddenDomain,true)){
                $this->spam = true;
                $log .= "\n"."marked as SPAM because domain $forbiddenDomain is forbidden : mail domain ".$domainToCheck;
                break;
              }
            }
        }
      }

      //5) FLAGGUED AS SPAM it the subject contains forbidden keywords
      if (!$this->spam) {
        $keywordSpamList = $spamModel->getKeywordSpamList();
        if(count($keywordSpamList)>0){
            $log .= "\n".'keywordSpamList='.$helper->getVarDumpInString($keywordSpamList);            
            foreach ($keywordSpamList as $keywordSpam) {                
                if($keywordSpam && strlen($keywordSpam) > 0){
                    $log .= "\n"."looking for : ".$helper->partenize($keywordSpam). ' Subject '.strtolower($this->subject);
                    if(preg_match($helper->partenize($keywordSpam), strtolower($this->subject))){
                      $this->spam = true;
                      $log .= "\n"."marked as spam because subject contains : ".$keywordSpam. ' Subject '.$this->subject;
                      break;
                    }
                }
            }
        }
      }

      $log .= "\n".'End spam detection for email='.$emailToCheck.' domain='.$domainToCheck.' RESULT='.(($this->spam)?'YES':'NO');

      mage::log($log, null, 'crm_ticket_spam_detection.log');
    }
  }




  public function reInitEmailStructured() {
    $this->fromEmail = null;
    $this->fromName = null;
    $this->toName = null;
    $this->to = null;
    $this->tos = array();
    $this->ccs = array();
    $this->spam = null;   
    $this->response = null;
    $this->responseContentType = null;
    $this->attachements = array();    
    $this->attachementsType = array();
    $this->subject = null;
  }

    


}