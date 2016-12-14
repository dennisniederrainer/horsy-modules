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
class MDN_CrmTicket_Front_TicketController extends Mage_Core_Controller_Front_Action
{

    protected function _checkAuthentication()
    {
        if (!mage::helper('customer')->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return false;
        }

        return true;
    }

    /**
     * load block for customer account
     */
    public function MyTicketsAction()
    {
        $this->_checkAuthentication();
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('My tickets'));
        $this->renderLayout();
    }

    /**
     * view ticket infos
     */
    public function ViewTicketAction()
    {
        $this->_checkAuthentication();

        $ticketId = $this->getRequest()->getParam('ticket_id');
        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        try {
            Mage::helper('CrmTicket/Customer')->currentCustomerCanViewTicket($ticket);
            $this->loadLayout();

            $this->getLayout()->getBlock('head')->setTitle($this->__('Ticket #%s', $ticketId));
            $this->renderLayout();
        } catch (Exception $ex) {
            Mage::getSingleton('core/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Front_Ticket/MyTickets');
        }
    }

    /**
     * called in ajax (so we dont use the layouts)
     */
    public function NewTicketAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('New Ticket'));
        $this->renderLayout();
    }

    
    public function SubmitTicketAction()
    {
        $_redirect = 'CrmTicket/Front_Ticket/NewTicket';
        $_sessionType = 'core';

        try {
            $ticketData = $this->getRequest()->getPost('ticket');
            $customerData = $this->getRequest()->getPost('customer');
            $messageData = $this->getRequest()->getPost('message');
            $messageData['ctm_content'] = $this->getRequest()->getPost('ctm_content');

            

            // call helper to save ticket
            $ticket = mage::getModel('CrmTicket/Ticket');

            // check if customer is logged
            $islogged = mage::helper("CrmTicket/Customer")->customerIsConnected();
            if ($islogged) {
                $_sessionType = 'customer';
            }


            if (Mage::getStoreConfig('crmticket/email_spam/front_anti_spam_protection')) {
                try {
                    mage::helper('CrmTicket/Captcha')->checkResults($ticketData, $messageData, $customerData, $islogged);

                //if any robot behaviour-like is detected, returns to contacts page with a message
                } catch (Exception $ex) {
                    Mage::getSingleton($_sessionType.'/session')->addError($ex->getMessage());
                    $this->_redirect($_redirect);
                    return true;
                }
            }

            if ($islogged == false) {

                //create new customer
                $storeId = Mage::app()->getStore()->getStoreId();
                $password = substr(base64_encode("your password."), 0, 10);
                $emailSubmitted = trim($customerData["customer_email"]);
                $isExistingCustomerNotLoggued = true;
                try {
                    $customerId = mage::helper("CrmTicket/Customer")
                        ->createNewCustomer(
                                $customerData["first_name_customer"],
                                $customerData["last_name_customer"],
                                $emailSubmitted,
                                $password,
                                $storeId);

                    $isExistingCustomerNotLoggued = false;
                } catch (Exception $e) {
                    $_redirect = 'customer/account/login';

                    //get the customer id from the email because we known it allready exists
                    $customerModel = Mage::getModel('customer/customer');
                    $customerModel->setWebsiteId(Mage::app()->getWebsite()->getId());
                    $customerExist = $customerModel->loadByEmail($emailSubmitted);

                    if ($customerExist && $customerExist->getId() > 0) {
                        // because we want to post the ticket without beeing loggued to avoid to loose the message data
                       $customerId = $customerExist->getId();
                        $isExistingCustomerNotLoggued = true;
                    } else {
                        throw new Exception($e->getMessage(), $e->getCode());
                    }
                }

                //log customer
                if (!$isExistingCustomerNotLoggued) {
                    $session = Mage::getSingleton('customer/session');
                    $session->setCustomerAsLoggedIn(mage::getModel('customer/customer')->load($customerId));
                }
            } else {
                $customerId = $ticketData["ct_customer_id"];
            }

            //last security to avoid to create ticket without any customer defined (it can happend if customer click very hardly in some scenarios)
            if(!$customerId){
                $_redirect = 'customer/account/login';
                throw new Exception("Can't identify you to post the message, please post your message again");
            }

            $storeId = Mage::app()->getStore()->getStoreId();
            $ticket->setct_store_id($storeId);

            $currentDateTime = date('Y-m-d H:i:s');

            $ticket->setct_created_at($currentDateTime);
            $ticket->setct_updated_at($currentDateTime);
            $ticket->setct_customer_id($customerId); // get customer id

            $ticket->setct_category_id($ticketData["ct_category_id"]);
            $ticket->setct_subject($ticketData["ct_subject"]);
            $ticket->setct_manager((isset($ticketData["ct_manager"]) ? $ticketData["ct_manager"] : 0)); // assign manager
            $ticket->setct_status(MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN);

            if (isset($ticketData["ct_object_id"])) {
                $ticket->setct_object_id($ticketData["ct_object_id"]); // assign object
            }

            $ticket->setct_product_id((isset($ticketData["ct_product_id"]) ? $ticketData["ct_product_id"] : 0));
            $ticket->save();

            //affect ticket to manager
            if (!Mage::getStoreConfig('crmticket/manager_affectation/do_not_affect_manager_on_new_ticket')) {
                Mage::helper('CrmTicket/TicketRouter')->affectTicketToManager($ticket);
            }
            
            $attachments = $this->getAttachmentsAsArray();

            $messageData['ctm_content'] = Mage::helper('CrmTicket/String')->prepareTextFromRawTextArea($messageData['ctm_content']);

            $ticket->postCustomerMessage($messageData["ctm_content"], $attachments['attachments']);
            
            $additionnalMessage = $attachments['attachmentsErrors'];

            //Redirect on the ticket page
            if ($islogged == false && $isExistingCustomerNotLoggued) {
                Mage::getSingleton('customer/session')->addSuccess($this->__('Message submitted. This account already exists, please login.'));

                //Add customer email to pre fill email field
                $this->_redirect($_redirect);
            } else {
                Mage::getSingleton($_sessionType.'/session')->addSuccess($this->__('Message submitted').$additionnalMessage);
                $_redirect = 'CrmTicket/Front_Ticket/ViewTicket';
                $this->_redirect($_redirect, array('ticket_id' => $ticket->getId()));
            }
        } catch (Exception $error) {
            Mage::getSingleton($_sessionType.'/session')->addError($this->__('Error : %s', $error->getMessage()));
            $this->_redirect($_redirect);
        }
    }
    
    /**
     * Return an array with post attachments
     */
    protected function getAttachmentsAsArray()
    {
        $return = array();

        $attachments = array();
        $attachmentsRefusedLog = '';

        try {
            $attachmentHelper = Mage::helper('CrmTicket/Attachment');
            $nbMaxAttachement = $attachmentHelper->getPublicMaxAttachementAllowed();
            $adminKey = $attachmentHelper->getPublicMessageAttachementKey();
            $allowedExtensions = $attachmentHelper->getPublicAllowedFileExtensions();
            for ($i = 1; $i <= $nbMaxAttachement; $i++) {
                $key = $adminKey . $i;
                if (isset($_FILES[$key]) && $_FILES[$key]['name'] != "") {
                    if (file_exists($_FILES[$key]['tmp_name'])) {
                        if ($attachmentHelper->checkAttachmentAllowed($_FILES[$key]['name'], $allowedExtensions)) {
                            $attachments[$_FILES[$key]['name']] = file_get_contents($_FILES[$key]['tmp_name']);
                        } else {
                            $attachmentsRefusedLog .= '<br>Attachment refused : '.$_FILES[$key]['name'].' : filetype not allowed' ;
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $attachmentsRefusedLog .= $ex->getMessage();
        }

        $return['attachments'] = $attachments;
        $return['attachmentsErrors'] = $attachmentsRefusedLog;

        return $return;
    }

    /**
     * Sent a message from customer account page
     */
    public function ReplyAction()
    {
        $messageData = $this->getRequest()->getPost();
      
        //add message if set
        if ($messageData["ctm_content"]) {

            $messageData['ctm_content'] = Mage::helper('CrmTicket/String')->prepareTextFromRawTextArea($messageData['ctm_content']);

            $ticket = mage::getModel('CrmTicket/Ticket')->load($messageData["ticket_id"]);

            $attachments = $this->getAttachmentsAsArray();

            $ticket->postCustomerMessage($messageData["ctm_content"], $attachments['attachments']);

            $additionnalMessage = $attachments['attachmentsErrors'];

            Mage::getSingleton('core/session')->addSuccess($this->__('Message submitted').$additionnalMessage);
        }

        //confirm
        $this->_redirect('CrmTicket/Front_Ticket/ViewTicket', array('ticket_id' => $messageData["ticket_id"]));
    }

    public function MailItemsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Download an attachment
     */
    public function DownloadAttachmentAction()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        $attachmentName = str_replace('..', '', $attachmentName);
        $attachmentName = str_replace('/', '', $attachmentName);

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        try {
            //check ticket rights
            Mage::helper('CrmTicket/Customer')->currentCustomerCanViewTicket($ticket);

            //check attachment
            $attachmentPath = Mage::helper('CrmTicket/Attachment')->getAttachmentPath($ticket, $attachmentName);
            if (!file_exists($attachmentPath)) {
                throw new Exception('This file doesnt exist !');
            }

            //return file for download
            $mime = mage::helper('CrmTicket/Attachment')->getMimeTypeContent($attachmentPath);
            $content = file_get_contents($attachmentPath);
            $this->prepareDownloadResponse($attachmentName, $content, $mime);
        } catch (Exception $ex) {
            Mage::getSingleton('core/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Front_Ticket/MyTickets');
        }
    }

    public function downloadMessageAttachmentAction()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $messageId = $this->getRequest()->getParam('message_id');

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $message = Mage::getModel('CrmTicket/Message')->load($messageId);

        //get and clean attachment name
        $attachmentName = $this->getRequest()->getParam('attachment');
        $attachmentName = str_replace('..', '', $attachmentName);
        $attachmentName = str_replace('/', '', $attachmentName);
        $attachmentName = urldecode($attachmentName);

        try {
            //check attachment
            $attachmentPath = Mage::helper('CrmTicket/Attachment')->getMessageAttachmentPath($ticket, $message, $attachmentName);

            if (!file_exists($attachmentPath)) {
                throw new Exception('This file doesnt exist : '.$attachmentPath);
            }

            //return file for download
            $mime = mage::helper('CrmTicket/Attachment')->getMimeTypeContent($attachmentPath);
            $content = file_get_contents($attachmentPath);
            $this->_prepareDownloadResponse($attachmentName, $content, $mime);
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
            $this->_redirect('CrmTicket/Front_Ticket/ViewTicket', array('ticket_id' => $ticketId));
        }
    }

    //backport for old magento version
    protected function prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream', $contentLength = null)
    {
        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = filesize($file);
            }
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->setHeader('Last-Modified', date('r'));

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $ioAdapter = new Varien_Io_File();
                if (!$ioAdapter->fileExists($file)) {
                    Mage::throwException(Mage::helper('core')->__('File not found'));
                }
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }

                exit(0);
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }

    /**
     * End the ticket
     */
    public function CloseTicketAction()
    {
        $ticketId = $this->getRequest()->getParam('ticket_id');

        $ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
        
        if ($ticket) {
            $ticket->close();
            Mage::getSingleton('core/session')->addSuccess($this->__('Ticket is closed'));
        }

        $this->_redirect('CrmTicket/Front_Ticket/ViewTicket', array('ticket_id' => $ticketId));
    }

    /**
     * Autologin
     */
    public function AutoLoginAction()
    {
        //get param
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $controlKey = $this->getRequest()->getParam('control_key');

        try {
            if ((!$ticketId) || (!$controlKey)) {
                throw new Exception('Url incorrect !');
            }

            //load ticket & check control key
            $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
            if ($controlKey != $ticket->getct_autologin_control_key()) {
                throw new Exception('Control key is incorrect');
            }

            //log customer & redirect to ticket
            $session = Mage::getSingleton('customer/session');
            $session->setCustomerAsLoggedIn($ticket->getCustomer());
            $this->_redirect('CrmTicket/Front_Ticket/ViewTicket', array('ticket_id' => $ticket->getId()));
        } catch (Exception $ex) {
            //display error msg & redirect to customer login
            Mage::getSingleton('core/session')->addError($this->__($ex->getMessage()));
            die($ex->getMessage());
            $this->_redirect('customer/account/login');
        }
    }

    /**
     * Check if the customer captcha si valid
     *
     * @return boolean
     */
    public function CheckCaptchaAction()
    {
        $status = "NOK";

        $antiSpamCheck = $this->getRequest()->getParam('anti_spam_check');
        $antiSpamHumanResult = $this->getRequest()->getParam('anti_spam_human_result');

        //Anti Spam protection
        if (isset($antiSpamCheck) && isset($antiSpamHumanResult)) {
            if (mage::helper('CrmTicket/Captcha')->checkResult($antiSpamCheck, $antiSpamHumanResult)) {
                $status = "OK";
            }
        }
        
        echo $status;
    }
}
