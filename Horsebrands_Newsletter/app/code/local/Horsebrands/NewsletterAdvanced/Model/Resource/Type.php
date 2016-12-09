<?php
/**
 * User: Vitali Fehler
 * Date: 27.06.13
 */
class Horsebrands_NewsletterAdvanced_Model_Resource_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    /**
     * Name of subscriber link DB table
     *
     * @var string
     */

    protected function _construct()
    {
        $this->_init('newsletteradvanced/type', 'type_id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }
}
?>