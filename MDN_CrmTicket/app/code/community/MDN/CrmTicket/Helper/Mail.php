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
 * @copyright  Copyright (c) 2015 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.3
 */
class MDN_CrmTicket_Helper_Mail extends Mage_Core_Helper_Abstract {

    private static $_MEDIA_FOLDER_NAME = 'media';
    private static $_EXTENSION_FOLDER_NAME = 'CrmTicket';
    private static $_RAWMAILS_FOLDER_NAME = 'RawMails';
    private static $_RAWMAIL_FILENAME = 'rawcontent.mail';

    protected $_baseRawmailFolder;

    /**
     * Save the email rawContent (not the header) into a file in
     *
     * /media/CrmTicket/Rawmail/{date.today}/{email.account.login}/{rawcontent.mail}
     *
     * The file path is saved into the field ctm_rawcontent in the table crm_ticket_mail
     *
     * Depending of the options
     *  - System -> configuration -> crmticket -> pop -> max_email_size
     * if the rawcontent is bigger than thsi size, the email is saved in file system,, insatead of being saved directly into the field ctm_rawcontent in the table crm_ticket_mail
     *
     *  - System -> configuration -> crmticket -> pop -> save_too_bigmail_in_filesystem
     * that enable/disable this feature
     *
     * @param type $emailaccount
     * @param type $mailUniqueId
     * @param type $rawContent
     * @return type
     */
    public function saveMailContent($emailaccount,$mailUniqueId,$rawContent){

        $finalFileUrl = "";

        $folderToSave = $this->getRawMailContentDirectory($emailaccount,$mailUniqueId);

        if($folderToSave){
            $finalFileUrl = $this->saveRawmail($folderToSave,$rawContent);
        }

        if(!$finalFileUrl){
            $currentMessageSize = strlen($rawContent);
            $message = 'Crm : Too Big mail skipped for '.$emailaccount.' -> saveMailContent for ID ='.$mailUniqueId.' mails was '.($currentMessageSize/(1024*1024)).'Mb';
            mage::log($message, null, 'crm_ticket_too_big_mail.log');
        }

        return $finalFileUrl;
    }


    /**
     * Write the mail content in the file system
     *
     * @param type $folderToSave
     * @param type $rawContent
     * @return boolean
     */
    private function saveRawmail($folderToSave,$rawContent){
        $saved = false;

        $finalFileUrl = $folderToSave.self::$_RAWMAIL_FILENAME;

        try {

            file_put_contents($finalFileUrl, $rawContent);
            $saved = $finalFileUrl;

        }catch(Exception $ex){
            Mage::logException($ex);
            $saved = false;
        }

        return $saved;
    }

    /**
     * Return rowMail directory
     *
     * @return type
     * TODO :  put in conf the folder names + saved + rename folder if change ?
     *
     * //ex of mailid = 1421348662.10621.mail122.ha.ovh.net_S_9742048
     * //ex of final path : /media/CrmTicket/Rawmail/20150112/customer.service@mycompagny.com/1421348662.10621.mail122.ha.ovh.net_S_9742048/rawcontent.mail
     *
     */
    private function getRawMailContentDirectory($emailaccount,$mailId) {

        $folder = null;

        $emailAccountUrl = $this->getTodayBaseRawMailContentDirectory().$this->cleanNameForFolderCreation($emailaccount).DS;

        if($this->createDirIfNotExists($emailAccountUrl)){

           $mailUrl = $emailAccountUrl.$this->cleanNameForFolderCreation($mailId).DS;

           if($this->createDirIfNotExists($mailUrl)){
                $folder = $mailUrl;
           }
        }

        return ($folder)?$folder:null;
    }

    /**
     * Return base rawmail main directory for the current day
     *
     * @return String
     */
    private function getBaseRawMailContentDirectory() {

        //media/CrmTicket/Rawmail
        $dir = Mage::getBaseDir(self::$_MEDIA_FOLDER_NAME) . DS . self::$_EXTENSION_FOLDER_NAME . DS . self::$_RAWMAILS_FOLDER_NAME;
        $this->createDirIfNotExists($dir);
        return $dir;
    }

    /**
     * Return base rawmail main directory for the current day
     *
     * @return String
     */
    private function getTodayBaseRawMailContentDirectory() {

        if (!$this->_baseRawmailFolder) {

            //media/CrmTicket/Rawmail
            $dir = $this->getBaseRawMailContentDirectory();

            //ex : media/CrmTicket/Rawmail/20140101/
            $dir = $dir.DS.date('Ymd').DS;
            $this->createDirIfNotExists($dir);

            $this->_baseRawmailFolder = $dir;
        }

        return $this->_baseRawmailFolder;
    }

