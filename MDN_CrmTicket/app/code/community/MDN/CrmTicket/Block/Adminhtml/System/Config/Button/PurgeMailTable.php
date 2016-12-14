<?php

class MDN_CrmTicket_Block_Adminhtml_System_Config_Button_PurgeMailTable extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('CrmTicket/Admin_Email/PurgeMailTable');
        
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel($this->__('Purge it'))
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}