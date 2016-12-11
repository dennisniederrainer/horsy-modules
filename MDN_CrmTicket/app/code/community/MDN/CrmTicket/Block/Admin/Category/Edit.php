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
class MDN_CrmTicket_Block_Admin_Category_Edit extends Mage_Adminhtml_Block_Widget_Form
{

    private $_category = null;
    private $_managers = null;

    /**
     * get current category for editing
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $categoryId = $this->getRequest()->getParam('category_id');

            if ($categoryId) {
                $this->_category = mage::getModel('CrmTicket/Category')->load($categoryId);
            }
        }

        return $this->_category;
    }

    /**
     *
     * @return type
     */
    public function getBackUrl()
    {
        return $this->getUrl('CrmTicket/Admin_Category/Grid');
    }

    /**
     *
     * @return type
     */
    public function getDeleteUrl($categoryId)
    {
        return $this->getUrl('CrmTicket/Admin_Category/Delete', array('category_id' => $categoryId));
    }

    /**
     *
     * @return type
     */
    public function getTypes()
    {
        return mage::getModel('CrmTicket/Category')->getCategoryTypes();
    }

    /**
     * get parent id and name
     * @return type
     */
    public function getAllPossibleParents()
    {
        return mage::getModel('CrmTicket/Category')->getAllCategories();
    }

    /**
     * get the name of the parent categoy
     */
    public function getParentName($categoryId)
    {
        return mage::getModel('CrmTicket/Category')->load($categoryId)->getctc_name();
    }

    /**
     * return all magento users
     */
    public function getManagers()
    {
        if (!$this->_managers) {
            $this->_managers = mage::getSingleton('admin/user')->getCollection();
        }

        return $this->_managers;
    }


    
    /*
     * Return all products
     */
    public function getProducts()
    {
        return Mage::helper('CrmTicket/Product')->getProducts();
    }
    
    public function getBooleans()
    {
        $a = array();
        $a[0] = $this->__('No');
        $a[1] = $this->__('Yes');
        return $a;
    }

    /**
     * Return websites
     * @return type
     */
    public function getWebsiteCollection()
    {
        return Mage::app()->getWebsites();
    }

    /**
     * return groups for one website
     * @param Mage_Core_Model_Website $website
     * @return type
     */
    public function getGroupCollection(Mage_Core_Model_Website $website)
    {
        return $website->getGroups();
    }

    /**
     * Return stores for one group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return type
     */
    public function getStoreCollection(Mage_Core_Model_Store_Group $group)
    {
        return $group->getStores();
    }
}
