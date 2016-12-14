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
class MDN_CrmTicket_Model_Customer_Object_Quote extends MDN_CrmTicket_Model_Customer_Object_Abstract
{

    public function getObjectType()
    {
        return 'quote';
    }
    
    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Quote');
    }
    
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'Quotation/Admin/edit/', 'param' => array('quote_id' => $objectId));
    }
    
    public function getObjects($customerId)
    {
        $objects = array();

        if ($this->isClassAllowedForLoading()) {
            $collection = Mage::getModel('Quotation/Quotation')->loadByCustomer($customerId);

            foreach ($collection as $quote) {
                $objects[$this->getObjectKey($quote)] = $quote->getincrement_id().self::DESC_SEPARATOR.'('.Mage::helper('core')->formatDate($quote->getcreated_time()).')';
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
        return $this->getObjectType().parent::ID_SEPARATOR.$object->getquotation_id();
    }
    
    
    public function getObjectClassName()
    {
        return 'Quotation/Quotation';
    }

    public function getObjectTitle($id)
    {
        return Mage::helper('CrmTicket')->__('Quote #%s', $this->loadObject($id)->getincrement_id());
    }

    public static function getQuickActions()
    {
        return '';
    }

    public function getModuleDependenceKey()
    {
        return 'MDN_Quotation';
    }
}
