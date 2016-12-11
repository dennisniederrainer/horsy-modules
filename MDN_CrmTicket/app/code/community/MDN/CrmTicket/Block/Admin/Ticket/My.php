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
class MDN_CrmTicket_Block_Admin_Ticket_My extends MDN_CrmTicket_Block_Admin_Ticket_Grid {

     public function __construct() {
        parent::__construct();
        $this->setId('CrmMyTicketsGrid');
        
    }
    /**
     * load collection with join
     *
     * @return unknown
     */
    protected function _prepareCollection() {


        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        
        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->join('customer/entity', 'ct_customer_id=entity_id')
                ->addFieldToFilter('ct_manager', $userId);

        $prefix = Mage::getConfig()->getTablePrefix();
        $collection->getSelect()->joinLeft(array('prty' => $prefix . 'crm_ticket_priority'), 'main_table.ct_priority = prty.ctp_id', array('ctp_id', 'ctp_priority_value'));

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();//assumed

    }


    public function setManager(){
        $this->_showManager = false;
    }

    /**
     * Ajax callback
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/MyGridAjax', array('_current'=>true));
    }

    
    
    
}

