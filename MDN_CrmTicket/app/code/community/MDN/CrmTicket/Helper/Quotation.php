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
class MDN_CrmTicket_Helper_Quotation extends Mage_Core_Helper_Abstract
{

    private $_list = null;

    public function getTicketsByQuotationId($quoteId)
    {
        if (!$this->_list) {
            if ($quoteId && $quoteId > 0) {
                $this->_list = Mage::getModel('CrmTicket/Ticket')
                    ->getCollection()
                    ->addFieldToFilter('ct_object_id', 'quote_'.$quoteId);
            }
        }
        
        return $this->_list;
    }
}
