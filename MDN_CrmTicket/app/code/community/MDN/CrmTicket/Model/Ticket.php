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
class MDN_CrmTicket_Model_Ticket extends Mage_Core_Model_Abstract
{

    //deprecated since crmTicket 1.3.6
    const STATUS_NEW = 'new';
    const STATUS_RESOLVED = 'resolved';

    //Tickets status
    const STATUS_WAITING_FOR_CLIENT = 'waiting_for_client';
    const STATUS_WAITING_FOR_ADMIN = 'waiting_for_admin';
    const STATUS_CLOSED = 'closed';

    //Tickets colors
    const COLOR_DONE = 'green';
    const COLOR_TODO = 'red';
    const COLOR_DEFAULT = 'black';
    
    //Invoicing Status
    const INVOICING_UNKNOWN = 'unknown';
    const INVOICING_TO_INVOICE = 'to_invoice';
    const INVOICING_INVOICED = 'invoiced';

    //cache
    private $_messages = null;
    private $_customer = null;
    private $_category = null;
    private $_product = null;
    private $_manager = null;
    private $_store = null;
    private $_emailaccount = null;
    private $_priority = null;
    private $_tagIds = null;
    private $_tags = null;
    private $_addableTags = null;
    private $_storeId = null;
    private $_statuses = null;

    public function _construct()
    {
        $this->_init('CrmTicket/Ticket', 'ct_id');
    }

    // -------------------- STATUS -----------------------------
    /**
     * get possible tickets statuses
     *
     */
    public function getStatuses()
    {
        if ($this->_statuses == null) {

            $statuses = array();
            $helper = Mage::helper('CrmTicket');

            //deprecated
            //$statuses[MDN_CrmTicket_Model_Ticket::STATUS_NEW] = $helper->__('New');
            //$statuses[MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED] = $helper->__('Resolved');
            //
            //hardcoded status
            $statuses[MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_CLIENT] = $helper->__('Waiting for client');
            $statuses[MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN] = $helper->__('Waiting for admin');
            $statuses[MDN_CrmTicket_Model_Ticket::STATUS_CLOSED] = $helper->__('Closed');

            //deprecated
            //custom status
            //$statuses = $this->getCustomStatus($statuses);
            
            $this->_statuses = $statuses;
        }
        return $this->_statuses;
    }

    public function getCustomStatus($statuses)
    {
        $customStatusesCollection = Mage::getModel('CrmTicket/Ticket_Status')
                ->getCollection()
                ->addFieldToFilter('cts_is_system', 0)//Only custom statues
                ->setOrder('cts_order', 'asc')//in the defined order
                ->setOrder('cts_name', 'asc'); //and secondly orderred by anme

        foreach ($customStatusesCollection as $customStatus) {
            $statuses[$customStatus->getcts_id()] = $customStatus->getcts_name();
        }

        return $statuses;
    }

    /**
     * Return all invoicing statuses
     * @return a
     */
    public function getInvoicingStatus()
    {
        $statuses = array();

        $helper = Mage::helper('CrmTicket');

        $statuses[MDN_CrmTicket_Model_Ticket::INVOICING_UNKNOWN] = $helper->__('Unknown');
        $statuses[MDN_CrmTicket_Model_Ticket::INVOICING_TO_INVOICE] = $helper->__('To invoice');
        $statuses[MDN_CrmTicket_Model_Ticket::INVOICING_INVOICED] = $helper->__('Invoiced');

        return $statuses;
    }

    /**
     * Set a ticket as Closed
     */
    public function close()
    {
        $this->setct_status(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED)->save();
    }

