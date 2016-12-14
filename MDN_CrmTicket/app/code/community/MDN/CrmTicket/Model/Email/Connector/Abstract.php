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
class MDN_CrmTicket_Model_Email_Connector_Abstract extends Mage_Core_Model_Abstract {

  const DEFAULT_MAX_NUMBER_MAIL_TO_DOWNLOAD = 10;
  const DEFAULT_MAX_MAIL_SIZE_TO_DOWNLOAD = 4; //4Mb by default


  //Copy of the MDN_CrmTicket_Model_EmailAccount
  //To KEEP the Email account Object ? -> NO because of the trim
  protected $_host; //mail box host
  protected $_port; //mail box port
  protected $_login; //mail box login
  protected $_pass; //mail box password
  protected $_protocol; //mail box protocol to use : POP, IMAP ...
  protected $_ssl; //SSL or TLS
  protected $_storeid; //keepped to link to customer
  protected $_accountDescription; //usually login@host
  protected $_conn; // Zend_Mail_Storage_Imap or Zend_Mail_Storage_Pop3 ...
  protected $_msgs; //Array of unique Id
  protected $_msgCount; // nb of message in the inbox during this connection
  protected $_maxNumberOfMailToDownload; // limit of mail to download for each getNewMessages()
  protected $_debugStringConn;
  protected $_mailsIds = array(); //array of mails unique Id and distant Id

  /**
   * manage connection to a descendant of Zend_Mail_Storage_Abstract
   *
   */
  public function connect() {

    throw new Exception('Connect must be implemented for this specific Mail connectivity ');
  }


  public function fillEmailAccountConfig($emailAccount) {

    $status = false;

    if ($emailAccount) {
      $this->_host = trim($emailAccount->getcea_host());
      $this->_port = trim($emailAccount->getcea_port());
      $this->_login = trim($emailAccount->getcea_login());
      $this->_pass = trim($emailAccount->getcea_password());
      $this->_ssl = $emailAccount->getcea_use_ssl();
      $this->_protocol = $emailAccount->getcea_connection_type();
      $this->_storeid = $emailAccount->getcea_store_id();

      $maxNumberOfMailToDownload = Mage::getStoreConfig('crmticket/pop/max_msg_download');
      $this->_maxNumberOfMailToDownload = ($maxNumberOfMailToDownload > 0) ? $maxNumberOfMailToDownload : self::DEFAULT_MAX_NUMBER_MAIL_TO_DOWNLOAD;

      $this->_debugStringConn = "[" . $this->_protocol . "] on [" . $this->_host . "]:[" . $this->_port . "] ssl:[" . $this->_ssl . "] l:[" . $this->_login . "] p:[" . $this->_pass . "]";
      $status = true;
    }

    return $status;
  }

  /**
   * Disconnect from the current connection
   *
   * @return boolean
   */
  public function disconnect() {

    $debug = array();
    $status = false;

    $debug[] = "<br/>DISCONNECTING from " . $this->_host;
    try {
      if ($this->_conn) {
        $this->_conn->close();
      }
      $debug[] = "<br/>DISCONNECTED from " . $this->_host;
      $status = true;
    } catch (Exception $ex) {
      $debug[] = "<br/>DISCONNECT Exception: " . $ex;
    }
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
    return $status;
  }

  /**
   * Do a simple connection test to the current account
   */
  public function testCredentialsValidity() {

    if ($this->checkCredentialsValidity()) {
      if ($this->connect()) {
        $this->disconnect();
        return Mage::helper('CrmTicket')->__('Your credentials are valid');
      }
    } else {
      throw new Exception(Mage::helper('CrmTicket')->__('Your credentials are not valid'));
    }
  }

  /**
   * Check if mails credentials are valids
   */
  protected function checkCredentialsValidity() {

    $credentialAreValid = false;

    if ($this->_host != null && strlen($this->_host) > 0
            && $this->_port != null && strlen($this->_port) > 0 & is_numeric($this->_port)
            && $this->_login != null && strlen($this->_login) > 0
            && $this->_pass != null && strlen($this->_pass) > 0) {

      //now credentials are ok so we create _accountDescription
      //by default the accountdescription is the login which is very often login@host.com
      $this->_accountDescription = $this->_login;
      //login can contain host
      if (!strpos($this->_login, '@')) {
        $this->_accountDescription = $this->_login . '@' . $this->_host;
      }

      $credentialAreValid = true;
    }

    return $credentialAreValid;
  }

