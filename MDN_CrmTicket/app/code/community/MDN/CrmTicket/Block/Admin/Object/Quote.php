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
class MDN_CrmTicket_Block_Admin_Object_Quote extends MDN_CrmTicket_Block_Admin_Object_Popup
{
    
    public function getQuote()
    {
        return $this->getObject();
    }

    public function getQuoteLink()
    {
        $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Quote')->getObjectAdminLink($this->getQuote()->getquotation_id());
        $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
        return '<a href="' . $url . '" target="_blank">' . $this->__('Quote #').$this->getQuote()->getincrement_id() . '</a>';
    }

    public function getQuoteCustomerLink()
    {
        $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Quote')->getCustomerAdminLink($this->getQuote()->getcustomer_id());
        $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
        return '<a href="' . $url . '" target="_blank">' .$this->getQuote()->getCustomer()->getName() . '</a>';
    }

    public function getQuoteManagerName()
    {
        return $this->getQuote()->getmanager();
    }

    public function getProductItem($id)
    {
        return Mage::getModel('catalog/product')->load($id);
    }
}
