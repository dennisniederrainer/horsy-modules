<?php

class Horsebrands_Rewrites_Model_MDN_Purchase_Pdf_Pdfhelper extends MDN_Purchase_Model_Pdf_Pdfhelper {
  protected $_MARGIN = 25;

  protected function insertLogo(&$page, $StoreId = null) {
      $image = Mage::getStoreConfig('sales/identity/logo', $StoreId);
      if ($image) {
          $image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
          if (is_file($image)) {
              $image = Zend_Pdf_Image::imageWithPath($image);
              $page->drawImage($image,
                  $this->_PAGE_WIDTH - $this->_MARGIN - $this->_LOGO_LARGEUR,
                  $this->_PAGE_HEIGHT - $this->_MARGIN - $this->_LOGO_HAUTEUR,
                  $this->_PAGE_WIDTH - $this->_MARGIN,
                  $this->_PAGE_HEIGHT - $this->_MARGIN);

              $this->y = $this->_PAGE_HEIGHT - $this->_MARGIN - $this->_LOGO_HAUTEUR;
          }
      }
      return $page;
  }

  public function drawHeader(&$page, $title, $StoreId = null) {
      $this->insertLogo($page, $StoreId);

      $name = $title;
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
      $page->drawText($title, 25, $this->y, 'UTF-8');

      $this->y -= 10;
  }

  public function addOrderInformationBlock(&$page, $supplier, $warehouse, $orderInformation) {
      $info_right_col_start = 130;

      // Order Information
      $this->y -= 20;
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

      $page->drawText('Auftragsnummer', 25, $this->y, 'UTF-8');
      $page->drawText($orderInformation['order_id'], $info_right_col_start, $this->y, 'UTF-8');
      $this->y -= 15;
      $page->drawText('Auftragsdatum', 25, $this->y, 'UTF-8');
      $page->drawText($orderInformation['order_date'], $info_right_col_start, $this->y, 'UTF-8');
      $this->y -= 15;
      $page->drawText('Aktionswoche', 25, $this->y, 'UTF-8');
      $page->drawText($orderInformation['campaign_week_no'], $info_right_col_start, $this->y, 'UTF-8');
      $this->y -= 15;
      $page->drawText('Aktionscode', 25, $this->y, 'UTF-8');
      $page->drawText($orderInformation['campaign_id'], $info_right_col_start, $this->y, 'UTF-8');
      $this->y -= 15;
      $page->drawText('Liefertermin', 25, $this->y, 'UTF-8');
      $page->drawText($orderInformation['delivery_date'], $info_right_col_start, $this->y, 'UTF-8');

      $this->y -= 30;
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
      $page->drawText('Lieferadresse/Shipping Address', 25, $this->y, 'UTF-8');
      $page->drawText('Lieferant/Supplier', $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
      $this->y -= 15;
      $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
      $this->DrawMultilineText($page, $warehouse, 25, $this->y, 10, 0, 16);
      $this->DrawMultilineText($page, $supplier, $this->_PAGE_WIDTH / 2 + 10, $this->y, 10, 0, 16);

      $this->y -= 110;
  }
}
