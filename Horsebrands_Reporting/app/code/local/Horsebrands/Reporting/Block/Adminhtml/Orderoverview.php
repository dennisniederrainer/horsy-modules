<?php
class Horsebrands_Reporting_Block_Adminhtml_Orderoverview extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'reporting';
        $this->_controller = 'adminhtml_orderoverview';
        $this->_headerText = Mage::helper('reporting')->__('Übersicht der Bestellungen');
        parent::__construct();
        $this->_removeButton('add');
    }
}