    /**
     * Return true if the ticket is closed
     * @return type
     */
    public function isClosed()
    {
        return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_CLOSED);
    }

    /**
     * //DEPRECATED
     * Return true if the ticket is resolved
     * @return type
     */
    public function isResolved()
    {
        return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);
    }

    /*
     * a ticket is editable if the status is not closed or resolved
     */

    public function isNotEditableByCustomer()
    {
        //we keep the test on resolved in case of bad upgrade, but the notion is deprecated
        return ($this->isClosed() || $this->isResolved());
    }

    /**
     * Status color for display
     *
     * @return type
     */
    public function getStatusColor()
    {
        $color = self::COLOR_DEFAULT;

        if ($this->isNotEditableByCustomer()) {
            $color = self::COLOR_DEFAULT;
        }

        if ($this->requireAdminReponse()) {
            $color = self::COLOR_TODO;
        }

        if ($this->requireCustomerReponse()) {
            $color = self::COLOR_DONE;
        }

        return $color;
    }

    public function requireAdminReponse()
    {
        return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_ADMIN);
    }

    public function requireCustomerReponse()
    {
        return ($this->getct_status() == MDN_CrmTicket_Model_Ticket::STATUS_WAITING_FOR_CLIENT);
    }

    /**
     * Set if customer can edit ticket
     * @return boolean
     */
    public function customerCanEdit()
    {
        return (!$this->isNotEditableByCustomer()) ? true : false;
    }

    /**
     * Return true if the ticket is closed
     * @return type
     */
    public function isPublic()
    {
        return ($this->getct_is_public() == 1) ? true : false;
    }

    public static function getClosedStatusesAsArray()
    {
        //we keep the test on resolved in case of bad upgrade, but the notion is deprecated
        return array(MDN_CrmTicket_Model_Ticket::STATUS_CLOSED, MDN_CrmTicket_Model_Ticket::STATUS_RESOLVED);
    }

    // ------------------------------- MESSAGES FUNCTIONS -----------------------

    /**
     * Posta customer message from front
     */
    public function postCustomerMessage($content, $attachments)
    {
        $isPublic = false;
        $notify = true;

        $additionalDatas = array();
        $additionalDatas['ctm_source_type'] = MDN_CrmTicket_Model_Message::TYPE_FORM;

        return $this->addMessage(MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER, $content, MDN_CrmTicket_Model_Message::CONTENT_TYPE_TEXT, $isPublic, $additionalDatas, $notify, $attachments);
    }

    /**
     * Add an new messsage and send a email
     */
    public function addMessage($author, $content, $contentType, $isPublic, $additionalDatas = array(), $notify = false, $attachments = array()) {

        // load & save NEW message
        $message = Mage::getModel('CrmTicket/Message');

        //clean message
        $content = $message->cleanMessage($content);

        $ticketId = $this->getId();
        $dateMessage = date('Y-m-d H:i:s');

        $message->setctm_content($content);
        $message->setctm_is_public($isPublic);
        $message->setctm_ticket_id($ticketId);
        $message->setctm_content_type($contentType);
        $message->setctm_author($author);
        $message->setctm_created_at($dateMessage);
        $message->setctm_updated_at($dateMessage);

        //log customer or admin ip
        $message->setctm_ip_address(Mage::helper('CrmTicket')->getUserIpAddress());

        if ($additionalDatas) {
            foreach ($additionalDatas as $k => $v) {
                $message->setData($k, $v);
            }
        }

        $message->save();

        //Unactivate the email's notification depending of the source of the message
        if ($notify) {
            if (!$message->isSourceNotify()) {
                $notify = false;
            }
        }

        $this->_messages = null;

        $this->setct_updated_at($dateMessage);

        //process attachments
        if (count($attachments) > 0) {
            Mage::helper('CrmTicket/Attachment')->saveAttachments($message, $attachments);
        }


        $backText = '';

        if ($notify) {
            Mage::dispatchEvent('crmticket_before_notify_message', array('message' => $message, 'backtext' => $backText));

            //If another module notify before -> unactivate email notification
            if ($message->notified) {
                $notify = false;
            }
        }

        //notify other party & change ticket status

        if ($author == MDN_CrmTicket_Model_Message::AUTHOR_ADMIN) {
            if ($notify) {
                $backText = $this->notify(MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER);
                $this->setct_status(self::STATUS_WAITING_FOR_CLIENT)->save(); //change the status only if a mail is sent
            }
        } else {
            if ($notify) {
                if (Mage::getStoreConfig('crmticket/notification/disable_admin_notification') != 1) {
                    $backText = $this->notify(MDN_CrmTicket_Model_Message::AUTHOR_ADMIN);
                }
            }
            $this->setct_status(self::STATUS_WAITING_FOR_ADMIN)->save();
        }

        if ($notify) {
            Mage::getSingleton('adminhtml/session')->addSuccess($backText);
        }

        //update msg count
        $this->updateMessageCount();

        //update dead line
        $this->setct_deadline($this->calculateDeadline());

        //detect Object from message content
        mage::getModel('CrmTicket/Customer_Object')->autoDetectObject($this, $content);

        $this->save();

        return $message->getId();
    }

    /**
     * get all messages of a ticket from older to most recent
     */
    public function getMessages($forceorder = null) {
        $order = 'ASC';

        if (Mage::getStoreConfig('crmticket/ticket_data/new_messages_in_first') == 1) {
            $order = 'DESC';
        }
        if (strlen($forceorder) > 2) {
            $order = $forceorder;
        }

        if ($this->getId()) {
            $this->_messages = mage::getModel('CrmTicket/Message')
                    ->getCollection()
                    ->addFieldToFilter('ctm_ticket_id', $this->getId())
                    ->setOrder('ctm_id', $order);
        } else {
            $this->_messages = mage::getModel('CrmTicket/Message')
                    ->getCollection()
                    ->addFieldToFilter('ctm_ticket_id', -1)
                    ->setOrder('ctm_id', $order);
        }
        return $this->_messages;
    }

    public function hasNewMessages($lastMessageId){
        $hasNewmessage = false;

        $collection = mage::getModel('CrmTicket/Message')
                    ->getCollection()
                    ->addFieldToFilter('ctm_ticket_id', $this->getId())
                    ->addFieldToFilter('ctm_id', array("gt" => $lastMessageId));

        if($collection->getSize()>0){
            $hasNewmessage = true;
        }
        
        return $hasNewmessage;
    }

    /**
     * Return msg count
     *
     * @return type
     */
    public function getMsgCount() {
        $collection = mage::getModel('CrmTicket/Message')
                ->getCollection()
                ->addFieldToFilter('ctm_ticket_id', $this->getId());

        return $collection->getSize();
    }

    /**
     * Update msg count
     */
    public function updateMessageCount() {
        $this->setct_msg_count($this->getMsgCount())->save();
    }

    /**
     * return attachments
     * @return type
     */
    public function getAttachments() {
        return Mage::helper('CrmTicket/Attachment')->getAttachments($this);
    }

    /**
     * Send email to customer
     */
    public function notify($target = MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
        $customerStoreid = $this->getCustomer()->getStoreId();
        $frontUrl = Mage::getUrl('CrmTicket/Front_Ticket/AutoLogin', array('ticket_id' => $this->getId(), '_store' => $customerStoreid, 'control_key' => $this->getControlKey()));
        $backUrl = Mage::helper('adminhtml')->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $this->getId()));

        $targetName = '';
        $customer = $this->getCustomer();

        if ($target == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
            $targetEmail = $customer->getEmail();
            $targetName = $customer->getName();
            $url = $frontUrl;
        } else {
            $manager = $this->getManager();
            $targetEmail = $manager->getEmail();
            $targetName = $manager->getName();
            $url = $backUrl;
        }

        //if the mail is from a mail import, we have to answer using the mail account associated to the category
        if ($target == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
            $emailAccount = $this->getEmailAccount();
            if (!$emailAccount) {
                throw new Exception('Please define an email account for this ticket');
            }

            $identity = array('name' => $emailAccount->getcea_name(), 'email' => $emailAccount->getCurrentSenderEmail());
        } else {
            $identity = array('name' => $customer->getName(), 'email' => $customer->getEmail());
        }

        $emailStoreId = $customerStoreid;
        if ($this->getStore()->getId()) {
            $emailStoreId = $this->getStore()->getId();
        }

        $emailTemplate = Mage::getStoreConfig('crmticket/notification/template', $emailStoreId);

        if ($emailTemplate == '') {
            die('Email template is not set (system > config > CRM > template)');
        }

        // get ticket infos
        $ticketId = $this->getId();
        $ticketSubject = $this->getct_subject();

        // get message infos
        $message = $this->getMessages('ASC')->getLastItem();
        $messageCreated = $message->getctm_created_at();
        $messageContent = $message->getctm_content();

        if (strlen($messageContent) < 1) {
            return '';
        }

        // add block to display the list of tickets
        $this->setData('area', 'adminhtml');
        $block = Mage::getSingleton('core/layout')->createBlock('CrmTicket/Admin_Email_Ticket_Messages');
        $block->setTicket($this);
        $block->setTemplate('CrmTicket/Email/Ticket/Messages.phtml');
        $ticketsHtml = $block->toHtml();

        // horsebrands last message to display on top of notification
        $block->setTemplate('CrmTicket/Email/Ticket/LastMessage.phtml');
        $latestMessage = $block->toHtml();

        //set previous message
        $previousMessage = '';


        //definies datas
        $data = array(
            'ct_subject' => $ticketSubject,
            'ct_ticket_id' => $ticketId,
            'ctm_created_at' => $messageCreated,
            'ctm_content' => $messageContent,
            'messages' => $ticketsHtml,
            'latest_message' => $latestMessage,
            'url' => $url,
            'hashtag' => $this->getHashTag(),
            'responsetag' => MDN_CrmTicket_Model_Email_EmailToTicket::FLAG_RESPONSE,
            'previous_message' => $previousMessage,
            'attachements' => $message->getAttachments()
        );


        $backText = '';

        //send email
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);


        $result = Mage::getModel('CrmTicket/EmailTemplate')
                ->sendTransactional(
                $emailTemplate, $identity, $targetEmail, $targetName, $data, $emailStoreId);

        if (!$result) {
            throw new Exception('An error happened trying to send email');
        }


        $backText = Mage::helper('CrmTicket')->__('Email sent to %s', $targetEmail);

        //send email to cc
        $data['url'] = $frontUrl;
        $ccslist = $this->getct_cc_email();
        if ($ccslist) {
            $ccs = explode(';', $ccslist);
            if ($ccs && count($ccs) > 0) {
                foreach ($ccs as $cc) {
                    if ($cc) {
                        $result = Mage::getModel('CrmTicket/EmailTemplate')
                                ->sendTransactional(
                                $emailTemplate, $identity, $cc, 'name', $data, $emailStoreId
                        );
                        //TODO : parse result
                        $backText .= ', ' . $cc;
                    }
                }
            }
        }

        return $backText;
    }

    // ----------------------------------------- OBJECT TRIGGERS --------------------------------

    /**
     * Called before a ticket is deleted
     * @return \MDN_CrmTicket_Model_Ticket
     */
    protected function _beforeDelete() {
        parent::_beforeDelete();

        // delete messages
        foreach ($this->getMessages('ASC') as $message) {
            $message->delete();
        }

        //Delete attachments
        Mage::helper('CrmTicket/Attachment')->deleteAttachments($this->getId());

        return $this;
    }

    /**
     * Before save (update created_at & updated_at dates)
     */
    protected function _beforeSave() {
        parent::_beforeSave();

        //if ticket is being created
        if (!$this->getId()) {
            //set creation date
            $this->setct_created_at(date('Y-m-d H:i:s'));

            //calculate dead line (if not allready set)
            if (!$this->getct_deadline()) {
                $this->setct_deadline($this->calculateDeadline());
            }

            $this->defineEmailAccountForTicket();

            //dispatch event
            Mage::dispatchEvent('crmticket_before_create_ticket', array('ticket' => $this));
        } else {    //ticket updated
            //if reply delay has changed, update dead line
            if ($this->getct_reply_delay() != $this->getOrigData('ct_reply_delay')) {
                $this->setct_deadline($this->calculateDeadline());
            }
        }

        if ($this->getOrigData('ct_subject') != $this->getct_subject()) {
            mage::getModel('CrmTicket/Customer_Object')->autoDetectObject($this, $this->getct_subject());
        }
    }

    /**
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        //update category msg count if ticket category has changed
        if ($this->getct_category_id() != $this->getOrigData('ct_category_id')) {
            $cat = $this->getCategory();
            if ($cat) {
                $cat->updateTicketCount();
            }
        }

        //if ticket status has changed, to closed or resolved (or any "blocking" status) -> dispatch the information
        if ($this->getOrigData('ct_status') != $this->getct_status()) {
            if (!$this->customerCanEdit()) {
                Mage::dispatchEvent('crmticket_ticket_status_changed', array('ticket' => $this));
            }
        }
    }

    // ----------------------------------------- CACHES --------------------------------


    public function getCustomer() {
        if ($this->_customer == null) {
            $customerId = $this->getct_customer_id();
            $this->_customer = mage::getModel('customer/customer')->load($customerId);
        }
        return $this->_customer;
    }

    public function getCategory() {
        if ($this->_category == null) {
            $categoryId = $this->getct_category_id();
            $this->_category = Mage::getModel('CrmTicket/Category')->load($categoryId);
        }
        return $this->_category;
    }

    public function getPriority() {
        if ($this->_priority == null) {
            $priorityId = $this->getct_priority();
            $this->_priority = Mage::getModel('CrmTicket/Ticket_Priority')->load($priorityId);
        }
        return $this->_priority;
    }

    /**
     * return product of the current ticket
     */
    public function getProduct() {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getct_product_id());
        }
        return $this->_product;
    }

    /**
     * return store of the current ticket
     */
    public function getStore() {
        if ($this->_store == null) {
            $storeId = $this->getct_store_id();
            if (is_null($storeId)) {
                //avoid crash
                $storeId = 1;
            }
            $this->_store = Mage::getModel('core/store')->load($storeId);
        }
        return $this->_store;
    }

    /**
     * Return ticket manager
     * @return type
     */
    public function getManager() {
        if ($this->_manager == null) {
            $managerId = $this->getct_manager();
            $this->_manager = Mage::getModel('admin/user')->load($managerId);
        }
        return $this->_manager;
    }

    // --------------------------- tags ----------------------

    /**
     * get the ticket tags collection
     */
    public function getTags() {
        if ($this->_tags == null) {
            if ($this->_tagIds == null) {
                $this->_tagIds = Mage::getModel('CrmTicket/TicketTag')->getTicketTagsIds($this);
            }
            $this->_tags = Mage::getModel('CrmTicket/Tag')->getCollection()->addFieldToFilter('ctg_id', array('in' => $this->_tagIds));
        }
        return $this->_tags;
    }

    /*
     * get the ticket tag collection that can be added
     */

    public function getAddableTags() {
        if ($this->_addableTags == null) {
            if ($this->_tagIds == null) {
                $this->_tagIds = Mage::getModel('CrmTicket/TicketTag')->getTicketTagsIds($this);
            }
            if (count($this->_tagIds) > 0) {
                $this->_addableTags = Mage::getModel('CrmTicket/Tag')->getCollection()->addFieldToFilter('ctg_id', array('nin' => $this->_tagIds));
            } else {
                $this->_addableTags = Mage::getModel('CrmTicket/Tag')->getCollection();
            }
        }
        return $this->_addableTags;
    }

    public function getStoreId() {
        if ($this->_storeId == null) {
            $storeId = $this->getct_store_id();

            if (!$storeId) {
                $customer = $this->getCustomer();
                if ($customer) {
                    //get store from customer
                    $storeId = $customer->getStoreId();
                }
            }
            $this->_storeId == $storeId;
        }

        return $this->_storeId;
    }

    // --------------------------- Email account ----------------------

    public function defineEmailAccountForTicket() {
        //set user_account using matrix rules, if not allready defined
        if (!$this->getct_email_account()) {
            $emailAccountIdFound = null;

            $rule = Mage::getModel('CrmTicket/EmailAccountRouterRules')->getEmailAccountRule($this->getct_store_id(), $this->getct_category_id());
            if ($rule) {
                $emailAccountIdFound = $rule->getcearr_email_account_id();
            }

            if (!$emailAccountIdFound) {
                //if no email account after use the default email account
                $emailAccountIdFound = Mage::getModel('CrmTicket/EmailAccount')->getDefaultEmailAccountId();

                if (!$emailAccountIdFound) {
                    //if no email account after routing rules, use the first one to avoid to get message without email account
                    $emailAccountIdFound = Mage::getModel('CrmTicket/EmailAccount')->getFirstEmailAccountId();
                }
            }

            //if no email account after routing rules, use the first one to avoid to get message without email account
            if ($emailAccountIdFound) {
                $emailAccount = Mage::getModel('CrmTicket/EmailAccount')->getEmailLoginById($emailAccountIdFound);
                if ($emailAccount) {
                    $this->setct_email_account($emailAccount);
                }
            }
        }
    }

    /**
     * Returns the account id relative to this ticket
     * @return String
     */
    public function getEmailAccount() {
        if ($this->_emailaccount == null) {
            //retrieve email account by the email account used in ticket
            $ticketAccount = $this->getct_email_account();

            if ($ticketAccount) {
                //retrieve email account by the email account used in ticket
                $ticketAccount = $this->getct_email_account();

                //if there is no email account found and there is only one email account defined we set is by default for any ticket
                if (strlen($ticketAccount) == 0) {
                    $emailAccountsCollections = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccounts();
                    if (count($emailAccountsCollections) == 1) {
                        $ticketAccount = $emailAccountsCollections->getFirstItem()->getConsolidedLogin();
                    }
                }

                //TODO : if a new ticket from BO from a user or an order, get routing rules to apply them here

                if ($ticketAccount) {
                    $this->_emailaccount = Mage::getModel('CrmTicket/EmailAccount')->getRealAccountLogin($ticketAccount);
                }
            }
        }

        return $this->_emailaccount;
    }

    /*
     * get the signature from email account associated with the response mail of this ticket
     */

    public function getResponseSignature() {
        $signature = '';

        $emailAccount = $this->getEmailAccount();

        if ($emailAccount) {
            $signature = $emailAccount->getcea_signature();
        }

        return $signature;
    }

    // ------------------------------- FRONT ---------------------------------------------

    /**
     * Control key to autolog customer
     */
    public function getControlKey() {
        $key = $this->getct_autologin_control_key();
        if (!$key) {
            $key = md5(date('YYYY-mm-dd H:i:s') . $this->getId());
            $this->setct_autologin_control_key($key)->save();
        }
        return $key;
    }

    /**
     * Update the number of public view for this ticket
     */
    public function updatePublicViewCount() {
        $nbview = $this->getct_nb_view();

        if (!is_null($nbview) && $nbview >= 0) {
            $nbview++;
        } else {
            $nbview = 1; //init at 1 view for this time in case of problem
        }
        $this->setct_nb_view($nbview)->save();
    }

    /**
     * Return has tag (to embedded in emails)
     */
    public function getHashTag() {
        return Mage::helper('CrmTicket/Hashtag')->getHashtag($this);
    }

    // ------------------------------------- OTHERS ---------------------------
    /**
     *  Return the list of quick action available for this ticket
     *
     * @return array of QuickAction
     */
    public function getQuickActions() {
        return Mage::getModel('CrmTicket/Ticket_QuickAction')->getQuickActions($this);
    }

    /**
     * Get the Class name of the current ticket
     * @return String Object classname or empty
     */
    public function getCustomerObjectClass() {
        $customerObjectType = '';

        $objectId = $this->getct_object_id();

        if ($objectId) {
            list($objectType, $objectId) = explode('_', $objectId); //get "Order" from "Order_1351415"
            if ($objectType) {
                $customerObjectType = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($objectType);
            }
        }

        return $customerObjectType;
    }

    /**
     * Calculate dead line
     * @return \Zend_Db_Expr
     */
    protected function calculateDeadline() {
        $baseDate = null;
        foreach ($this->getMessages('ASC') as $msg) {
            if ($msg->getctm_author() == MDN_CrmTicket_Model_Message::AUTHOR_CUSTOMER) {
                if (strtotime($baseDate) < strtotime($msg->getctm_created_at())) {
                    $baseDate = $msg->getctm_created_at();
                }
            }
        }

        if ($baseDate) {
            $delay = $this->getReplyDelay();
            return date('Y-m-d H:i:s', strtotime($baseDate) + $delay * 3600);
        }

        return new Zend_Db_Expr('null');
    }

    /**
     * Calculate reply delay for this ticket (base on default / category / ticket)
     */
    protected function getReplyDelay() {
        $delay = Mage::getStoreConfig('crmticket/general/delay_to_reply');

        //check delay at category level
        if ($this->getCategory()) {
            $replayDelay = $this->getCategory()->getctc_reply_delay();
            if ($replayDelay) {
                $delay = $replayDelay;
            }
        }

        //check delay at ticket level
        if ($this->getct_reply_delay() > 0) {
            $delay = $this->getct_reply_delay();
        }

        return $delay;
    }

}
