<?php

class MDN_Orderpreparation_Model_Pdf_OrderPreparationCommentsPdf extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {

    protected $_currentOrderId = null;

    public function getPdf($order = array()) {

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        $this->_currentOrderId = $order->getincrement_id();

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        //cree la nouvelle page
        $titre = mage::helper('purchase')->__('Order #') . $order->getincrement_id();
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = $order->getStoreId();
        $page = $this->NewPage($settings);

        //cartouche
        $txt_date = "Date :  " . mage::helper('core')->formatDate($order->getCreatedAt(), 'long');
        $txt_order = '';
        
        //$adresse_fournisseur = Mage::getStoreConfig('sales/identity/address');
        $customer = mage::getmodel('customer/customer')->load($order->getCustomerId());
        $adresse_client = mage::helper('purchase')->__('Shipping Address') . ":\n" . $this->FormatAddress($order->getShippingAddress(), '', false, $customer->gettaxvat());
        $adresse_fournisseur = mage::helper('purchase')->__('Billing Address') . ":\n" . $this->FormatAddress($order->getBillingAddress(), '', false, $customer->gettaxvat());
        $this->AddAddressesBlock($page, $adresse_fournisseur, $adresse_client, $txt_date, $txt_order);

        //Rajoute le carrier et la date d'expe pr�vue & les commentaires
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        $this->y -=15;
        $page->drawText(mage::helper('purchase')->__('Shipping') . ' : ' . $order->getShippingDescription(), 15, $this->y, 'UTF-8');
        $this->y -=15;
        $comments = $this->WrapTextToWidth($page, $order->getmdn_comments(), 550);
        $offset = $this->DrawMultilineText($page, $comments, 15, $this->y, 10, 0.2, 11);
        $this->y -=10 + $offset;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //affiche l'entete du tableau
        $this->drawTableHeader($page);
        $this->y -=10;

        //get items
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $items = Mage::helper('Orderpreparation/Shipment')->GetItemsToShipAsArray($order->getId(), $preparationWarehouseId, $operatorId);
        
        //if array is empty, include all products
        if (count($items) == 0)
        {
            foreach($order->getAllItems() as $orderItem)
            {
                $items[$orderItem->getId()] = $orderItem->getqty_ordered();
            }
        }
        
        //Affiche le r�cap des produits
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        foreach ($items as $orderItemId => $qty) {
            
            if ($qty == 0)
                continue;
            
            //Load product
            $item = mage::getModel('sales/order_item')->load($orderItemId);
            $product = mage::getModel('catalog/product')->load($item->getproduct_id());

            //Does not display products that dont manage stocks
            if (!$product->getStockItem()->ManageStock())
                continue;

            //add product picture
            if ($product->getSmallImage()) {
                $picturePath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
                if (file_exists($picturePath)) {
                    try {
                        $zendPicture = Zend_Pdf_Image::imageWithPath($picturePath);
                        $page->drawImage($zendPicture, 10, $this->y - 15, 10 + 30, $this->y - 15 + 30);
                    } catch (Exception $ex) {
                        //nothing
                    }
                }
            }

            //add product barcode
            $barcode = mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product);
            if ($barcode) {
                $picture = mage::helper('AdvancedStock/Product_Barcode')->getBarcodePicture($barcode);
                if ($picture) {
                    $zendPicture = $this->pngToZendImage($picture);
                    $page->drawImage($zendPicture, 60, $this->y - 15, 60 + 80, $this->y - 15 + 30);
                }
            }

            //dessine
            $page->drawText($this->TruncateTextToWidth($page, $product->getSku(), 70), 160, $this->y, 'UTF-8');
            $page->drawText($item->getShelfLocation(), 230, $this->y, 'UTF-8');

            $name = $this->WrapTextToWidth($page, $product->getName(), 250);
            $offset = $this->DrawMultilineText($page, $name, 300, $this->y, 10, 0.2, 11);

            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 18);
            $page->drawText((int) $item->getqty_ordered(), 540, $this->y, 'UTF-8');

            //rajoute les commentaires
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 8);
            $this->y -= $this->_ITEM_HEIGHT;
            $caption = $this->WrapTextToWidth($page, $item->getcomments(), 300);
            $offset = $this->DrawMultilineText($page, $caption, 200, $this->y, 10, 0.2, 11);
            $this->y -= $offset;

            //rajoute une ligne de s�paration
            $page->setLineWidth(0.5);
            $page->drawLine(10, $this->y - 4, $this->_BLOC_ENTETE_LARGEUR, $this->y - 4);
            $this->y -= $this->_ITEM_HEIGHT;

            //si on a plus la place de rajouter le footer, on change de page
            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }
        }

        //dessine le pied de page
        $this->drawFooter($page);

        //rajoute la pagination
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

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
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);

        $page->drawText(mage::helper('purchase')->__('Sku'), 160, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Location'), 230, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Name'), 300, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Quantity'), 530, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

  /**
     * Dessine l'entete de la page
     */
    public function drawHeader(&$page, $title, $StoreId = null) {
        //fond de l'entete
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $page->drawRectangle(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y - $this->_BLOC_ENTETE_HAUTEUR, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        // insert le logo
        $this->insertLogo($page, $StoreId);

        //rajoute l'adresse et coordon�es dans l'entete
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        $this->DrawMultilineText($page, Mage::getStoreConfig('purchase/general/header_text'), 300, $this->y - 10, 10, 0, 15);

        //barre grise sous le bloc d'entete
        $this->y -= $this->_BLOC_ENTETE_HAUTEUR + 5;
        $page->setLineWidth(1.5);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.1));
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //nom de l'objet
        $this->y -= 35;
        $name = $title;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 24);
        $this->drawTextInBlock($page, $name, 10, $this->y, $this->_PAGE_WIDTH, 50, 'l');

        //draw order barcode
        if (class_exists('Zend_Barcode'))
        {
            $barcodeOptions = array('text' => $this->_currentOrderId);
            $rendererOptions = array();
            $factory = Zend_Barcode::factory(
                    'Code128', 'image', $barcodeOptions, $rendererOptions
            );
            $image = $factory->draw();
            $zendPicture = $this->pngToZendImage($image);
            $barcodeWidth = 150;
            $barcodeHeight = 35;
            $page->drawImage($zendPicture, $this->_BLOC_ENTETE_LARGEUR - $barcodeWidth, $this->y - 10, $this->_BLOC_ENTETE_LARGEUR, $this->y - 10 + $barcodeHeight);
        }
        
        //barre grise sous le titre
        $this->y -= 20;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
    }


    protected function pngToZendImage($pngImage) {
        //save png image to disk
        $path = Mage::getBaseDir() . DS . 'var' . DS . 'barcode_image.png';
        imagepng($pngImage, $path);

        //create zend picture
        $zendPicture = Zend_Pdf_Image::imageWithPath($path);

        //delete file
        unlink($path);

        //return
        return $zendPicture;
    }

}

