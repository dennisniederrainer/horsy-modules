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
class MDN_CrmTicket_Block_Admin_Ticket_Summary_User extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('CustomRelationManager');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
        $this->setDefaultDir('DESC');
    }

    public function getTickets(){
        return mage::getModel('CrmTicket/Ticket')->getCollection();
    }
 
    /**
     * return managers (magento user)
     *  
     */
    public function getManagers(){
        
        $magentoUsers = array();
        
        $magentoUsers = mage::getSingleton('admin/user')
                                ->getCollection()
                                ->addFieldToFilter('is_active', 1);
        
        return $magentoUsers;
    }
        
    
    /**
     * get the categories of the ticket 
     */
    public function getCategories(){
        return mage::getModel('CrmTicket/Category')->getCollection();                
    }
    
    /**
     * return statuses by category 
     */
    public function getStatuses(){        
       return Mage::getModel('CrmTicket/Ticket')->getStatuses();
    }
   
    /**
     * 
     * @param type $status
     * @param type $categoryId
     * @return string 
     */
    public function getTicketCount($status, $userId){

        // get tickets with categories id and ticket status
        $tickets = mage::getModel('CrmTicket/Ticket')
                ->getCollection();
              
        if ($status)
                $tickets->addFieldToFilter('ct_status', $status);
        
        if ($userId)
                $tickets->addFieldToFilter('ct_manager', $userId);
        
        return count($tickets->getAllIds());
    }
    
}

