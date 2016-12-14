<?php

class MDN_CrmTicket_Block_Adminhtml_System_Config_Form_Field_Date extends Mage_Adminhtml_Block_System_Config_Form_Field
{

  const DATE_INTERNAL_FORMAT = 'yyyy-MM-dd';

  public function render(Varien_Data_Form_Element_Abstract $element)
  {
    
     $t = explode('.', Mage::getVersion());
     $versionMinor = $t[1];

     //because it is after mage CE 1.5 and for Mage EE so after 10
     if($versionMinor >= 5){
       $element->setFormat(self::DATE_INTERNAL_FORMAT);
     }
     

     $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
     return parent::render($element);
  }
}