  /**
   *
   * Check in DB is the message was previously imported
   * 
   * @param type $accountDescription
   * @param type $msgId
   */
  protected function isMsgAllreadyImported($msgId) {
    $debug = array();

    //todo : call this once and compare ids via an array
    $item = Mage::getModel('CrmTicket/Email')
            ->getCollection()
            ->addFieldToFilter('ctm_msg_id', $msgId)
            ->addFieldToFilter('ctm_account', $this->_accountDescription);

    $nbrows = $item->getSize();

    //Nominal case -> should be 1
    $status = true;
    if ($nbrows == 1) {
      $debug[] = "Message id=$msgId was allready imported <br>";
    } else if ($nbrows > 1) {
      $debug[] = "Database pb !!! Message id=$msgId ( nbrow =$nbrows) : INCONSISTANT <br>";
    } else { //=0 was not allready imported
      $debug[] = "Message id=$msgId is READY to be imported !<br>";
      $status = false;
    }
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
    return $status;
  }

  /**
   *
   * Get an Array of the Ids previously imported from DB is the message was previously imported
   *
   * @param type $accountDescription
   * @param type $msgId
   */
  protected function getMsgsAllreadyImported() {
    $debug = array();

    /*$emails = Mage::getModel('CrmTicket/Email')
            ->getCollection()
            ->addFieldToFilter('ctm_account', $this->_accountDescription);

    $ids = array();
    foreach($emails as $email){
      $ids[] = $email->getctm_msg_id();
    }*/

    $prefix = Mage::getConfig()->getTablePrefix();
    $sql = "SELECT ctm_msg_id FROM ".$prefix."crm_ticket_mail WHERE ctm_account='".$this->_accountDescription."' ORDER BY ctm_id DESC";
    $result = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
    $ids = array();

    foreach($result as $key =>$value){
      $ids[] = $value["ctm_msg_id"];
    }
    
    Mage::helper('CrmTicket')->log(implode("\n", $debug));
    return $ids;
  }

  /**
   * Define a custom limit of email to download
   * 
   * @param type $limit
   */
  public function limitNumberOfMailToDownload($limit) {
    if (isset($limit) && is_numeric($limit) && $limit > 0) {
      $this->_maxNumberOfMailToDownload = $limit;
    }
  }

  public function getMaxEmailSize(){
    $maxEmailSize = self::DEFAULT_MAX_MAIL_SIZE_TO_DOWNLOAD;

    $maxEmailSizeByConf = Mage::getStoreConfig('crmticket/pop/max_email_size');
    if($maxEmailSizeByConf && is_numeric($maxEmailSizeByConf) && $maxEmailSizeByConf>0){
        $maxEmailSize = $maxEmailSizeByConf;
    }

    return ($maxEmailSize * 1024 * 1024); // 4Mb by default
  }

