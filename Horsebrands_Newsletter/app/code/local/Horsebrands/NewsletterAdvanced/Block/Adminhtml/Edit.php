<?php
/**
 * User: Vitali Fehler
 * Date: 01.07.13
 */
class Horsebrands_NewsletterAdvanced_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'horsebrands_newsletteradvanced';
        $this->_controller = 'adminhtml';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Newsletter Liste speichern'));
        $this->_updateButton('delete', 'label', $this->__('Newstletter Liste löschen'));
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('newsletteradvanced_type')->getId()) {
            return $this->__('Newsletter Liste bearbeiten');
        }
        else {
            return $this->__('Neue Newsletter Liste');
        }
    }
}
?>