    /**
     * Create a folder if not exist
     *
     * @param type $dir
     * @return boolean
     */
    private function createDirIfNotExists($dir){
        $dirCreated = false;

        try{

            if (!is_dir($dir)){
                  $dirCreated = mkdir($dir);
            }else{
               $dirCreated = true;
            }

        }catch(Exception $ex){
            $dirCreated = false;
        }

        return $dirCreated;
    }

    /**
     * Prevent crash in saving the mail content if the directory name include special characters
     *
     * @param type $name
     * @return type
     */
    private function cleanNameForFolderCreation($name){

        $excludedChar = array ('/','>','<','|',':','&',' ','@',',','=','-','#','$','%');

        foreach ($excludedChar as $char){
            $name = str_replace($char, '_', $name);
        }

        return trim($name);
    }

    public function deleteFileAndFolder($filepath) {

        $deleted = false;

        //prevent to delete some inconsistant path
        if(!(strpos($filepath,$this->getBaseRawMailContentDirectory()) !== false)){
            return $deleted;
        }

        try{
            if (file_exists($filepath)) {
                if (unlink($filepath)) {
                    $deleted = true;

                    //delete also parent folder
                    $folderPath = str_replace(self::$_RAWMAIL_FILENAME, '', $filepath);
                    if(strpos($filepath,$this->getBaseRawMailContentDirectory()) !== false){
                        if(is_dir($folderPath)){
                            rmdir($folderPath);
                        }
                    }
                }
            }
        }
        catch(Exception $ex)
        {
            Mage::logException($ex);
        }
        return $deleted;
    }

     /**
     * Clean old mail downlaoded in database and in file system
     * @param type $debug
     * @return int
     */
    public function cleanOldEmails(){

       $debug = array();
       $debug[] = 'Begin CRON cleanOldEmails at '.date('Y-m-d h.i.s');

       $emailDeleted = 0;
       try{
       $nbmonth = Mage::getStoreConfig('crmticket/pop/delete_mail_month');

       if(is_numeric($nbmonth) && $nbmonth>0){

            if($nbmonth>11){
              $nbmonth = 11; //limit to avoid a stupid processing
            }

            $collection = Mage::getModel('CrmTicket/Email')->getCollection();

            $emailCount = $collection->getSize();
            $debug[] = $emailCount.' emails saved in database';

            if($emailCount>0){

              $limitTimeStamp = mktime(0, 0, 0, date("m")-$nbmonth, date("d"),   date("Y"));

              foreach ($collection as $mail) {
                $emailDate = $mail->getctm_date();
                if($emailDate){
                  $emailTimeStamp = strtotime($emailDate);
                  if($emailTimeStamp < $limitTimeStamp){
                    $debug[] = 'Delete email Id='.$mail->getId().' with date ='.$emailDate;
                    $mail->delete(); //the file will be deleted in before Delete
                    $emailDeleted++;
                  }
                }
              }
            }
          }else{
            $debug[] = 'The setting in crmticket/pop/delete_mail_month is not valid :'.$nbmonth;
          }
        }
        catch(Exception $ex)
        {
          $debug[] = "cleanOldEmails Exception: ".$ex->getMessage();
          Mage::logException($ex);
        }

        $debug[] = $emailDeleted.' emails deleted';

        $debug[] = 'End CRON cleanOldEmails at '.date('Y-m-d h.i.s');
        Mage::helper('CrmTicket')->log(implode("\n", $debug));
    }


