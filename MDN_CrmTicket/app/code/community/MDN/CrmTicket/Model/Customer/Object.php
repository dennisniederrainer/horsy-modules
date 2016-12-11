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
class MDN_CrmTicket_Model_Customer_Object extends Mage_Core_Model_Abstract
{

  /**
   * Return all objects for one customer (the one of the ticket, logic ...)
   *
   * @param type $customerId
   */
  public function getObjects($customerId)
  {
      $objects = array();

      $classes = $this->getClasses();
      foreach ($classes as $class) {
          $classObjects = $class->getObjects($customerId);
          if (count($classObjects) > 0) {
              $objects[$class->getObjectName()] = $classObjects;
          }
      }

      return $objects;
  }

  /**
   * Return all customer object classes (from config.xml file(s))
   */
  public function getClasses()
  {
      $retour = array();

      $nodes = Mage::getConfig()->getNode('crmticket/customer/objects')->asArray();

      foreach ($nodes as $k => $info) {
          $obj = Mage::getModel($info['class']);
          $retour[] = $obj;
      }

      return $retour;
  }

  /**
   * Get class by type
   * @param type $type
   * @return null
   */
  public function getClassByType($type)
  {
      foreach ($this->getClasses() as $class) {
          if ($class->getObjectType() == $type) {
              return $class;
          }
      }
      return null;
  }

  /**
   * detct the
   * @param type $ticket
   * @param type $textToParse
   */
  public function autoDetectObject($ticket, $textToParse)
  {
      $return = null;

      if (!$ticket->getct_object_id() && $textToParse) {
          $objectIdsToDetect = $this->getObjects($ticket->getct_customer_id());

          foreach ($objectIdsToDetect as $group => $items) {
              foreach ($items as $objectKeyId => $objectFormalizedDescription) {
                  $infos = explode(MDN_CrmTicket_Model_Customer_Object_Abstract::DESC_SEPARATOR, $objectFormalizedDescription);
                                 
                  //detect order, Rma, Quote, inveoice, CreditMemo ...
                  $objectIncrementId = $infos[0];
                  $found = (strpos($textToParse, $objectIncrementId) !== false);

                  //if there is a marketplaceorderid, try to detect it
                  if (count($infos)== 3 && strpos($textToParse, $infos[2]) !== false) {
                      $found = true;
                  }

                  if ($found) {
                      $ticket->setct_object_id($objectKeyId);
                      return $objectKeyId;
                  }
              }
          }
      }
      
      return $return;
  }
}
