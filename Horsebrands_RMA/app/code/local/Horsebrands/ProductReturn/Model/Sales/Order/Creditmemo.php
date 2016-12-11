<?php

class Horsebrands_ProductReturn_Model_Sales_Order_Creditmemo extends MDN_AdvancedStock_Model_Sales_Order_Creditmemo {

	/**
   * Rewrite the Core Creditmemo to grant public access to set state to 'pending'
   *
   * @return
   */
	public function setPendingState() {
		$this->setState(self::STATE_OPEN);
	}
	public function setRefundedState() {
		$this->setState(self::STATE_REFUNDED);
	}

	/**
   * Send email with creditmemo data and set State to Refunded
   *
   * @param boolean $notifyCustomer
   * @param string $comment
   * @return Mage_Sales_Model_Order_Creditmemo
   */
  public function sendEmail($notifyCustomer = true, $comment = '', $emailTemplateId = false)
  {
      $order = $this->getOrder();
      $storeId = $order->getStore()->getId();

      if (!Mage::helper('sales')->canSendNewCreditmemoEmail($storeId)) {
          return $this;
      }
      // Get the destination email addresses to send copies to
      $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
      $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
      // Check if at least one recepient is found
      if (!$notifyCustomer && !$copyTo) {
          return $this;
      }

      // Start store emulation process
      $appEmulation = Mage::getSingleton('core/app_emulation');
      $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

      try {
          // Retrieve specified view block from appropriate design package (depends on emulated store)
          $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
              ->setIsSecureMode(true);
          $paymentBlock->getMethod()->setStore($storeId);
          $paymentBlockHtml = $paymentBlock->toHtml();
      } catch (Exception $exception) {
          // Stop store emulation process
          $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
          throw $exception;
      }

      // Stop store emulation process
      $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

      // Retrieve corresponding email template id and customer name
      if($emailTemplateId == false) {
          if ($order->getCustomerIsGuest()) {
              $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
              $customerName = $order->getBillingAddress()->getName();
          } else {
              $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
              $customerName = $order->getCustomerName();
          }
      } else {
          $templateId = $emailTemplateId;
          $customerName = $order->getCustomerName();
      }

      $mailer = Mage::getModel('core/email_template_mailer');
      if ($notifyCustomer) {
          $emailInfo = Mage::getModel('core/email_info');
          $emailInfo->addTo($order->getCustomerEmail(), $customerName);
          if ($copyTo && $copyMethod == 'bcc') {
              // Add bcc to customer email
              foreach ($copyTo as $email) {
                  $emailInfo->addBcc($email);
              }
          }
          $mailer->addEmailInfo($emailInfo);
      }

      // Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
      if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
          foreach ($copyTo as $email) {
              $emailInfo = Mage::getModel('core/email_info');
              $emailInfo->addTo($email);
              $mailer->addEmailInfo($emailInfo);
          }
      }

      $templateParams = array(
          'order'        => $order,
          'creditmemo'   => $this,
          'comment'      => $comment,
          'billing'      => $order->getBillingAddress(),
          'payment_html' => $paymentBlockHtml
      );

      if($emailTemplateId != false) {
          $getCustomerId = $order->getCustomerId();
          $customer = Mage::getModel('customer/customer')->load($getCustomerId);

          $returnParams = array(
              'customer_firstname'    => $customer->getFirstname(),
              'customer_is_female'    => ($customer->getPrefix() === 'Frau'),
              'order_id'              => $order->getincrement_id(),
          );

          $templateParams = array_merge($templateParams, $returnParams);
      }


      // Set all required params and send emails
      $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
      $mailer->setStoreId($storeId);
      $mailer->setTemplateId($templateId);
      $mailer->setTemplateParams($templateParams);
      $mailer->send();
      $this->setEmailSent(true);
      $this->_getResource()->saveAttribute($this, 'email_sent');

      // set state to refunded
      $this->setRefundedState();
      $this->save();

      return $this;
  }
}
