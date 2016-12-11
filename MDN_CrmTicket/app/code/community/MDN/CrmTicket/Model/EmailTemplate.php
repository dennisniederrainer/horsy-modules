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
class MDN_CrmTicket_Model_EmailTemplate extends Mage_Core_Model_Email_Template {

    const XML_PATH_TEMPLATE_EMAIL = 'global/template/email';
    const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';
    const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';
    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';
   

    /**
     * Send mail to recipient with the ability to send attachement
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     * */
    public function send($email, $name = null, array $variables = array()) { 

        $emails = array_values((array) $email);
        $names = is_array($name) ? $name : (array) $name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        $tos = '';
        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
            $tos .= $email.', ';
        }

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $subject = $this->getProcessedTemplateSubject($variables);
        if (!$subject) {
            $subject = ' ';
        }
        //$encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'Q');
        $encodedSubject = '=?utf-8?B?' . base64_encode($subject) . '?=';
        $mail->setSubject($encodedSubject);
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
        $mail->setReturnPath($this->getSenderEmail());
        $mail->setReplyTo($this->getSenderEmail());

        //add attachements from ticket to the mail
        $attachmentCount = 0;
        if (array_key_exists('attachements', $variables)) {

            foreach ($variables['attachements'] as $att) {
                //$contentType = $att->getContentType();//fix 23 05 2013 : improve portability
                $fileName = $att->getFileName();
                if ($fileName /*&& $contentType*/) {
                    $pj = $mail->createAttachment($att->getContent());
                    $pj->filename = $fileName;
                    $attachmentCount++;
                }
            }
        }

        try {
            $mail->send();
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
        }

        //Log in a file all Sent mail with Crm ticket with a preview of subject and body
        $previewSize = 500;
        $mailLog  =  "\n"."\n".date('Y-m-d H:i:s').' : Sent to '.$tos. ' from '.$this->getSenderEmail().' with '.$attachmentCount.' file(s)';
        try{
            $mailLog .= "\n".'Subject='.substr(Mage::helper('CrmTicket/String')->prepareForLog($subject), 0, $previewSize);
            $mailLog .= "\n".'Body='.substr(Mage::helper('CrmTicket/String')->prepareForLog($text), strlen(MDN_CrmTicket_Model_Email_EmailToTicket::FLAG_RESPONSE)+1, $previewSize);
        }catch(Exception $ex){
            //ignore
        }

        mage::log($mailLog, null, 'crm_ticket_sent_mail.log');

        return true;
    }

    

}
