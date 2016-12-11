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
class MDN_CrmTicket_Model_Ticket_Priority extends Mage_Core_Model_Abstract {
    
    public function _construct(){

        $this->_init('CrmTicket/Ticket_Priority', 'ctp_id');

    }

    protected function _beforeSave() {
        parent::_beforeSave();
        
        if($this->getctp_priority_value()<1){
            throw new Exception(Mage::helper('CrmTicket')->__('The value must be >=1'));
        }

        if(strlen($this->getctp_name())==0){
            throw new Exception(Mage::helper('CrmTicket')->__('The name must be filled'));
        }
        
    }
    
}