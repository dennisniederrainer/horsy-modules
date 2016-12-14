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
class MDN_CrmTicket_Block_Admin_Object_Popup extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected $_object;

    /**
     * Load object
     */
    public function getObject()
    {
        if ($this->_object == null) {
            $class = Mage::getModel('CrmTicket/Customer_Object')->getClassByType($this->getObjectType());
            $this->_object = $class->loadObject($this->getObjectId());
        }
        return $this->_object;
    }

    public function getStoreName($storeId)
    {
        return Mage::getModel('core/store')->load($storeId)->getName();
    }
}
