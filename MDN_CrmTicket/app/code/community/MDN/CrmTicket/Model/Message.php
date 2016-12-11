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
class MDN_CrmTicket_Model_Message extends Mage_Core_Model_Abstract
{

    const AUTHOR_CUSTOMER = 'customer';
    const AUTHOR_ADMIN = 'admin';

    const CONTENT_TYPE_TEXT = 'text';
    const CONTENT_TYPE_HTML = 'html';

    const TYPE_MAIL = 'mail';
    const TYPE_FORM = 'form';
    const TYPE_PHONE = 'phone';
    const TYPE_FAX = 'fax';
    const TYPE_OTHER = 'other';
    

    //reponse communication channels
    const NOTIFY_EMAIL = 'email';
    const NOTIFY_WEBSERVICE = 'webservice';

    private $_adminUser = null;
    private $_customerName = null;
    private $_ticket = null;

    public function _construct()
    {
        $this->_init('CrmTicket/Message', 'ctm_id');
    }

    /**
     * return the ticket associated to this message
     *
     * @param type $attachment
     */
    public function getTicket()
    {
        if (!$this->_ticket) {
            $this->_ticket = Mage::getModel('CrmTicket/Ticket')->load($this->getctm_ticket_id());
        }

        return $this->_ticket;
    }

    /**
      *
      */
     protected function _afterSave()
     {
         parent::_afterSave();
     }

    /**
     * return a list of attachements for the current message
     *
     * @param type $attachment
     */
    public function getAttachments()
    {
        return Mage::helper('CrmTicket/Attachment')->getAttachmentsForMessage($this->getTicket(), $this);
    }

    /**
     * Return possible entry type for 1 message
     *
     * @return array
     */
    public function getSourceTypes()
    {
        $sources = array();
        $helper = Mage::helper('CrmTicket');
        $sources[self::TYPE_MAIL] = $helper->__('Email');
        //$sources[self::TYPE_FORM] = $helper->__('Form');
        $sources[self::TYPE_PHONE] = $helper->__('Phone call');
        $sources[self::TYPE_FAX] = $helper->__('Fax');
        $sources[self::TYPE_OTHER] = $helper->__('Other');
        return $sources;
    }

    /**
     * Returns if this message source type is allowed to notify customer by email
     *
     * @param type $source
     * @return boolean
     */
    public function isSourceNotify()
    {
        $notify = false;
        switch ($this->getSourceType()) {
        case self::TYPE_MAIL:
        case self::TYPE_FORM:
          $notify = true;
          break;
        default:
          break;
      }
        return $notify;
    }


    /*public function getNotifyChannel(){

      $notifyChannel = self::NOTIFY_EMAIL;

      if(mage::helper('CrmTicket/WebService')->isWebServiceChannel($this)){
        $notifyChannel = self::NOTIFY_WEBSERVICE;
      }

      //Mage::dispatchEvent('crmticket_notification_channel', array('message' => $this));

      //

      return $notifyChannel;
    }
      */



    /**
     * Returns the css style to use
     *
     * @param type $source
     * @return boolean
     */
    public function getCssStyle()
    {
        $css = $this->getctm_author();
      
        switch ($this->getSourceType()) {
        case self::TYPE_PHONE:
          $css = 'phone';
          break;
        case self::TYPE_FAX:
          $css = 'fax';
          break;
        default:
          break;
      }
        return $css;
    }



    /**
     * return message type
     */
    public function getSourceType()
    {
        return $this->getctm_source_type();
    }

    /**
     * Return possible authors for 1 message
     * @return type
     */
    public function getAuthors()
    {
        $authors = array();

        $authors[self::AUTHOR_ADMIN] = self::AUTHOR_ADMIN;
        $authors[self::AUTHOR_CUSTOMER] = self::AUTHOR_CUSTOMER;

        return $authors;
    }

    /**
     * Return admin having wrote the msg
     * @return null
     */
    public function getAdminUser()
    {
        if (!$this->_adminUser) {
            $userId = $this->getctm_admin_user_id();

            if ($userId) {
                $this->_adminUser = Mage::getModel('admin/user')->load($userId);
            }
        }

        return $this->_adminUser;
    }

    public function getCustomerName()
    {
        if (!$this->_customerName) {
            $this->_customerName = $this->getTicket()->getCustomer()->getName();
        }

        return $this->_customerName;
    }
    
    /**
     *
     * @param type $msg
     */
    public function cleanMessage($msg)
    {
        //remove html code
        $msg = str_replace('<script', '', $msg);
        $msg = str_replace('onclick', '', $msg);
        
        return $msg;
    }


    /**
     * Return true if this message should notify customer by email at creation
     *
     * @param type $msg
     */
    public function shouldNotify($type)
    {
        $notify = false;

        switch ($type) {
            case self::TYPE_MAIL:
            case self::TYPE_FORM:
                $notify = true;
                break;
            default:
                break;
        }

        return $notify;
    }

    public function getMessageBody()
    {
        return $this->getctm_content();
    }

    
    public function getAuthorName($displayAdminUserName=false, $displaySourceType=false)
    {
        $authorName = $this->getctm_author();

        //if you want display Admin Author name in public mail and form front
        if ($authorName == self::AUTHOR_ADMIN && $displayAdminUserName) {
            if($this->getAdminUser()){
               $authorName = $this->getAdminUser()->getusername();
            }else{
               // $authorName = 'admin';
              $authorName = 'Horsebrands Kundenservice';
            }
        }

        if ($authorName == self::AUTHOR_CUSTOMER) {
          $authorName = $this->getCustomerName();
        } else {
          $authorName = 'Horsebrands Kundenservice';
        }

        if (Mage::getStoreConfig('crmticket/ticket_data/change_message_type') && $displaySourceType) {
            $authorName = $authorName . ' (' .$this->getSourceType() . ')';
        }

        return $authorName;
    }

    public function getPublicMessageHeader()
    {
        $displayAdminUserName = false;
        $displaySourceType = false;
        return $this->getMessageHeader($this->getAuthorName($displayAdminUserName, $displaySourceType));
    }

    public function getBackofficeMessageHeader()
    {
        $displayAdminUserName = true;
        $displaySourceType = true;
        return $this->getMessageHeader($this->getAuthorName($displayAdminUserName, $displaySourceType));
    }

    private function getMessageHeader($authorName)
    {
        $helper = mage::helper('CrmTicket');
        return $helper->__('By %s, %s',
                $helper->__($authorName),
                Mage::helper('core')->formatDate($this->getctm_updated_at(), 'long', true));
    }
}
