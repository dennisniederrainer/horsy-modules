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
class MDN_CrmTicket_Helper_Data extends Mage_Core_Helper_Abstract
{


    private $_moduleList;

    public function allowProductSelection()
    {
        return (Mage::getStoreConfig('crmticket/general/allow_product_selection') == 1);
    }

    public function allowCustomerObjectSelection()
    {
        return (Mage::getStoreConfig('crmticket/general/allow_object_selection') == 1);
    }

    /*
     * Format order_id, Invoice id ... for public Display
     */

    public function getObjectPublicName($objectid)
    {
        return str_replace(MDN_CrmTicket_Model_Customer_Object_Abstract::ID_SEPARATOR, ' N.', ucfirst(strtolower($objectid)));
    }

    /**
     * Log message
     * @param type $msg
     */
    public function log($msg)
    {
        if (Mage::app()->getRequest()->getParam('dbg') == 1) {
            echo('<br>' . $msg);
        } else {
            mage::log($msg, null, 'crm_ticket.log');
        }
    }

    /**
     * Log message
     * @param type $msg
     */
    public function logErrors($msg)
    {
        mage::log($msg, null, 'crm_ticket_errors.log');
        if (Mage::app()->getRequest()->getParam('dbg') == 1) {
            echo('<br>' . $msg);
        }
    }
    
    /**
     *
     * @param type $msg
     */
    public function notifyTechnicalContact($msg)
    {
        $websiteName = Mage::getStoreConfig('web/unsecure/base_url');
        mail(Mage::getStoreConfig('crmticket/notification/technical_contact'), 'CRM Ticket error on '.$websiteName, $msg);
    }

    /**
     * Parse a getStoreConfig of type text area
     * explode it using the separator and trim each field
     * an return an array with the valid values
     *
     * @param string $confEntry url of conf
     * @param string $separator
     * @param array of string $defaultvalue
     * @return array of string
     */
    public function getConfTextAreaAsTrimedArray($confPath, $separator, $defaultValue)
    {
        $confValues = $this->getSafeConfigValue($confPath);
        if (strlen($confValues)>0) {
            $confEntries = explode($separator, $confValues);            
            foreach ($confEntries as $index => $value) {
                $confEntries[$index] = trim($value);
            }           
            return $confEntries;
        }
        return $defaultValue;
    }

    private function getSafeConfigValue($confPath) {
        $value = null;
        if ($confPath) {
            $sql = 'SELECT value FROM '.Mage::getConfig()->getTablePrefix().'core_config_data WHERE path="' . $confPath.'";';
            $row = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchRow($sql);
            if($row && array_key_exists('value', $row)){
                $value = $row['value'];
            }
        }
        return $value;
    }

    /**
    * Return booleans values, usefull for ye sno Combo
    * @return type
    */
   public function getBooleans()
   {
       $options = array();
       $options[0] = $this->__('No');
       $options[1] = $this->__('Yes');
       return $options;
   }

   /**
     * Detect the user IP address
     *
     * @return type
     */
    public function getUserIpAddress()
    {
        $ip = null;

        //based on ZF logic from \lib\Zend\Http\UserAgent\AbstractDevice.php
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        return $ip;
    }

    /**
    *
    * Checks if a module is present
    */
   public function checkModulePresence($moduleKeyName)
   {
       $presentAndActive = false;

       if ($moduleKeyName) {
           if (!$this->_moduleList) {
               $this->_moduleList = Mage::getConfig()->getNode('modules')->children();
           }

            //echo Mage::helper('CrmTicket/String')->getVarDumpInString($this->_moduleList);

            if (array_key_exists($moduleKeyName, $this->_moduleList)) {
                $moduleConf = $this->_moduleList->$moduleKeyName;

                //$moduleConf is a Mage_Core_Model_Config_Element
                if ($moduleConf->is("active", "true")) {
                    $presentAndActive = true;
                }
            }
       }
       return $presentAndActive;
   }
}
