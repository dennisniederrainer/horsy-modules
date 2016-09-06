<?php

class Horsebrands_Aktionen_Block_Login extends Mage_Core_Block_Template {

  public function getActionUrl($type) {
    if($type=='registration') {
      return $this->helper('customer')->getRegisterPostUrl();
    } elseif($type=='login') {
      return $this->helper('customer')->getLoginPostUrl();
    }

    return '#';
  }
}
