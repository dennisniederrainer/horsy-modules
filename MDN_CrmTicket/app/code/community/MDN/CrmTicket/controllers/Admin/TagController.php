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
class MDN_CrmTicket_Admin_TagController extends Mage_Adminhtml_Controller_Action {

    /**
     * display statistics by user
     */
    public function EditAction() {
        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit tag'));
        $this->renderLayout();
    }

    /**
     * display statistics by category
     */
    public function GridAction() {

        $this->loadLayout();
        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Tag list'));
        $this->renderLayout();
    }

     /**
     *
     */
    public function SaveAction() {

        // get category id
        $id = $this->getRequest()->getPost('ctg_id');

        $data = $this->getRequest()->getPost('tag');

        // load category
        $tag = mage::getModel('CrmTicket/Tag')->load($id);
        foreach ($data as $key => $value) {
            $tag->setData($key, $value);
        }
        $tag->save();

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));

        //Redirect
        $this->_redirect('CrmTicket/Admin_Tag/Edit', array('ctg_id' => $tag->getId()));
    }

    /**
     * delete a tag
     */
    public function DeleteAction() {

        $id = $this->getRequest()->getParam('ctg_id');

        $tag = mage::getModel('CrmTicket/Tag')->load($id);

        if($tag->getId()>0){
            $tag->delete();
        }

        //confirm
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Tag deleted'));

        //Redirect
        $this->_redirect('CrmTicket/Admin_Tag/Grid');
    }


    public function TicketTagAjaxAction() {

        $status = false;
        $html = '';

        $tagId = $this->getRequest()->getParam('ctg_id');
        $ticketId = $this->getRequest()->getParam('ticket_id');
        $action = $this->getRequest()->getParam('action');

        if(!empty($tagId) && !empty($ticketId)){

            if($action == 'add'){
                $status = mage::helper('CrmTicket/Tag')->addTicketTag($tagId,$ticketId);
            }
            if($action == 'delete'){
                $status = mage::helper('CrmTicket/Tag')->deleteTicketTag($tagId,$ticketId);
            }
        }

        if($status){
            $html = Mage::getSingleton('core/layout')->createBlock('CrmTicket/Admin_Ticket_Edit')
                    ->setTemplate('CrmTicket/Ticket/Edit/Tab/Sections/Tags.phtml')
                    ->toHtml();
        }

        echo $html;
    }

    protected function _isAllowed() {
      return true;
    }

}
