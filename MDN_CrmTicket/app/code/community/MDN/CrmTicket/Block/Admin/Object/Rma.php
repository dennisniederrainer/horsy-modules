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
class MDN_CrmTicket_Block_Admin_Object_Rma extends MDN_CrmTicket_Block_Admin_Object_Popup
{

    public function getRma()
    {
        return $this->getObject();
    }

    public function getRmaLink()
    {
        $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Rma')->getObjectAdminLink($this->getRma()->getrma_id());
        $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
        return '<a href="' . $url . '" target="_blank">' . $this->__('Rma #').$this->getRma()->getrma_ref() . '</a>';
    }

    public function getRmaOrderLink()
    {
        $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Rma')->getOrderAdminLink($this->getRma()->getrma_order_id());
        $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
        return '<a href="' . $url . '" target="_blank">'.$this->getRma()->getSalesOrder()->getIncrementId() . '</a>';
    }

    public function getRmaCustomerLink()
    {
        $urlInfo = Mage::getModel('CrmTicket/Customer_Object_Rma')->getCustomerAdminLink($this->getRma()->getrma_customer_id());
        $url = $this->getUrl($urlInfo['url'], $urlInfo['param']);
        return '<a href="' . $url . '" target="_blank">' .$this->getRma()->getCustomer()->getName() . '</a>';
    }

    public function getRmaManagerName()
    {
        return mage::getSingleton('admin/user')->load($this->getRma()->getrma_manager_id())->getName();
    }

    public function getAvailableQty($product)
    {
        return (int)($product->getqty_invoiced() - $product->getqty_refunded() - $product->getqty_canceled());
    }
}
