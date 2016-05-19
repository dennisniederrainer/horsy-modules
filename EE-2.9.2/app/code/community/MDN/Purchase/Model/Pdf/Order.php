<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Purchase Order PDF
 *
 */
class MDN_Purchase_Model_Pdf_Order extends MDN_Purchase_Model_Pdf_Pdfhelper {

    private $_showPictures = false;
    private $_pictureSize = 70;

    public function getPdf($orders = array()) {
        $this->initLocale($orders);

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        foreach ($orders as $order) {

            //add new page
            $titre = mage::helper('purchase')->__('Purchase Order');
            $settings = array();
            $settings['title'] = $titre;
            $settings['store_id'] = 0;
            $page = $this->NewPage($settings);

            //page header
            $txt_date = "Date :  " . date('d/m/Y', strtotime($order->getpo_date()));
            $txt_order = "No " . $order->getpo_order_id();
            $adresse_fournisseur = $order->getSupplier()->getAddressAsText();
            $adresse_client = $order->getTargetWarehouse()->getstock_address();
            $this->AddAddressesBlock($page, $adresse_fournisseur, $adresse_client, $txt_date, $txt_order);

            //table header
            $this->drawTableHeader($page);

            $this->y -=10;


            //Display products
            foreach ($order->getProducts() as $item) {

                //font initialization
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

                //display SKU or picture
                if ($this->_showPictures == false) {
                    $sku = $item->getpop_supplier_ref();
                    if ($sku == '')
                        $sku = $item->getsku();
                    $page->drawText($this->TruncateTextToWidth($page, $sku, 95), 15, $this->y, 'UTF-8');
                }
                else {
                    $product = mage::getModel('catalog/product')->load($item->getpop_product_id());
                    if ($product->getId()) {
                        $productImagePath = Mage::getBaseDir() . '/media/catalog/product' . $product->getsmall_image();
                        if (is_file($productImagePath)) {
                            try {
                                $image = Zend_Pdf_Image::imageWithPath($productImagePath);
                                $page->drawImage($image, 10, $this->y - $this->_pictureSize + 20, 5 + $this->_pictureSize, $this->y + 10);
                            } catch (Exception $ex) {
                                
                            }
                        }
                    }
                }

                //name
                $caption = $this->WrapTextToWidth($page, $item->getpop_product_name(), 185);
                if ($item->getpop_packaging_id())
                {
                    $caption .= "\n(".$item->getpop_packaging_name().')';
                }
                $offset = $this->DrawMultilineText($page, $caption, 125, $this->y, 10, 0.2, 11);
                
                //unit price
                if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY)
                    $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getpop_price_ht()), 320, $this->y, 60, 20, 'r');
                
                //qty
                $this->drawTextInBlock($page, (int) $item->getOrderedQty(), 390, $this->y, 40, 20, 'c');
                
                //WEEE, tax rate, row total
                if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {
                    $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getpop_eco_tax()), 425, $this->y, 40, 20, 'c');
                    $this->drawTextInBlock($page, number_format($item->getpop_tax_rate(), 2) . '%', 470, $this->y, 40, 20, 'c');
                    $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getRowTotal()), 500, $this->y, 60, 20, 'r');
                }

                if ($this->_showPictures)
                    $this->y -= $this->_pictureSize;
                else
                    $this->y -= $offset + 20;

                //new page if required
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    $this->drawTableHeader($page);
                }
            }

            //add shipping costs
            if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {
                $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
                $this->DrawMultilineText($page, mage::helper('purchase')->__('Shipping costs'), 125, $this->y, 10, 0.2, 11);
                $this->drawTextInBlock($page, number_format($order->getpo_tax_rate(), 2) . '%', 470, $this->y, 40, 20, 'c');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getShippingAmountHt()), 500, $this->y, 60, 20, 'r');

                //Tax & duties
                $this->y -= $this->_ITEM_HEIGHT;
                $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
                $this->DrawMultilineText($page, mage::helper('purchase')->__('Taxes and Duties'), 125, $this->y, 10, 0.2, 11);
                $this->drawTextInBlock($page, number_format($order->getpo_tax_rate(), 2) . '%', 470, $this->y, 40, 20, 'c');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getZollAmountHt()), 500, $this->y, 60, 20, 'r');
            }

            //new page if required
            if ($this->y < (150)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }

            //grey line
            $this->y -= 10;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
            $VerticalLineHeight = 80;
            $page->drawLine($this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2, $this->y - $VerticalLineHeight);

            //totals font
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 14);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
            $this->y -= 20;

            //Comments
            $comments = $order->getpo_comments();
            if (($comments != '') && ($comments != null)) {
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
                $page->drawText(mage::helper('purchase')->__('Comments'), 15, $this->y, 'UTF-8');
                $comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH / 2);
                $this->DrawMultilineText($page, $comments, 15, $this->y - 15, 10, 0.2, 11);
            }
            if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {
                $page->drawText(mage::helper('purchase')->__('Total (excl tax)'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTotalHt()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
                $this->y -= 20;
                $page->drawText(mage::helper('purchase')->__('Tax'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTaxAmount()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
                $this->y -= 20;
                $page->drawText(mage::helper('purchase')->__('Total (incl tax)'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTotalTtc()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
            }

            $this->y -= 20;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            //Payment & shipping methods
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
            $this->y -= 20;
            $page->drawText(mage::helper('purchase')->__('Billing Method : ') . $order->getpo_payment_type(), 15, $this->y, 'UTF-8');
            $this->y -= 20;
            $page->drawText(mage::helper('purchase')->__('Carrier : ') . $order->getpo_carrier(), 15, $this->y, 'UTF-8');

            $this->y -= 20;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            //Static area
            $this->y -= 20;
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
            $page->drawText(mage::helper('purchase')->__('Comments : '), 15, $this->y, 'UTF-8');
            $this->y -= 20;
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
            $txt = Mage::getStoreConfig('purchase/general/pdf_comment');
            $txt = $this->WrapTextToWidth($page, $txt, $this->_PAGE_WIDTH - 100);
            $this->DrawMultilineText($page, $txt, 15, $this->y, 10, 0.2, 11);

            //Draw footer
            $this->drawFooter($page);
        }

        //Display pages numbers
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        //reset language
        Mage::app()->getLocale()->revert();

        return $this->pdf;
    }

    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        //entetes de colonnes
        $this->y -= 15;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

        $page->drawText(mage::helper('purchase')->__('Sku'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Description'), 125, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Unit Price'), 345, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Qty'), 400, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('WEEE'), 425, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Tax'), 470, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Subtotal'), 530, $this->y, 'UTF-8');
 
        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

    /**
     * init pdf locale depending of supplier locale
     *
     */
    protected function initLocale($orders) {
        //consider only first order
        foreach ($orders as $order) {
            $localeId = $order->getSupplier()->getsup_locale();
            if ($localeId)
                Mage::app()->getLocale()->emulateLocale($localeId);
        }
    }

}