<?php

class Horsebrands_GiftCard_Helper_Data extends Mage_Core_Helper_Abstract {

    const GIFTCARD_ATTRIBUTE_SET_NAME = "Giftcard";
    const XML_PATH_THEME = "customer/giftcard/theme_path";
    const XML_PATH_GIFTCARD_PRODUCTS = "customer/giftcard/giftcard_product_path";
    const XML_PATH_GIFTCARD_TEMPLATE = "customer/giftcard/giftcard_email_template";

    const pdfFontColor = '#1d1d1d';

    const fromLabel = "von";
    const toLabel = "für";


    public function hasGiftcardAttributeSet($product) {
      $productAttributeset = Mage::getModel("eav/entity_attribute_set")->load($product->getAttributeSetId());
      return ($productAttributeset->getAttributeSetName() == self::GIFTCARD_ATTRIBUTE_SET_NAME);
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
        if( $this->hasGiftcardAttributeSet($item->getProduct()) ) {
          return true;
        }
      }

      return false;
    }

    public function getGiftCardItems($order) {
      $giftcardItems = array();

      foreach ($order->getAllItems() as $item) {
        if( $this->hasGiftcardAttributeSet($item->getProduct()) ) {
          array_push($giftcardItems, $item);
        }
      }

      return $giftcardItems;
    }

    public function prepareGiftCard($giftcardBundle, $order, $item, $index) {
      $couponId = $item->getProduct()->getData('giftcard_coupon_code_id');
      $couponcode = $this->generateCouponCode($couponId, $order->getCustomerId());

      Mage::log('Ordernumber: ' . $order->getIncrementId() . ' -- Couponcode: ' . $couponcode, null, 'GIFTCARDS-donot-delete.log');

      $productOptions = $item->getProductOptions();
      $options = $productOptions['options'];

      $giftcardData = $this->getProductOptions($options);
      $giftcardData['amount'] = $item->getProduct()->getAttributeText('giftcard_amount');
      $giftcardData['theme_file'] = $item->getProduct()->getgiftcard_theme_filename();
      $giftcardData['code'] = $couponcode;
      $giftcardData['orderid'] = $order->getIncrementId();
      $giftcardData['itemid'] = $item->getId();

      // create PDF
      $this->generateGiftCardAsPdf($giftcardData, $order->getCustomerId());
      array_push($giftcardBundle, $giftcardData);

      return $giftcardBundle;
    }

    public function generateGiftCardAsPdf(&$vars, $customerId) {
      $path = Mage::getStoreConfig(self::XML_PATH_THEME) . $vars['theme_file'];
      $pdf = new Zend_Pdf($path, null, true);
      $dateGiftCard = date('d.m.Y');
      $dateFilename = date('Y-m-d');

      $page = $pdf->pages[0];
      // Set font
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12)
          ->setFillColor(Zend_Pdf_Color_Html::color(self::pdfFontColor));

      // $page->drawText( TEXT , X, Y);
      $page->drawText($vars['amount'], 157, 50, 'UTF-8');
      $page->drawText($vars['code'], 251, 50, 'UTF-8');

      $page->drawText($vars['to'], 245, 140, 'UTF-8');
      $page->drawText($vars['from'], 245, 110, 'UTF-8');

      $filename = $vars['orderid'].'-'.$vars['code'].'_'.$dateFilename.'.pdf';
      $path = Mage::getStoreConfig(self::XML_PATH_GIFTCARD_PRODUCTS) . $filename;
      $pdf->save($path);
      $vars["pathToPdf"] = $path;
    }

    public function sendGiftCardToReceiverBundle($allItemsData, $order) {
        $storeId = $order->getStoreId();
        $templateId = Mage::getStoreConfig(self::XML_PATH_GIFTCARD_TEMPLATE);
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
        $vars["order_id"] = $order->getIncrementId();

        $sender = array('name' => 'Horsebrands Service',
        'email' => 'service@horsebrands.de');

        $name = $customer->getName();

        try {
            $mailTemplate
                // ->addBcc('jochen.haget@horsebrands.de')
                ->sendTransactional($templateId, $sender, $order->getCustomerEmail(), $name, $vars, $storeId);
            // $mailTemplate->setTemplateSubject($mailSubject)
            //     ->sendTransactional($templateId, $sender, 'dennis.niederrainer@gmail.com', $name, $vars, $storeId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function generateCouponCode($couponId, $customerid) {
      // Get coupon rule
      $rule = Mage::getModel('salesrule/rule')->load($couponId);
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

      // TODO: generate customer coupon
      // //manifest coupon to customer
      // $customercoupon = Mage::getModel('coupon/coupon');
      //
      // $customercoupon->setCustomerId($customerid);
      // $customercoupon->setCouponId($coupon->getId());
      // //coupontype eigentlich für invitefriend, wurde geworben/hat geworben
      // //zusätzlich type = 2 für geschenkgutscheine
      // $customercoupon->setCouponType(2);
      // $customercoupon->setInviteFriendId(0);
      //
      // $customercoupon->save();

      return $coupon->getCode();
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
