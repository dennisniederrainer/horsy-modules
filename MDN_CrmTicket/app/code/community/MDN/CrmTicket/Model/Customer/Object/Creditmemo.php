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
class MDN_CrmTicket_Model_Customer_Object_Creditmemo extends MDN_CrmTicket_Model_Customer_Object_Abstract
{

    public function getObjectType()
    {
        return 'creditmemo';
    }

    public function getObjectName()
    {
        return Mage::helper('CrmTicket')->__('Creditmemos');
    }

    //creditmemo_id - >ça existe ?
    public function getObjectAdminLink($objectId)
    {
        return array('url' => 'adminhtml/sales_creditmemo/view', 'param' => array('creditmemo_id' => $objectId));
    }

    public function getObjects($customerId)
    {
        $collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customerId);
        $retour = array();
        foreach ($collection as $order) {
            foreach ($order->getCreditmemosCollection() as $creditMemo) {
                $retour[$this->getObjectType() . parent::ID_SEPARATOR . $creditMemo->getId()] = $creditMemo->getIncrementId() .self::DESC_SEPARATOR.'(' . Mage::helper('core')->formatDate($creditMemo->getCreatedAt()) . ')';
            }
        }
        return $retour;
    }



    public function getObjectClassName()
    {
        return 'sales/order_creditmemo';
    }

    /**
     *
     * @param type $id
     * @return type
     */
    public function getObjectTitle($id)
    {
        return Mage::helper('CrmTicket')->__('Creditmemo #%s', $this->loadObject($id)->getIncrementId());
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
