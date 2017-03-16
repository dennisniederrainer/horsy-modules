<?php

class Horsebrands_Invitefriends_CouponsController extends Mage_Core_Controller_Front_Action {

  public function preDispatch()
  {
    parent::preDispatch();
    if (!Mage::getSingleton('customer/session')->authenticate($this)) {
      $this->setFlag('', 'no-dispatch', true);
    }
  }

  public function indexAction()
  {
    $coupons = Mage::helper('invitefriends/coupon')->getCustomerCoupons(Mage::getSingleton('customer/session')->getCustomer());
    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');

    $this->getLayout()->getBlock('customer_coupons')->setCoupons($coupons);
    $this->renderLayout();
  }
}
