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
class MDN_CrmTicket_Helper_Captcha extends Mage_Core_Helper_Abstract
{

    private $_r1 = null;
    private $_r2 = null;

    public function getRandomOne()
    {
        if (!$this->_r1) {
            $this->_r1 = rand(1, 10);
        }
        return $this->_r1;
    }

    public function getRandomTwo()
    {
        if (!$this->_r2) {
            $this->_r2 = rand(1, 10);
        }
        return $this->_r2;
    }

    //must be called first
    public function getCaptchaDisplay()
    {
        return $this->getRandomOne().' + '.$this->getRandomTwo().' = ';
    }

    //must be called in second
    public function getCaptchaResult()
    {
        return base64_encode(md5($this->_r1+$this->_r2));
    }

    /**
     * Checks if the Md5 of the user response = the encrypted md5 present on the page
     */
    public function checkResult($antiSpamCheck, $antiSpamHumanResult)
    {
        $status = false;

        if (!empty($antiSpamCheck) && !empty($antiSpamHumanResult)) {
            if (md5(trim($antiSpamHumanResult)) == base64_decode(trim($antiSpamCheck))) {
                $status = true;
            }
        }

        return $status;
    }

    public function checkResults($ticketData, $messageData, $customerData, $islogged)
    {
        if (isset($ticketData['anti_spam_check']) && isset($ticketData['anti_spam_human_result'])) {
            //check if the Md5 of the user response = the encrypted md5 present on the page
            if (!empty($ticketData['anti_spam_check']) && !empty($ticketData['anti_spam_human_result'])) {
                $expectedMD5 = base64_decode($ticketData['anti_spam_check']);
                if (!(md5($ticketData['anti_spam_human_result']) == $expectedMD5)) {
                    throw new Exception($this->__("The result is not correct"));
                }

              //if the robot tries to circumvent the protection with random stuff
              if (trim($ticketData['anti_spam_human_result']) == trim($ticketData['anti_spam_check'])) {
                  throw new Exception($this->__("Robots can't post tickets"));
              }
            }
        } else {
            //if no anti spam set, block
          //Redirect on the front contact page
          throw new Exception($this->__("Please fill all fields"));
        }

        //anti spam trick to avoid robots
        //second check : ct_important must be empty
        if (isset($ticketData['ct_important']) && strlen($ticketData['ct_important'])>0) {
            throw new Exception($this->__("Robots can't post tickets"));
        }

        //if the subject and the message are equal, it is very improbable except if it is from a robot
        if (trim($ticketData["ct_subject"]) == trim($messageData['ctm_content'])) {
            throw new Exception($this->__('This message is suspect, Please review your message'));
        }

        //Similar first name and last name is often a robot
        if ($islogged == false) {
            if (trim($customerData["first_name_customer"]) == trim($customerData["last_name_customer"])) {
                throw new Exception($this->__('Please fill a last name and first name different'));
            }
        }
    }
}