    /**
     * Clean database from big saved mails
     */
    public function cleanBigSavedMails() {

        $enablePurge = Mage::getStoreConfig('crmticket/pop/enable_purge_table_mail_content');
        if ($enablePurge) {

            try{
                $debug = array();

                $beginTimeAll = time();
                $date = date('Ymdhis');

                $debug[] = 'Begin CRON cleanBigSavedMails at ' . $date;

                $connection = mage::getResourceModel('sales/order_item_collection')->getConnection();
                $prefix = Mage::getConfig()->getTablePrefix();

                $nbDays = Mage::getStoreConfig('crmticket/pop/purge_table_mail_after_x_days');
                if (!is_null($nbDays) && is_numeric($nbDays) && $nbDays > 0) {
                    //clean all mails content older than x days
                    $dateForCleaning = date("Y-m-d 00:00:00", strtotime("-" . $nbDays . " day"));

                    $sql = "UPDATE ".$prefix."crm_ticket_mail set ctm_rawheader = '', ctm_rawcontent = '' where ctm_isfilecontent=0 and ctm_date <  '$dateForCleaning';";
                    $connection->query($sql);
                }

                $nbMaxAllowed = Mage::getStoreConfig('crmticket/pop/purge_table_mail_bigger_than_x_mb');
                if (!is_null($nbMaxAllowed) && is_numeric($nbMaxAllowed) && $nbMaxAllowed > 0) {

                    //+clean very big mail to avoid sql dump error on database restore
                    $maxSize = $nbMaxAllowed * 1024 * 1024;
                    $sql = "UPDATE  ".$prefix."crm_ticket_mail set ctm_rawheader = '', ctm_rawcontent = '' where ctm_isfilecontent=0 and length(ctm_rawcontent) > $maxSize;";
                    $connection->query($sql);
                    //to check : SELECT ctm_id, LENGTH(ctm_rawcontent) AS len FROM crm_ticket_mail ORDER BY len DESC;
                }

                $debug[] = 'cleanBigSavedMails takes ' . (time() - $beginTimeAll) . ' seconds';

                $debug[] = 'End CRON cleanBigSavedMails at ' . $date;
                Mage::helper('CrmTicket')->log(implode("\n", $debug));
            }
            catch(Exception $ex)
            {
                Mage::logException($ex);
            }
        }
    }

   

    /**
     * Download new maislon all EMail Account
     */
    public function getNewEmails($displayResult = true)
    {
        $debug = array();
        $debug[] = 'getNewEmails BEGIN at ' . date('Y-m-d h:i:s');
        $result  = '';

        try {
            $collection = Mage::getModel('CrmTicket/EmailAccount')
                    ->getCollection()
                    ->addFieldToFilter('cea_enabled', 1)
                    ->setOrder('cea_connection_type', 'asc');

            foreach ($collection as $emailAccount) {

                //to avoid to block other accounts
                try {
                    $beginMessage = 'Checking ' . $emailAccount->getcea_name();

                    $debug[] = $beginMessage;
                    $result .= $beginMessage;

                    $resultAccount = Mage::getModel('CrmTicket/Email_Main')->checkForMails($emailAccount,$displayResult);
                    
                    $debug[] = $resultAccount;
                    $result .= '<br>'.$resultAccount.'<br>';

                } catch (Exception $ex) {
                    $debug[] = $emailAccount->getcea_name()." Skipped : getNewEmails Exception: " . $ex->getMessage();
                }
            }
        } catch (Exception $ex) {
            $debug[] = "getNewEmails Exception: " . $ex->getMessage();
        }

        $debug[] = 'getNewEmails End at ' . date('Y-m-d h:i:s');
        Mage::helper('CrmTicket')->log(implode("\n", $debug));

        return $result;
    }

    public function addToEmailSpamList($mail){
        if($mail){
            $forbiddenEmails = Mage::getModel('CrmTicket/EmailSpam')->getForbiddenEmails();
          
            if(!in_array($mail,$forbiddenEmails)){                
                $forbiddenEmails[] = $mail;               
                Mage::getModel('CrmTicket/EmailSpam')->setForbiddenEmails($forbiddenEmails);
            }
        }
    }
    
    public function addToDomainSpamList($domain){
        if($domain){
            $forbiddenDomains= Mage::getModel('CrmTicket/EmailSpam')->getForbiddenDomains();
          
            if(!in_array($domain,$forbiddenDomains)){
                $forbiddenDomains[] = $domain;
                Mage::getModel('CrmTicket/EmailSpam')->setForbiddenDomains($forbiddenDomains);
            }
        }
    }

    public function processAsSpam($spamAction, $ticketId){
        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        if ($ticket) {
            $email = $ticket->getCustomer()->getEmail();
            if($email){
                $domain = Mage::helper('CrmTicket/String')->getDomainFromEmail($email);
                switch ($spamAction) {
                    case MDN_CrmTicket_Model_EmailSpam::SPAM_ACTION_MARK_DOMAIN:
                        $this->addToDomainSpamList($domain);
                        break;

                    case MDN_CrmTicket_Model_EmailSpam::SPAM_ACTION_MARK_EMAIL:
                        $this->addToEmailSpamList($email);
                        break;

                    case MDN_CrmTicket_Model_EmailSpam::SPAM_ACTION_MARK_EMAIL_AND_DOMAIN:
                        $this->addToEmailSpamList($email);
                        $this->addToDomainSpamList($domain);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    

}

