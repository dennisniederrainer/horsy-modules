<?php

class Horsebrands_Aktionen_Block_Login extends Mage_Core_Block_Template {

  public function getActionUrl($type) {
    if($type=='registration') {
      return '#';
    } elseif($type=='login') {
      return $this->helper('customer')->getLoginPostUrl();//$this->getUrl('customer/account/loginPost');
    }

    return '#';
  }
}
