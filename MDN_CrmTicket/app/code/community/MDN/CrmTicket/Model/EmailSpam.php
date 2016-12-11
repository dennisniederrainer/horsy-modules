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
class MDN_CrmTicket_Model_EmailSpam extends Mage_Core_Model_Abstract {

    private static $_SPAM_CONF_SEPARATOR = ',';

    const SPAM_ACTION_MARK_DOMAIN = 'domain_is_spam';
    const SPAM_ACTION_MARK_EMAIL = 'email_is_spam';
    const SPAM_ACTION_MARK_EMAIL_AND_DOMAIN = 'domain_and_email_is_spam';

    public function _construct(){
        $this->_init('CrmTicket/EmailSpam', 'cesr_id');
    }

    /*
     * Theses domains does not send Spam emails
     */
    public function getAllowedDomains(){
      return Mage::helper('CrmTicket')->getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/allowed_domains',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * Theses domain allways send Spam emails
     */
    public function getForbiddenDomains(){
      return Mage::helper('CrmTicket')->getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/forbidden_domains',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * Theses emails does not send Spam emails
     */
    public function getAllowedEmails(){
      return Mage::helper('CrmTicket')->getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/allowed_emails',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * Theses emails allways send Spam emails
     */
    public function getForbiddenEmails(){
      return Mage::helper('CrmTicket')->getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/forbidden_emails',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * If the parser find one of these word ina mail subject, the email will be marked as spam
     */
    public function getKeywordSpamList(){
      return Mage::helper('CrmTicket')->getConfTextAreaAsTrimedArray(
                'crmticket/email_spam/forbidden_subject_keywords',
                self::$_SPAM_CONF_SEPARATOR,
                array());
    }

    /*
     * Update Spam List
     */
    public function setForbiddenEmails($forbiddenMails){
        if(is_array($forbiddenMails)){
            $forbiddenMails = implode(self::$_SPAM_CONF_SEPARATOR, $forbiddenMails);
        }
        $conf = new Mage_Core_Model_Config();
        $conf->saveConfig('crmticket/email_spam/forbidden_emails', $forbiddenMails);
        //Mage::app()->getConfig()->reinit();
    }

    public function setForbiddenDomains($forbiddenDomains){
        if(is_array($forbiddenDomains)){
            $forbiddenDomains = implode(self::$_SPAM_CONF_SEPARATOR, $forbiddenDomains);
        }
        $conf = new Mage_Core_Model_Config();
        $conf->saveConfig('crmticket/email_spam/forbidden_domains', $forbiddenDomains);
        //Mage::app()->getConfig()->reinit();
    }

    public function getSpamActions(){
      $helper = Mage::helper('CrmTicket');

      $spamActions = array();

      $spamActions[self::SPAM_ACTION_MARK_DOMAIN] = $helper->__('Mark domain as spam');
      $spamActions[self::SPAM_ACTION_MARK_EMAIL] = $helper->__('Mark email as spam');
      $spamActions[self::SPAM_ACTION_MARK_EMAIL_AND_DOMAIN] = $helper->__('Mark email and domain as spam');

      return $spamActions;
    }

}
