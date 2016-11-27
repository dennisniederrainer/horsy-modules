<?php

class Horsebrands_Reporting_Block_Adminhtml_Renderer_FullName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $firstname = $row->getData('firstname');
        $middlename = $row->getData('middlename');
        $lastname = $row->getData('lastname');
        
        $fullname = '';
        
        if($firstname) {
           $fullname .= $firstname;
        }
        if($middlename) {
           $fullname .= ' '.$middlename;
        }
        if($lastname) {
           $fullname .= ' '.$lastname;
        }
        return $fullname;
    }
}
