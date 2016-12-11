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
class MDN_CrmTicket_Admin_EmailController extends Mage_Adminhtml_Controller_Action {

    public function GridAction() {
        $this->loadLayout();

        $this->_setActiveMenu('crmticket');
        $this->getLayout()->getBlock('head')->setTitle($this->__('CRM Saved Emails'));

        $this->renderLayout();
    }

    public function EditAction() {

        //bulk display parsed

        try
        {
          $emailId = $this->getRequest()->getParam('ctm_id');
          $debugMode = $this->getRequest()->getParam('dbg');
          $email = Mage::getModel('CrmTicket/Email')->load($emailId);

          $result = $email->extractInfosFromRawMail();

            if ($result) {

                echo '<p><b>From :</b>' . $result->fromEmail . ' : ' . $result->fromName . '</p>';
                echo '<p><b>To : </b>' . $result->to . '</p>';
                echo '<p><b>Subject :</b> ' . $result->subject . '</p>';
                if($debugMode){
                  echo '<p>Subject Normalized : ' . Mage::helper('CrmTicket/String')->normalize($result->subject) . '</p>';
                }
                echo '<p><b>Body :</b></p>';
                echo $result->response;

                if($debugMode){
                  echo '<br/><p>Final Response Inserred : </p>';
                  $model = Mage::getModel('CrmTicket/Email_EmailToTicket');
                  $response = $model->removeResponsesFromPreviousTicket($result->response);
                  $response = trim(strip_tags($response, '<p><br>'));
                  $response = $model->consolidateUnclosedTags($response);
                  echo $response;

                  Mage::helper('CrmTicket/String')->getVarDumpInString($result,'Mail');

                  //try to link
                  $ticket = Mage::getModel('CrmTicket/Email_EmailToTicket_TicketDefiner')->getTicket($result);
                  
                  if($ticket){
                    Mage::helper('CrmTicket/String')->getVarDumpInString($ticket,'Linked to ticket :');
                  }
                }

            } else {
                echo 'Unable to extract email !';
            }

        }
        catch(Exception $ex){
          echo '<br/>Exception :<pre>'.$ex.'</pre>';
        }
    }

    /**
     * Downlaod all new mails from all active mail boxes
     */
    public function GetNewMailsAction() {
        try
        {
            $message = mage::helper('CrmTicket/Mail')->getNewEmails();
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
        }
        $this->_redirect('CrmTicket/Admin_Email/Grid');
    }


    /**
     * 
     */
    public function ConvertNewMailAction() {
        try
        {
            $count = Mage::getModel('CrmTicket/Email')->processNewEmails();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s emails converted to ticket', $count));
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('%s', $ex->getMessage()));
        }        
        $this->_redirect('CrmTicket/Admin_Email/Grid');
    }
    
    /**
     * Mass action : associate mail to ticket
     */
    public function MassAssociateToTicketAction()
    {
        $ids = $this->getRequest()->getPost('ctm_ids');

        foreach($ids as $id)
        {
            $mail = Mage::getModel('CrmTicket/Email')->load($id);
            $ignoreSpam = true;
            $mail->convertToTicket($ignoreSpam);
        }        

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Emails processed'));
        $this->_redirect('*/*/Grid');
    }

    public function PurgeMailTableAction()
    {
      mage::getModel('CrmTicket/Observer')->cleanBigSavedMails();

      Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Table mail cleaned'));

      $this->_redirect('adminhtml/system_config/edit', array('section' => 'crmticket'));
    }

    public function addToEmailSpamListAction(){
        
        $request = $this->getRequest();
        $email = $request->getParam('email');
        $ticketId = $request->getParam('ticket_id');

        mage::helper('CrmTicket/Mail')->addToEmailSpamList($email);

        $ticket = Mage::getModel('CrmTicket/Ticket')->load($ticketId);
        $ticket->close();
        
        Mage::getSingleton('adminhtml/session')->addSuccess($email.' '.$this->__('marked as spam'));
        
        //go back to the good place
        $referer = $request->getOriginalRequest()->getHeader('Referer');
        if ($referer && strpos($referer, '/Edit/') > 0) {
            $this->_redirect('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $ticketId));
        } elseif (strpos($referer, '/My/') > 0) {
            $this->_redirect('CrmTicket/Admin_Ticket/My');
        } else {
            $this->_redirect('CrmTicket/Admin_Ticket/Grid');
        }


    }

}