  /**
   * Get new messages from the current mailBox & Process only new messages selected
   *
   * @param type $conn  
   */
  protected function getNewMessages($returnMessage = true) {
    $debug = array();
    $helper = Mage::helper('CrmTicket');

    $importLog = 'getNewMessages at '.date('Y-m-d H:i:s')."\n";
    
    $this->_msgCount = $this->_conn->countMessages();    
    $result = $this->_msgCount . Mage::helper('CrmTicket')->__(' mails in the inbox of ') . $this->_accountDescription . '<br/>';


    $mailDownloadedCounter = 0;
    $mailSkippedCounter = 0;
    $currentDistantMsgNumber = 0;
    $firstEmailId = 0;
    $lastEmailId = 0;

    //$beginCheckDBTS = microtime(true);
    if ($this->_msgCount > 0) {

      $firstEmailId=$this->_msgCount;

      $this->_mailsIds = array(); //re init
      //get the Last Messages ID

      $messagesAllreadyImported = $this->getMsgsAllreadyImported();
      
      for ($currentDistantMsgNumber = $this->_msgCount; $currentDistantMsgNumber >= 1; $currentDistantMsgNumber--) {
        
        //Limit the number of downlaoded messages
        if ($mailDownloadedCounter >= $this->_maxNumberOfMailToDownload) {
          break;
        }

        $msgUniqueId = $this->_conn->getUniqueId($currentDistantMsgNumber);
        
        //save here the list of Messages to Download 
        if(!in_array($msgUniqueId, $messagesAllreadyImported)){ //FASTER
          $this->_mailsIds[$msgUniqueId] = $currentDistantMsgNumber;
          $mailDownloadedCounter++;
        }else{
          $mailSkippedCounter++;
        }
      }

      $lastEmailId=$currentDistantMsgNumber;
    }

    $importedMail = 0;

    $deleteMessageOption = Mage::getStoreConfig('crmticket/pop/delete_email_aftersave');

    $tooBigMailInFileSystem = Mage::getStoreConfig('crmticket/pop/save_too_bigmail_in_filesystem');

    $maxMessageSize = $this->getMaxEmailSize(); 
   
    if ($mailDownloadedCounter > 0) {
      foreach ($this->_mailsIds as $msgUniqueId => $distantMsgNumber) {

        //$beginDownloadMailTS = microtime(true);
        $rawHeader = $this->_conn->getRawHeader($distantMsgNumber);
        $maildate = $this->getEmailDate($rawHeader);

        //if we can't get a date from the mail, use the current date by default
        if(!$maildate){
          $maildate = date('Y-m-d H:i:s');
        }else{
          $maildate = date('Y-m-d H:i:s',$maildate);
        }

        $importLog .= 'Importing '.$msgUniqueId."\n";
        $importLog .= 'MAIL DATE IS = '.$maildate."\n";

        if($this->importEmailAfterDate($rawHeader) !== false){
            $importLog .= 'IMPORT BEGIN'."\n";
            //Save the Raw Email a first time because the parsing can crash for any reason in the next step
            $newEmail = Mage::getModel('CrmTicket/Email');
            $newEmail->setctm_msg_id($msgUniqueId); //save the unique Id to enable isMsgAllreadyImported() to be safe
            $newEmail->setctm_account($this->_accountDescription); //save the account email to enable isMsgAllreadyImported() to be safe

            //SAVE EMAIL HEADER IN ALL CASE BECAUSE SIZE IS ACCEPTABLE
            $newEmail->setctm_rawheader($rawHeader); //download and Save RawHeader in latin 1

            //FOR THE CONTENT WE CHOOSE TO SAVE IT
            $RAW_CONTENT = $this->_conn->getRawContent($distantMsgNumber);
            $newEmail->setctm_rawcontent($RAW_CONTENT); //download and Save RawContent in latin 1

            //prevent to import too big emails            
            $currentMessageSize = strlen($RAW_CONTENT);
            $importLog .= "Raw message uid=$msgUniqueId / id=$distantMsgNumber Size=$currentMessageSize Bytes - MAX ALLOWED is '.$maxMessageSize.' Bytes<br/>";

            if($currentMessageSize > $maxMessageSize){

                if($tooBigMailInFileSystem){
                    $fileUrl = Mage::helper('CrmTicket/Mail')->saveMailContent($this->_accountDescription,$msgUniqueId,$RAW_CONTENT);
                    $newEmail->setctm_rawcontent($fileUrl);
                    $newEmail->setctm_isfilecontent(1);
                    $importLog .= "Raw message uid=$msgUniqueId / id=$distantMsgNumber SAVED IN FILESYSTEM at $fileUrl <br/>";
                }else{
                    //just block them to avoid import crash due to
                    $importLog .= "Raw message uid=$msgUniqueId / id=$distantMsgNumber NOT SAVED BECAUSE TOO BIG Size='.$currentMessageSize.' Bytes<br/>";
                    continue;
                }
            }
        

            $newEmail->setctm_date($maildate);
            $newEmail->setctm_status(MDN_CrmTicket_Model_Email::STATUS_NEW);
           

            $newEmail->save();
            $newId = $newEmail->getId();
            if($newId){
              $importLog .= 'IMPORT SUCESSFULL'."\n";
              $importedMail++;
              $debug[] = "Raw message uid=$msgUniqueId / id=$distantMsgNumber saved<br/>";

              if($deleteMessageOption){
                if($this->deleteEmailOnServerAfterLocalSave($distantMsgNumber)){
                  $debug[] = "Mail deleted from server for Uniqueid=$msgUniqueId distantMsgNumber=$distantMsgNumber InternalId=".$newId;
                }
              }
            }else{
                $importLog .= 'IMPORT FAILED'."\n";
            }
        }else{
            $importLog .= 'IMPORT SKIPPED BY DATE'."\n";
        }
      }
    }

    $result .= $helper->__('%s emails imported', $importedMail);
    $debug[] = '<br/>' . $result;
    $helper->log(implode("\n", $debug));

    mage::log($importLog, null, 'crm_ticket_email_import.log');

    if(!$returnMessage)
        $result = $importedMail;

    return $result;
  }



