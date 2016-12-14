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
class MDN_CrmTicket_Model_Observer {

    /**
     * Retrieve mail from all mail box with an activated account
     * 
     */
    public function checkAllActivatedMailAccount() {

        mage::helper('CrmTicket/Mail')->getNewEmails();
    }

    /**
     * Clean older mail that the number of month defined in
     * System -> config -> crmticket -> pop -> delete_mail_month
     * Used by a cron job scheduled in config.xml the 1st day of every month at 3:00am
     *
     */
    public function cleanOldEmails(){       

        mage::helper('CrmTicket/Mail')->cleanOldEmails();       
    }

     /**
     * crm_ticket_mail can be very big, we clean it
     * mage::getModel('CrmTicket/Observer')->cleanBigSavedMails();
     *
     * This function is used by the button "Purge It" in system -> config -> CrmTicket -> Email account -> button "Purge It"
     *
     */
    public function cleanBigSavedMails(){

        mage::helper('CrmTicket/Mail')->cleanBigSavedMails();
    }
}


