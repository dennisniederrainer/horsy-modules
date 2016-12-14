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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Ticket_Edit extends Mage_Adminhtml_Block_Widget_Form
{


    private $_ticket = null;
    private $_priorities = null;
    private $_categories = null;
    private $_users = null;
    private $_products = null;
    private $_emailaccounts = null;
    private $_emailaccount = null;
    private $_defaultreplies = null;

    private $_customer = null;
    private $_customerPhones = null;
    private $_customerGroup = null;
    private $_customerShippingAddress = null;
    private $_customerBillingAddress = null;
    private $_customerTicketCount = 0;

    private $_customerObjects = null;
    private $_customerQuotes = null;
    private $_customerRmas = null;
    private $_customerOrders = null;

    private $_customerLastQuotes = null;
    private $_customerLastRmas = null;
    private $_customerLastOrders = null;

    const LAST_ORDER_COUNT = 5;
    const LAST_RMA_COUNT = 5;
    const LAST_QUOTE_COUNT = 5;
    const LAST_ORDERED_PRODUCTS = 10;

    /**
     * Prepare layout (insert wysiwyg js scripts)
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
                
        if (method_exists(Mage::helper('catalog'), 'isModuleEnabled')) {
            if (Mage::helper('catalog')->isModuleEnabled('Mage_Cms')) {
                if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                    $head = $this->getLayout()->getBlock('head');
                    if(is_object($head)){
                       $head->setCanLoadTinyMce(true);
                    }
                }
            }
        }
    }

    /**
     * get current ticket for editing
     */
    public function getTicket()
    {
        if ($this->_ticket == null) {
            $ticketId = $this->getRequest()->getParam('ticket_id');
            $this->_ticket = mage::getModel('CrmTicket/Ticket')->load($ticketId);
            if (!$ticketId) {
                $this->_ticket->setct_customer_id($this->getRequest()->getParam('customer_id'));
                $this->_ticket->setct_manager(Mage::getSingleton('admin/session')->getUser()->getId());
            }
        }
        return $this->_ticket;
    }

    public function setTicket($ticket){
        $this->_ticket == $ticket;
        return $this;
    }

    /**
     * get current message for editing
     */
    public function getMessage($messageId)
    {
        return Mage::getModel('CrmTicket/Message')->load($messageId);
    }

    /**
     * url of the button back
     * @return type
     */
    public function getBackUrl()
    {
        $referer = $this->getRequest()->getOriginalRequest()->getHeader('Referer');

        if ($referer && strpos($referer, '/My/')>0) {
            return $this->getUrl('CrmTicket/Admin_Ticket/My');
        } else {
            return $this->getUrl('CrmTicket/Admin_Ticket/Grid');
        }
    }

    /**
     *
     * @return type
     */
    public function getDeleteUrl($ticketId)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/Delete', array('ticket_id' => $ticketId));
    }

    /**
     *
     * @return type
     */
    public function getDeleteMessageUrl($messageId) {
        return $this->getUrl('CrmTicket/Admin_Ticket/DeleteMessage', array('message_id' => $messageId));
    }

    /**
     *
     * @return type
     */
    public function getNewTicketUrl($customerId)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('customer_id' => $customerId));
    }
    
    /**
     * Notify admin
     * @return type
     */
    public function getNotifyAdminUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/NotifyAdmin', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * Notify customer
     * @return type
     */
    public function getNotifyCustomerUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/NotifyCustomer', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * Notify custom email
     * @return type
     */
    public function getNotifyCustomEmailsUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/NotifyCustomEmails', array('ticket_id' => $this->getTicket()->getId()));
    }

    public function getRefreshMessageListUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/MessageListAjax', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     * return all magento users
     */
    public function getManagers()
    {
        if ($this->_users == null) {
            $this->_users = mage::getSingleton('admin/user')->getCollection();
        }
        return $this->_users;
    }

    /**
     * return all categories
     */
    public function getCategories()
    {
        if ($this->_categories == null) {
            $this->_categories = mage::getSingleton('admin/user')->getCollection();
        }
        return $this->_categories;
    }

    /**
     * return all categories
     */
    public function getPriorities()
    {
        if ($this->_priorities == null) {
            $this->_priorities = mage::getModel('CrmTicket/Ticket_Priority')->getCollection();
        }
        return $this->_priorities;
    }

    /**
     * call controller to notitify customer
     */
    public function getUrlNotifyCustomer()
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/triggerNotifyCustomer', array('ticket_id' => $this->getTicket()->getId()));
    }

    /**
     *
     * @return type
     */
    public function getTitle()
    {
        $ticketId = $this->getTicket()->getId();
        if ($ticketId) {
            return $this->__('Edit ticket').' #'.$ticketId;
        } else {
            return $this->__('New ticket');
        }
    }

    /**
     *
     * @return type
     */
    public function getCustomer()
    {
        if ($this->_customer == null) {
            $customer = $this->getTicket()->getCustomer();

            if ($customer) {
                $this->_customer = $customer;
            }
        }
        return $this->_customer;
    }

        
    

    public function getCustomerPhones()
    {
        if ($this->_customerPhones == null) {
            $customer = $this->getCustomer();

            if ($customer) {
                $this->_customerPhones = Mage::helper('CrmTicket/Customer')->getFormatedPhones($customer);
            }
        }
        return $this->_customerPhones;
    }

    public function getCustomerGroups()
    {
        if ($this->_customerGroup == null) {
            $customer = $this->getCustomer();

            if ($customer) {
                $groupId = $customer->getGroupId();
                if ($groupId) {
                    $this->_customerGroup = Mage::getModel('customer/group')->load($groupId)->getCode();
                }
            }
        }
        return $this->_customerGroup;
    }

    public function getBillingAddress()
    {
        if ($this->_customerBillingAddress == null) {
            $customer = $this->getCustomer();

            if ($customer) {
                $address = $customer->getPrimaryBillingAddress();

                if ($address) {
                    $this->_customerBillingAddress = Mage::helper('CrmTicket/Customer')->getFormatedAddress($address);
                }
            }
        }

        return $this->_customerBillingAddress;
    }

    public function getShippingAddress()
    {
        if ($this->_customerShippingAddress == null) {
            $customer = $this->getCustomer();
            
            if ($customer) {
                $address = $customer->getPrimaryShippingAddress();

                if ($address) {
                    $this->_customerShippingAddress = Mage::helper('CrmTicket/Customer')->getFormatedAddress($address);
                }
            }
        }
        return $this->_customerShippingAddress;
    }

    public function getCustomerTicketCount(){


        if ($this->_customerTicketCount == null) {

            if($this->getCustomer()){
                
                $collection = Mage::getModel('CrmTicket/Ticket')
                    ->getCollection()
                    ->addFieldToFilter('ct_customer_id', $this->getCustomerId());

                if($collection){
                    $this->_customerTicketCount = $collection->getSize();
                }
            }
        }
        
        return $this->_customerTicketCount;
    }

    //-- orders ----------------------------------

    public function getOrders()
    {
        if ($this->_customerOrders == null) {

            $customer = $this->getCustomer();

            if ($customer) {
                $this->_customerOrders = Mage::getResourceModel('sales/order_collection')
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('customer_id', $customer->getId())
                    ->setOrder('created_at', 'desc');
            }
        }
        return $this->_customerOrders;
    }

    public function getLastOrders()
    {
        if ($this->_customerLastOrders == null) {
                           
            $collection = $this->getOrders();

            if($collection){
                $collection->getSelect()->limit($this->getOrderLimit());
                $this->_customerLastOrders = $collection;
            }
            
        }
        return $this->_customerLastOrders;
    }

    public function getOrderCount()
    {
        return ($this->getOrders())?$this->getOrders()->getSize():0;
    }

    public function getOrderLimit()
    {
        return self::LAST_ORDER_COUNT;
    }

    public function getOrderUrl($order)
    {
        $urlInfo = array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $order->getId()));
        return $this->getUrl($urlInfo['url'], $urlInfo['param']);
    }
    
    public function getOrderMainInfos($order)
    {
        return '<strong>'.$order->getIncrementId().'</strong>';
    }

    public function getOrderAdditionnalInfos($order)
    {
        return ' - '.Mage::helper('core')->formatDate($order->getCreatedAt()).' - '.round($order->getGrandTotal(), 2).' '.Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol().' - <i>('.$order->getStatusLabel().')</i>';
    }


    //-- Rmas ----------------------------------

    public function getRmas()
    {
        if ($this->_customerRmas == null) {

            if (Mage::helper('CrmTicket')->checkModulePresence(Mage::getModel('CrmTicket/Customer_Object_Rma')->getModuleDependenceKey())) {
                if ($this->getCustomer()) {
                    $collection = Mage::getModel('ProductReturn/Rma')->loadByCustomer($this->getCustomerId());
                    $collection->setOrder('rma_created_at', 'desc');
                    $this->_customerRmas = $collection;
                }
            }
        }

        return $this->_customerRmas;
    }
    
    public function getLastRmas()
    {
        if ($this->_customerLastRmas == null) {
            
            $collection = $this->getRmas();
            if($collection){
                $collection->getSelect()->limit($this->getRmaLimit());
                $this->_customerLastRmas = $collection;
            }
        }
        return $this->_customerLastRmas;
    }

    public function getRmaCount()
    {
        return ($this->getRmas())?$this->getRmas()->getSize():0;
    }

    public function getRmaLimit()
    {
        return self::LAST_RMA_COUNT;
    }

    public function getRmaUrl($rma)
    {
        $urlInfo = array('url' => 'ProductReturn/Admin/Edit', 'param' => array('rma_id' => $rma->getrma_id()));
        return $this->getUrl($urlInfo['url'], $urlInfo['param']);
    }

    public function getRmaMainInfos($rma)
    {
        return '<strong>'.$rma->getrma_ref().'</strong>';
    }

    public function getRmaAdditionnalInfos($rma)
    {
        return ' - '.Mage::helper('core')->formatDate($rma->getrma_created_at());
    }

    //-------- Quotes ---------------------

    public function getQuotes()
    {
        if ($this->_customerQuotes == null) {

            if (Mage::helper('CrmTicket')->checkModulePresence(Mage::getModel('CrmTicket/Customer_Object_Quote')->getModuleDependenceKey())) {
                if ($this->getCustomer()) {                    
                    $collection = Mage::getModel('Quotation/Quotation')->loadByCustomer($this->getCustomerId());                    
                    $collection->setOrder('quotation_id', 'desc');
                    $this->_customerQuotes = $collection;
                }
            }
        }

        return $this->_customerQuotes;
    }

    public function getLastQuotes()
    {
        if ($this->_customerLastQuotes == null) {

           $collection = $this->getQuotes();
            if($collection){
                $collection->getSelect()->limit($this->getQuoteLimit());
                $this->_customerLastQuotes = $collection;
            }
            
        }
        return $this->_customerLastQuotes;
    }

    public function getQuoteCount()
    {
        return ($this->getQuotes())?$this->getQuotes()->getSize():0;        
    }

    public function getQuoteLimit()
    {
        return self::LAST_QUOTE_COUNT;
    }

    public function getQuoteUrl($quote)
    {
        $urlInfo = array('url' => 'Quotation/Admin/edit/', 'param' => array('quote_id' => $quote->getquotation_id()));
        return $this->getUrl($urlInfo['url'], $urlInfo['param']);
    }

    public function getQuoteMainInfos($quote)
    {
        return '<strong>'.$quote->getincrement_id().'</strong> ';
    }

    public function getQuoteAdditionnalInfos($quote)
    {
        return ' - '.$quote->getcaption().' - '.Mage::helper('core')->formatDate($quote->getcreated_time()).' - '.round($quote->GetFinalPriceWithTaxes(), 2).' '.Mage::app()->getLocale()->currency($quote->getCurrency())->getSymbol();
    }

    //-------- Order products ---------------------


    public function getLastProducts(){
        $result = array();
        $lastOrders = $this->getLastOrders();
        if($lastOrders){
            $lastOrderIds = $lastOrders->getAllIds();
            if(count($lastOrderIds)>0){
                $prefix = Mage::getConfig()->getTablePrefix();
                $sql = 'SELECT product_id, count(product_id) as nb_ordered ';
                $sql .= 'FROM '.$prefix.'sales_flat_order_item ';
                $sql .= 'WHERE order_id in ('.implode($lastOrderIds,',').') ';
                $sql .= 'GROUP BY product_id ';
                $sql .= 'ORDER BY nb_ordered DESC ';
                $sql .= 'LIMIT 0,'.self::LAST_ORDERED_PRODUCTS;
                $result = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
            }
        }
        return $result;

    }

    public function getProductUrl($productId)
    {
        $urlInfo = array('url' => 'adminhtml/catalog_product/edit', 'param' => array('id' => $productId));
        return $this->getUrl($urlInfo['url'], $urlInfo['param']);
    }

    public function getProductInfo($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getName().' ('.$product->getSku().')';
    }

    //-- Customer
    
    public function getCustomerId()
    {
        return $this->getTicket()->getct_customer_id();
    }


    /**
     * Url to see customer sheet
     * @return type
     */
    public function getCustomerUrl()
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getCustomerId()));
    }

    /*
     * Return all products
     */
    public function getProducts()
    {
        if ($this->_products == null) {
            $this->_products = Mage::helper('CrmTicket/Product')->getProducts();
        }
        return $this->_products;
    }

    /**
     * return url to download attachment from admin
     * @param type $attachment
     */
    public function getAttachmentDownloadLink($attachment)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/downloadAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'attachment' => $attachment->getFileName()));
    }

    /**
     * return url to delete attachment from admin
     * @param type $attachment
     */
    public function getAttachmentDeleteLink($attachment)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/deleteAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'attachment' => $attachment->getFileName()));
    }

    /**
     * return url to download message attachment from admin
     * @param type $attachment
     */
    public function getAttachmentMessageDownloadLink($message, $attachment)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/downloadMessageAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'message_id' => $message->getId(), 'attachment' => $attachment->getFileName()));
    }

    /**
     * return url to delete message attachment from admin
     * @param type $attachment
     */
    public function getAttachmentMessageDeleteLink($message, $attachment)
    {
        return $this->getUrl('CrmTicket/Admin_Ticket/deleteMessageAttachment', array('ticket_id' => $attachment->getTicket()->getId(), 'message_id' => $message->getId(), 'attachment' => $attachment->getFileName()));
    }
    
    /**
     * return a list of attachments for the current message
     *
     * @param type $attachment
     */
    public function getAttachments()
    {
        $ticket = $this->getTicket();
        return Mage::helper('CrmTicket/Attachment')->getAttachmentsForMessage($ticket, null);//null be cause new message
    }

    /**
     * Event to allow other extensions to display information under the private comments
     */
    public function getCustomContent()
    {
        Mage::dispatchEvent('crmticket_ticket_sheet_custom_data', array('ticket' => $this->getTicket()));
    }

    /**
     * Return booleans values
     * @return type
     */
    public function getBooleans()
    {
        $a = array();
        $a[0] = $this->__('No');
        $a[1] = $this->__('Yes');
        return $a;
    }

    /**
     * return invoicing statuses
     * @return type
     */
    public function getInvoicingStatus()
    {
        return Mage::getModel('CrmTicket/Ticket')->getInvoicingStatus();
    }

    /**
     * Return websites
     * @return type
     */
    public function getWebsiteCollection()
    {
        return Mage::app()->getWebsites();
    }

    /**
     * return groups for one website
     * @param Mage_Core_Model_Website $website
     * @return type
     */
    public function getGroupCollection(Mage_Core_Model_Website $website)
    {
        return $website->getGroups();
    }

    /**
     * Return stores for one group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return type
     */
    public function getStoreCollection(Mage_Core_Model_Store_Group $group)
    {
        return $group->getStores();
    }

    /**
     * Return customer objects
     */
    public function getCustomerObjects()
    {
         if ($this->_customerObjects == null) {
            $this->_customerObjects = Mage::getModel('CrmTicket/Customer_Object')->getObjects($this->getCustomerId());
        }
        return $this->_customerObjects;
    }

    public function getCustomerObjectsCount()
    {
        return ($this->getCustomerObjects())?count($this->getCustomerObjects()):0;
    }
    
    /**
     * View object url
     */
    public function getViewObjectUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Customer/ViewObject');
    }
    
    /**
     * display a popup with objct details
     */
    public function getPopupObjectUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Customer/ViewObjectPopup');
    }

    public function getSetSenderAsSpammerUrl($spamEmail){
        return $this->getUrl('CrmTicket/Admin_Email/addToEmailSpamList', array('email' => $spamEmail, 'ticket_id' => $this->getTicket()->getId()));
    }
    
    /**
     * Get Default Replies
     *
     * @return type
     */
    public function getDefaultReplies()
    {
        if ($this->_defaultreplies == null) {
            $this->_defaultreplies =  Mage::getModel('CrmTicket/DefaultReply')->getCollection()->setOrder('cdr_name', 'asc');
        }
        return $this->_defaultreplies;
    }

   
    public function getResponseSignature()
    {
        return $this->getTicket()->getResponseSignature();
    }

    public function getEmailAccounts()
    {
        if ($this->_emailaccounts == null) {
            $this->_emailaccounts = Mage::getModel('CrmTicket/EmailAccount')->getEmailAccounts();
        }
        return $this->_emailaccounts;
    }

    public function getEmailAccount()
    {
        if ($this->_emailaccount == null) {
            $this->_emailaccount = $this->getTicket()->getEmailAccount();
            if (!$this->_emailaccount) {
                $paramAccountId = $this->getRequest()->getParam('email_account_id');
                if ($paramAccountId) {
                    $this->_emailaccount = Mage::getModel('CrmTicket/EmailAccount')->load($paramAccountId);
                }
            }
        }
        return $this->_emailaccount;
    }


    /**
     * Because a ticket email account is not allways define, these checks are neceassary
     *
     * @param type $emailaccount
     * @return boolean
     */
    public function matchEmailAccount($emailaccount)
    {
        $match = false;
        if ($emailaccount) {
            $ticketEmailAccount = $this->getTicket()->getEmailAccount();
            if ($ticketEmailAccount) {
                if ($ticketEmailAccount->getId() == $emailaccount->getId()) {
                    $match = true;
                }
            }
        }
        return $match;
    }

   /**
    * Get the store id from the database for te current a ticket
    * If the ticket is new, try to retrieve it from the customer
    */
   public function getStoreId()
   {
       return $this->getTicket()->getStoreId();
   }


   /**
     * return url to delete a tag from admin
     * @param type $attachment
     */
    public function getTagDeleteLink($ticketId,$tagId) {
        return $this->getUrl('CrmTicket/Admin_Tag/TicketTagAjax', array('ticket_id' => $ticketId, 'action' => 'delete','ctg_id' => $tagId));
    }

    /**
     * return url to add a tag from admin
     * @param type $attachment
     */
    public function getTagAddLink($ticketId,$tagId) {
        return $this->getUrl('CrmTicket/Admin_Tag/TicketTagAjax', array('ticket_id' => $ticketId, 'action' => 'add', 'ctg_id' => $tagId));
    }

    /**
     * Special fcuntion to simplify the main phtml file
     * 
     * @param type $templateFileName
     */
    public function displaySubTemplate($templateFileName) {
        echo $this->setTemplate('CrmTicket/Ticket/Edit/Tab/Sections/'.$templateFileName)->toHtml();
    }

   
    public function getCustomerOrderTotalInvoiced()
    {
        $amount = 0;
        if($this->getCustomerTotalOrderCount()>0){
            $collection = $this->getCustomerOrderCollection();
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->addExpressionFieldToSelect('customer_total','SUM({{base_total_paid}})','base_total_paid');
            $totalPaidArray = $collection->getColumnValues('customer_total');
            if($totalPaidArray && is_array($totalPaidArray) && count($totalPaidArray)>0){
                $amount = (float)$totalPaidArray[0];
            }
        }
        $display = $amount.' '.$this->getCurrencyCode();
        return  $display;
    }

    
    public function getCustomerOrderCollection()
    {        
        return Mage::getResourceModel('sales/order_collection')->addFieldToFilter('customer_id', $this->getCustomer()->getId());
    }

    public function getCustomerTotalOrderCount()
    {
        return $this->getCustomerOrderCollection()->getSize();
    }

    public function getCustomerCompletedOrderCount()
    {
        return $this->getCustomerOrderCollection()->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)->getSize();
    }

    public function getCurrencyCode()
    {
        return Mage::app()->getStore(Mage::app()->getStore()->getStoreId())->getCurrentCurrencyCode();
    }

    public function getNewMessageEditor(){
        $defaultMessage = '';
        $currentMessage = trim($this->getTicket()->getct_current_message());
        $signature = trim($this->getResponseSignature());
        if(strlen($currentMessage)>strlen($signature)){
          $defaultMessage = $currentMessage;
        }else{
          $defaultMessage = $signature;
        }
        return Mage::helper('CrmTicket/Editor')->getWysiwygHtml('ctm_content', $defaultMessage, false,'100%','400px');
    }

   
}
