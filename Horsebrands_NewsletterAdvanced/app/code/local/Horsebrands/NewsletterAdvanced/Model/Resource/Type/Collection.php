<?php
/**
 * NewsletterAdvanced Type collection
 *
 * @category    Horsebrands
 * @package     Horsebrands_NewsletterAdvanced
 * @author      Vitali Fehler
 */
class Horsebrands_NewsletterAdvanced_Model_Resource_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Store table name
     *
     * @var string
     */
    protected $_storeTable;

    public function _construct()
    {
        $this->_init('newsletteradvanced/type');
        $this->_storeTable = $this->getTable('core/store');
    }
}
?>