  /**
   * Delete email on server after saving it locally
   */
  private function deleteEmailOnServerAfterLocalSave($msgUniqueId) {

    $status = true;

    try {
      //this function does not returns any status
      //so we can consider it's ok except if an exception is raised, especially in imap
      $this->_conn->removeMessage($msgUniqueId);
      
    } catch (Exception $ex) {
      //ignored
      $status = false;
    }
    return $status;
  }

 /**
  * Extract date from Header mail 
  * 
  * @param type $rawHeader
  * @return boolean
  */
  private function getEmailDate($rawHeader){

    $mailTimeStamp = 0;

    try
    {
      $mailDateText = '';

      $fromHeaderkey = 'Date: ';
      $toLF = chr(10);
      $toCR = chr(13);
      $toGMTPlus = ' +'; //ex +1000(CET)
      $toGMTLess = ' -'; //ex -8000(CET)

      $stringHelper = Mage::helper('CrmTicket/String');
      $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toLF));
      
      if (strlen($extractedHeaderContent) == 0) {
        $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toCR));
      }
      if (strlen($extractedHeaderContent) == 0) {
        $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toGMTPlus));
      }
      if (strlen($extractedHeaderContent) == 0) {
        $extractedHeaderContent = trim($stringHelper->extractTextBetweenFlags($rawHeader, $fromHeaderkey, $toGMTLess));
      }
      if (strlen($extractedHeaderContent) > 0) {
        $mailDateText = $extractedHeaderContent;
      }

      if($mailDateText){
        $mailTimeStamp = strtotime($mailDateText);
      }

    } catch(Exception $ex){
      //ignore
    }

    //return the mail's TimeStamp to import mail if  : no setting defined OR if date is valid OR if date is not parsable
    return $mailTimeStamp;
  }

  /**
   * limit message import from a certain date
   */
  private function importEmailAfterDate($rawHeader){

    $mailTimeStamp = 0;

    try 
    {     
      //1) extract date
      $mailTimeStamp = $this->getEmailDate($rawHeader);

      //Compare date with config (if config is defined)      
      if($mailTimeStamp){
          $confDate = Mage::getStoreConfig('crmticket/pop/date_limit');
          if($confDate){
            $confTimeStamp = strtotime($confDate);
            if($confTimeStamp){
              if($mailTimeStamp < $confTimeStamp){
                return false;
              }
            }
          }
      }
    } catch(Exception $ex){
        //ignore
    }

    //return the mail's TimeStamp to import mail if  : no setting defined OR if date is valid OR if date is not parsable
    return $mailTimeStamp;
  }


  /**
   * Entry point
   *
   * retrieveMailBox  connect to the specified mailbox and try to get and parse messages
   * Connect to a Mail account and launch the function to process and import messages to ticketing tool
   */
  public function retrieveMailBox($displayResult = true) {

    $result = false;

    if ($this->checkCredentialsValidity() && $this->connect()) {
      $result = $this->getNewMessages($displayResult);
      $this->disconnect();
    }

    return $result;
  }

}

