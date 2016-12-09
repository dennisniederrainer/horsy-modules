<?php
/**
 * User: Vitali Fehler
 * Date: 01.07.13
 */
class Horsebrands_NewsletterAdvanced_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'horsebrands_newsletteradvanced';
        $this->_controller = 'adminhtml_type';
        $this->_headerText = $this->__('Newsletter Listen');

        parent::__construct();
    }
}
?>