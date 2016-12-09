<?php
/**
 * Type model
 *
 * @method Horsebrands_NewsletterAdvanced_Model_Resource_Subscriber _getResource()
 * @method Horsebrands_NewsletterAdvanced_Model_Resource_Subscriber getResource()
 * @method int getStoreId()
 * @method Mage_Newsletter_Model_Subscriber setStoreId(int $value)
 *
 * User: Vitali Fehler
 * Date: 27.06.13
 */

    class Horsebrands_NewsletterAdvanced_Model_Type extends Mage_Core_Model_Abstract
    {
        protected function _construct()
        {
            $this->_init('newsletteradvanced/type');
        }
    }
?>