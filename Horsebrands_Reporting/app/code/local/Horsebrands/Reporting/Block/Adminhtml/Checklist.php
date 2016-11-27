<?php
class Horsebrands_Reporting_Block_Adminhtml_Checklist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'reporting';
        $this->_controller = 'adminhtml_checklist';
        $this->_headerText = Mage::helper('reporting')->__('Prüfliste - Bestellabwicklung');
        parent::__construct();
        $this->_removeButton('add');
    }
}
