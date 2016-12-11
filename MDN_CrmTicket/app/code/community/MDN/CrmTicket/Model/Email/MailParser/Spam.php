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
 * @copyright  Copyright (c) 2013 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_CrmTicket_Model_Email_MailParser_Spam extends MDN_CrmTicket_Model_Email_MailParser_Abstract {

  //reliable
  const SPAM_TAG = 'X-Spam-Tag';

  //not reliables
  const PROBABLY_SPAM_TAG = 'X-Probably-Spam-Tag';
  const CHECK_SPAM_TAG = 'X-Spam-Check';

  public function parse(&$emailStructured, $msgObject, &$rawHeader) {

    $debug = array();
    $identifiedAsSpam = false;

    if(Mage::getStoreConfig('crmticket/email_spam/detect_spam_from_header')){

        //X-Spam-Tag Detection
        $spamTag = $this->getValueFromArrayKeysHeadersByKey(self::SPAM_TAG,$rawHeader);
        if (!$spamTag) {
            $spamTag = $this->extractHeaderAlternativeMethods($msgObject, self::SPAM_TAG.':', $rawHeader);
        }

        if ($spamTag) {
          $debug[] = "Mail flaggued as a SPAM by the mail provider " . $spamTag . "<br/>";
          $identifiedAsSpam = true;
        }
    }

    $emailStructured->identifyAsSpam($identifiedAsSpam);
    $debug[] = "Mail is a SPAM " . ($emailStructured->isSpam()?'YES':'NO') . "<br/>";


    Mage::helper('CrmTicket')->log(implode("\n", $debug));
  }

}
