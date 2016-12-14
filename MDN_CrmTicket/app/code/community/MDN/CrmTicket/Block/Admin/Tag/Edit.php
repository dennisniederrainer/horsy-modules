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
class MDN_CrmTicket_Block_Admin_Tag_Edit extends Mage_Adminhtml_Block_Widget_Form {

    private $_tag = null;

    /**
     * Get current tag
     */
    public function getTag() {
         if ($this->_tag == null) {
            $tagId = $this->getRequest()->getParam('ctg_id');
            if(is_numeric($tagId) && $tagId>0){
                $this->_tag = mage::getModel('CrmTicket/Tag')->load($tagId);//edit
            }else{
                $this->_tag = mage::getModel('CrmTicket/Tag'); // new
            }
        }
        return $this->_tag;
        
    }
    
    /**
     *
     * @return type 
     */
    public function getBackUrl() {
        return $this->getUrl('CrmTicket/Admin_Tag/Grid');
    }

    /**
     *
     * @return type 
     */
    public function getDeleteUrl() {
        return $this->getUrl('CrmTicket/Admin_Tag/Delete', array('ctg_id' => $this->getTag()->getId()));
    }

    /**
     *
     * @return type 
     */
    public function getTitle()
    {
        if ($this->getTag()->getId())
            return $this->__('Edit tag');
        else
            return $this->__('New tag');
    }

    
}

