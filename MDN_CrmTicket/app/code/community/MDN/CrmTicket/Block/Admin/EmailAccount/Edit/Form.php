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
class MDN_CrmTicket_Block_Admin_EmailAccount_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Class constructor
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('emailAccountForm');
    }

    /**
     * return current email account
     * @return type 
     */
    public function getEmailAccount() {
        return Mage::getModel('CrmTicket/EmailAccount')->load(Mage::registry('cea_id'));
    }


    /**
     * Prepare form data
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {

        $emailAccount = $this->getEmailAccount();
        $helper = Mage::helper('CrmTicket');
        $demo = false;

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ));

        $fieldset = $form->addFieldset('email_account_fieldset', array(
            'legend' => $helper->__('Information')
                ));


        if($demo){
          $fieldset->addField('note', 'note', array(
              'text' => '<b>'.$helper->__('To test the email import of this demo, please send some e-mail to <u>crmexpress_demo@boostmyshop.com</u><br>and they will be created as tickets automatically after a few minutes by a cron job').'</b>'
          ));
        }

        $fieldset->addField('cea_id', 'hidden', array(
            'name' => 'data[cea_id]',
            'label' => $helper->__('Id'),
            'value' => $emailAccount->getId()
        ));

        $fieldset->addField('cea_name', 'text', array(
            'name' => 'data[cea_name]',
            'label' => $helper->__('Account display name'),
            'value' => $emailAccount->getcea_name(),
            'required' => true,
            'disabled' => $demo
        ));

        $fieldset->addField('cea_store_id', 'select', array(
            'name' => 'data[cea_store_id]',
            'label' => $helper->__('Store'),
            'value' => $emailAccount->getcea_store_id(),
            'required' => true,
            'options'  => Mage::getModel('CrmTicket/Email_Main')->getStoresForForm(),
            'disabled' => $demo
        ));
        
        $fieldset->addField('cea_connection_type', 'select', array(
            'name' => 'data[cea_connection_type]',
            'label' => $helper->__('Account type'),
            'value' => $emailAccount->getcea_connection_type(),
            'required' => true,
            'options'  => Mage::getModel('CrmTicket/Email_Main')->getConnectionTypeForForm(),
            'disabled' => $demo
        ));

        $fieldset->addField('cea_use_ssl', 'select', array(
            'name' => 'data[cea_use_ssl]',
            'label' => $helper->__('Use SSL/TLS'),
            'value' => $emailAccount->getcea_use_ssl(),
            'required' => true,
            'options'  => Mage::getModel('CrmTicket/EmailAccount')->getSSLTLS(),
            'disabled' => $demo
        ));

        $fieldset->addField('cea_host', 'text', array(
            'name' => 'data[cea_host]',
            'label' => $helper->__('Incoming server (Host)'),
            'value' => $emailAccount->getcea_host(),
            'required' => true,
            'disabled' => $demo
        ));

        $fieldset->addField('cea_port', 'text', array(
            'name' => 'data[cea_port]',
            'label' => $helper->__('Incoming server port'),
            'value' => $emailAccount->getcea_port(),
            'required' => true,           
            'after_element_html' => '<br/><small>'.$helper->__('Default POP:110, POP-SSL:995, IMAP4:143, IMAP4-SSL:585').'</small>',
            'disabled' => $demo
        ));
        
        $fieldset->addField('cea_login', 'text', array(
            'name' => 'data[cea_login]',
            'label' => $helper->__('Login'),
            'value' => $emailAccount->getcea_login(),
            'required' => true,
            'disabled' => $demo
        ));

        $fieldset->addField('cea_password', 'password', array(
            'name' => 'data[cea_password]',
            'label' => $helper->__('Password'),
            'value' => $emailAccount->getcea_password(),
            'required' => true,
            'disabled' => $demo
        ));        

        $fieldset->addField('cea_signature', 'editor', array(
            'name' => 'data[cea_signature]',
            'label' => $helper->__('Email\'s default signature'),
            'value' => $emailAccount->getcea_signature(),            
            'style'    => 'width:300px; height:250px;',
            'config'   => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'wysiwyg'  => true
        ));

        $fieldset->addField('cea_email', 'text', array(
            'name' => 'data[cea_email]',
            'label' => $helper->__('Custom Email for response'),
            'value' => $emailAccount->getcea_email(),
            'required' => false,
            'after_element_html' => '<br/><small>'.$helper->__("Email to use for response, usefull if you don't want to use the account login").'</small>',
            'disabled' => $demo
        ));

        $fieldset->addField('cea_enabled', 'select', array(
            'name' => 'data[cea_enabled]',
            'type' => 'options',
            'value' => $emailAccount->getcea_enabled(),
            'options' => array(
                1 => $helper->__('Yes'),
                0 => $helper->__('No'),
            ),
            'label' => $helper->__('Enabled'),
            'after_element_html' => '<br/><small>'.$helper->__('Activate this account to enable the cron to import mails in tickets').'</small>'
        ));
        


        //$form->setAction($this->getUrl('*/*/save')); //HACK FOR DEMO
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
