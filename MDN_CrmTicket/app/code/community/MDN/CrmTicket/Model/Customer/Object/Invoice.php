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
class MDN_CrmTicket_Model_Customer_Object_Invoice extends MDN_CrmTicket_Model_Customer_Object_Abstract
{

    public function getObjectType()
    {
        return 'invoice';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Invoices');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_invoice/view', 'param' => array('invoice_id' => $objectId));
    }
    
    public function getObjects($customerId)
    {
        $collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customerId);
        $retour = array();
        foreach ($collection as $order) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $retour[$this->getObjectType().parent::ID_SEPARATOR.$invoice->getId()] = $invoice->getIncrementId().self::DESC_SEPARATOR.'('.Mage::helper('core')->formatDate($invoice->getCreatedAt()).')';
            }
        }
        return $retour;
    }
    

    public function getObjectClassName()
    {
        return 'sales/order_invoice';
    }

    public function getObjectTitle($id)
    {
        return Mage::helper('CrmTicket')->__('Invoice #%s', $this->loadObject($id))->getIncrementId();
    }

    public static function getQuickActions()
    {
        return '';
    }

    public function isClassAllowedForLoading()
    {
        return true;
    }
}
