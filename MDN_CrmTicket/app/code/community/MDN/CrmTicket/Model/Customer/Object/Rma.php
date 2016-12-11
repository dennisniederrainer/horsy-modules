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
class MDN_CrmTicket_Model_Customer_Object_Rma extends MDN_CrmTicket_Model_Customer_Object_Abstract
{

    public function getObjectType()
    {
        return 'rma';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Rma');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'ProductReturn/Admin/Edit', 'param' => array('rma_id' => $objectId));
    }

    public function getCustomerAdminLink($objectId)
    {
        return array('url' => 'adminhtml/customer/edit', 'param' => array('id' => $objectId));
    }

    public function getOrderAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_order/view', 'param' => array('order_id' => $objectId));
    }
    
    public function getObjects($customerId)
    {
        $objects = array();

        if ($this->isClassAllowedForLoading()) {
            $collection = Mage::getModel('ProductReturn/Rma')->loadByCustomer($customerId);

            foreach ($collection as $rma) {
                $objects[$this->getObjectKey($rma)] = $rma->getrma_ref().self::DESC_SEPARATOR.'('.Mage::helper('core')->formatDate($rma->getrma_created_at()).')';
            }
        }
        
        return $objects;
    }

    public function isClassAllowedForLoading()
    {
        return Mage::helper('CrmTicket')->checkModulePresence($this->getModuleDependenceKey());
    }

    /**
     *
     * @param type $object
     * @return type
     */
    public function getObjectKey($object)
    {
        return $this->getObjectType().parent::ID_SEPARATOR.$object->getrma_id();
    }
    
    
    public function getObjectClassName()
    {
        return 'ProductReturn/Rma';
    }

    public function getObjectTitle($id)
    {
        return Mage::helper('CrmTicket')->__('Rma #%s', $this->loadObject($id)->getrma_ref());
    }

    public static function getQuickActions()
    {
        return '';
    }

    public function getModuleDependenceKey()
    {
        return 'MDN_ProductReturn';
    }
}
