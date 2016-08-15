<?php
require_once(Mage::getModuleDir('controllers','Mage_Checkout').DS.'OnepageController.php');

class Horsebrands_Rewrites_Checkout_OnepageController extends Mage_Checkout_OnepageController {

  /**
   * neue saveBillingAction Methode, die Billing und Shipping-Daten beachtet
   */
  public function saveBillingAction($presave = false) {
    if ($this->_expireAjax()) {
        return;
    }

    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost('billing', array());
      $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

      if (isset($data['email'])) {
          $data['email'] = trim($data['email']);
      }

      $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

      if (!isset($result['error'])) {
        if($presave) {
          return true;
        }

        /* check quote for virtual */
        if ($this->getOnepage()->getQuote()->isVirtual()) {
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } else {
            $this->getOnepage()->saveShipping($data, $customerAddressId);
            // Mage::dispatchEvent('cart_updateShipping', array());
            $method = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getShippingMethod();

            if($method) {
                $this->getOnepage()->saveShippingMethod($method);
                $this->getOnepage()->getQuote()->collectTotals()->save();

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            } else {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );

                $result['allow_sections'] = array('billing');
            }

            $result['duplicateBillingInfo'] = 'true';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
      }
    }
  }

  public function saveShippingAction() {
    if ($this->_expireAjax()) {
      return;
    }

    if ($this->getRequest()->isPost() && $this->saveBillingAction(true)) {
      $data = $this->getRequest()->getPost('shipping', array());
      $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);

      $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

      if (!isset($result['error'])) {
          $method = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getShippingMethod();
          if($method) {
              $this->getOnepage()->saveShippingMethod($method);
              $this->getOnepage()->getQuote()->collectTotals()->save();

              $result['goto_section'] = 'payment';
              $result['update_section'] = array(
                  'name' => 'payment-method',
                  'html' => $this->_getPaymentMethodsHtml()
              );
          } else {
              $result['goto_section'] = 'shipping_method';
              $result['update_section'] = array(
                  'name' => 'shipping-method',
                  'html' => $this->_getShippingMethodsHtml()
              );
          }
          $result['allow_sections'] = array('billing');
      }

      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
  }
}
