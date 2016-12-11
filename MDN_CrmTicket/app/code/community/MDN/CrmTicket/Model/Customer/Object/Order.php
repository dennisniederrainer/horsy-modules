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
class MDN_CrmTicket_Model_Customer_Object_Order extends MDN_CrmTicket_Model_Customer_Object_Abstract
{

    public function getObjectType()
    {
        return 'order';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Orders');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $objectId));
    }
    
    /**
     * return orders
     *
     * @param type $customerId
     * @return type
     */
    public function getObjects($customerId)
    {
        $collection = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('customer_id', $customerId);
        $retour = array();
        foreach ($collection as $item) {
            $orderDescription = $item->getIncrementId().self::DESC_SEPARATOR.'('.Mage::helper('core')->formatDate($item->getCreatedAt()).')';
            if ($item->getmarketplace_order_id()) {
                $orderDescription .= self::DESC_SEPARATOR.$item->getmarketplace_order_id();
            }
            $retour[$this->getObjectKey($item)] = $orderDescription;
        }
        return $retour;
    }
    
    /**
     *
     * @param type $object
     * @return type
     */
    public function getObjectKey($object)
    {
        return $this->getObjectType().parent::ID_SEPARATOR.$object->getId();
    }
    
   
    public function getObjectClassName()
    {
        return 'sales/order';
    }
    
    public function getObjectTitle($id)
    {
        return Mage::helper('CrmTicket')->__('Order #%s', $this->loadObject($id)->getincrement_id());
    }


    public static function getQuickActions()
    {
        return array('invoice');
    }

    public function isClassAllowedForLoading()
    {
        return true;
    }
}
