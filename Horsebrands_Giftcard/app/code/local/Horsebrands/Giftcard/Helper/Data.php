<?php

class Horsebrands_GiftCard_Helper_Data extends Mage_Core_Helper_Abstract {

    const SALES_RULE_25_ID = 107;
    const SALES_RULE_50_ID = 108;
    const SALES_RULE_100_ID = 109;

    const GIFTCARD_ATTRIBUTE_SET_ID = 186;

    const giftcardURI = '/html/horsebrands-prod/giftcards/';
    const pathToPdfTemplate = '/html/horsebrands-prod/giftcards/templates/horsebrands_gutschein_template_individualisierbar.pdf';
    const pdfFontColor = '#521515';

    const fromLabel = "von";
    const toLabel = "an";

    public function hasGiftcardAttributeSet($product) {
        return ($product->getAttributeSetId() == self::GIFTCARD_ATTRIBUTE_SET_ID);
    }

    public function hasGiftCardItemInQuote($quote) {
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            if( $this->hasGiftcardAttributeSet($item->getProduct()) ) {
                return true;
            }
        }

        return false;
    }

    public function hasGiftCardItemInOrder($order) {
        foreach ($order->getAllItems() as $item) {
            if($item->getProduct()->getAttributeSetId() == self::GIFTCARD_ATTRIBUTE_SET_ID) {
                return true;
            }
        }

        return false;
    }

    public function getGiftCardItems($order) {
        $giftcardItems = array();

        foreach ($order->getAllItems() as $item) {
            if($item->getProduct()->getAttributeSetId() == self::GIFTCARD_ATTRIBUTE_SET_ID) {
                array_push($giftcardItems, $item);
            }
        }

        return $giftcardItems;
    }

    public function prepareGiftCard($array, $order, $item, $index) {
        $price = Mage::helper('tax')->getPrice($item->getProduct(), $item->getProduct()->getFinalPrice());
        // $code = $this->generateCouponCode($item->getPrice(), $order->getCustomerId());
        $code = $this->generateCouponCode($price, $order->getCustomerId());

        $productOptions = $item->getProductOptions();
        $options = $productOptions['options'];

        $giftcardData = $this->getProductOptions($options);
        // $giftcardData['amount'] = Mage::app()->getLocale()->currency('EUR')->toCurrency($item->getPrice());
        $giftcardData['amount'] = Mage::app()->getLocale()->currency('EUR')->toCurrency($price);
        $giftcardData['code'] = $code;
        $giftcardData['orderid'] = $order->getId();
        $giftcardData['itemid'] = $item->getId();

        // create PDF
        $this->generateGiftCardAsPdf($giftcardData, $order->getCustomerId(), $index);
        array_push($array, $giftcardData);

        return $array;
    }

    public function generateCouponCode($amount, $customerid) {
      $salesruleid = 0;

      switch (intval($amount)) {
          case 25:
              $salesruleid = self::SALES_RULE_25_ID;
              break;
          case 50:
              $salesruleid = self::SALES_RULE_50_ID;
              break;
          case 100:
              $salesruleid = self::SALES_RULE_100_ID;
              break;
          default:
              throw new Exception('No Salesrule for amount: '.intval($amount));
      }

      // Get coupon rule
      $rule = Mage::getModel('salesrule/rule')->load($salesruleid);
      // Define a coupon code generator model instance
      $generator = Mage::getModel('salesrule/coupon_massgenerator');

      $parameters = array(
          'format' => 'alphanumeric',
          'dash_every_x_characters' => 0,
          'prefix' => '',
          'suffix' => '',
          'length' => 12
      );

      if (!empty($parameters['format'])) {
          switch (strtolower($parameters['format'])) {
              case 'alphanumeric':
              case 'alphanum':
                  $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
                  break;
              case 'alphabetical':
              case 'alpha':
                  $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL);
                  break;
              case 'numeric':
              case 'num':
                  $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC);
                  break;
          }
      }

      $generator->setDash(!empty($parameters['dash_every_x_characters']) ? (int) $parameters['dash_every_x_characters'] : 0);
      $generator->setLength(!empty($parameters['length']) ? (int) $parameters['length'] : 12);
      $generator->setPrefix(!empty($parameters['prefix']) ? $parameters['prefix'] : '');
      $generator->setSuffix(!empty($parameters['suffix']) ? $parameters['suffix'] : '');

      // Set the generator, and coupon type so it's able to generate
      $rule->setCouponCodeGenerator($generator);
      $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

      $coupon = $rule->acquireCoupon();
      $coupon->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED);
      // $coupon->setExpirationDate(date("Y-m-d ", strtotime("+1 month", time())));
      $coupon->save();

      //manifest coupon to customer
      $customercoupon = Mage::getModel('coupon/coupon');

      $customercoupon->setCustomerId($customerid);
      $customercoupon->setCouponId($coupon->getId());
      //coupontype eigentlich für invitefriend, wurde geworben/hat geworben
      //zusätzlich type = 2 für geschenkgutscheine
      $customercoupon->setCouponType(2);
      $customercoupon->setInviteFriendId(0);

      $customercoupon->save();

      return $coupon->getCode();
    }

    public function generateGiftCardAsPdf(&$vars, $customerId, $giftcardIndex = 1) {
      $pdf = new Zend_Pdf(self::pathToPdfTemplate, null, true);
      $dateGiftCard = date('d.m.Y');
      $dateFilename = date('Y-m-d');

      $page = $pdf->pages[0];
      // Set font
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12)
          ->setFillColor(Zend_Pdf_Color_Html::color(self::pdfFontColor));

      $page->drawText($vars['to'], 159, 299, 'UTF-8');
      $page->drawText(substr($vars['orderid'], -5), 447, 299, 'UTF-8');

      $page->drawText($vars['from'], 159, 254, 'UTF-8');
      $page->drawText($dateGiftCard, 402, 254, 'UTF-8');

      // $page->drawText($vars['amount'] . ' Euro', 159, 125);
      $page->drawText($vars['amount'], 165, 125, 'UTF-8');
      $page->drawText($vars['code'], 373, 125, 'UTF-8');

      $path = '/html/horsebrands-prod/giftcards/'.$dateFilename.'_'.$vars['orderid'].'-'.$vars['code'].'.pdf';
      $pdf->save($path);
      $vars["pathToPdf"] = $path;
    }

    public function sendGiftCardToReceiverBundle($allItemsData, $order) {
        $storeId = Mage::app()->getStore()->getId();
        $templateId = 'horsebrands_email_giftcard_template';
        $mailTemplate = Mage::getModel('core/email_template');

        $i = 1;
        foreach ($allItemsData as $data) {
            if (array_key_exists("pathToPdf", $data) && file_exists($data["pathToPdf"])) {

                $filename = 'Horsebrands_Geschenkgutschein_'.intval($data["amount"]).'Euro_'.$i.'.pdf';

                $mailTemplate
                    ->getMail()
                    ->createAttachment(
                        file_get_contents($data["pathToPdf"]),
                        'text/csv',
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $filename
                    );

                $i++;
            }
        }

        $vars = array();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $vars["customer_is_female"] = ($customer->getPrefix() === 'Frau');
        $vars["customer_firstname"] = $customer->getFirstname();
        $mailSubject = 'Geschenkgutscheine für Bestellung #' . $order->getIncrementId();

        $sender = array('name' => 'Horsebrands Gutscheinservice',
        'email' => 'noreply@horsebrands.de');

        $name = $customer->getName();

        try {
            $mailTemplate->setTemplateSubject($mailSubject)
                ->addBcc('jochen.haget@horsebrands.de')
                ->sendTransactional($templateId, $sender, $order->getCustomerEmail(), $name, $vars, $storeId);
            // $mailTemplate->setTemplateSubject($mailSubject)
            //     ->sendTransactional($templateId, $sender, 'dennis.niederrainer@gmail.com', $name, $vars, $storeId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function getProductOptions($options) {
      $vars = array();

      foreach ($options as $option) {
        $value = $option['value'];

        switch (strtolower($option['label'])) {
          case strtolower(self::fromLabel):
              $vars['from'] = $value;
              break;
          case strtolower(self::toLabel):
              $vars['to'] = $value;
              break;
          default:
              Mage::log('label: ' . $option['label'] . ' did not fit a match.', null, 'giftcard_getproductoptions.log');
              break;
        }
      }

      return $vars;
    }
}
