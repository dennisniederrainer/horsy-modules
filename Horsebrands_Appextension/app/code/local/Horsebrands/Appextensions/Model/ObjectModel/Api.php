<?php
class Horsebrands_Appextensions_Model_ObjectModel_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getActiveFlashSales()
    {
        $flashSales = Mage::getModel('PrivateSales/FlashSales');
        $collection = $flashSales->getCollection();
        $collection->addFieldToFilter('fs_enabled', '1');
        $collection->setOrder('fs_start_date', 'ASC');
        $collection->load();
        $activeFlashSales = array();

        // Horsebrands:
        // time() returns the current time im milliseconds since January 1 1970 00:00:00 GMT
        // because it's GMT, we have to use the timestamp() method.
        $now = Mage::getModel('core/date')->timestamp(time());
        $collectionArray = $collection->toArray();
        foreach($collectionArray["items"] as $flashSale)
        {
            if($flashSale)
            {
                // $startDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale["fs_start_date"]));
                // $endDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale["fs_end_date"]));

                // Horsebrands: core/date->timestamp(x) geht davon aus, dass Zeit x in GMT Zeitzone ist
                //  Es werden also 2 Stunden addiert und deswegen kam es zum Start- und Endzeiten-Problem der App
                $startDate = strtotime($flashSale["fs_start_date"]);
                $endDate = strtotime($flashSale["fs_end_date"]);

                $flashSale["fs_start_date"] = date("Y-m-d H:i:s", $startDate);
                $flashSale["fs_end_date"] = date("Y-m-d H:i:s", $endDate);
                $category = Mage::getModel("catalog/category")->load($flashSale["fs_category_id"]);
                $flashSale["fs_picture"] = $category->getImageUrl();
                $flashSale["fs_picture_thumbnail"] = Mage::getBaseUrl('media').'catalog/category/'.$category->getThumbnail();
                $flashSale["fs_subtitle"] = $flashSale["fs_description"];
                $flashSale["fs_description"] = $category->getDescription();
                unset($flashSale["fs_target_url"]); // delete this, because we don't need it
                ksort($flashSale);
                if($now > $startDate && $now < $endDate)
                {
                    array_push($activeFlashSales, $flashSale);
                }
            }
        }

        return $activeFlashSales;
    }

    // double local time conversion: if you delete the timestamp conversion, times will be displayed right.
    public function getComingFlashSales()
    {
        $flashSales = Mage::getModel('PrivateSales/FlashSales');
        $collection = $flashSales->getCollection();
        $collection->addFieldToFilter('fs_enabled', '1');
        $collection->setOrder('fs_start_date', 'ASC');
        $collection->load();
        $comingFlashSales = array();

        // Horsebrands:
        // time() returns the current time im milliseconds since January 1 1970 00:00:00 GMT
        // because it's GMT, we have to use the timestamp() method.
        $now = Mage::getModel('core/date')->timestamp(time());
        $collectionArray = $collection->toArray();
        foreach($collectionArray["items"] as $flashSale)
        {
            if($flashSale)
            {
                // $startDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale["fs_start_date"]));
                // $endDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale["fs_end_date"]));

                // Horsebrands: core/date->timestamp(x) geht davon aus, dass Zeit x in GMT Zeitzone ist
                //  Es werden also 2 Stunden addiert und deswegen kam es zum Start- und Endzeiten-Problem der App
                $startDate = strtotime($flashSale["fs_start_date"]);
                $endDate = strtotime($flashSale["fs_end_date"]);

                $category = Mage::getModel("catalog/category")->load($flashSale["fs_category_id"]);
                $flashSale["fs_start_date"] = date("Y-m-d H:i:s", $startDate);
                $flashSale["fs_end_date"] = date("Y-m-d H:i:s", $endDate);
                $flashSale["fs_picture"] = $category->getImageUrl();
                $flashSale["fs_picture_thumbnail"] = Mage::getBaseUrl('media').'catalog/category/'.$category->getThumbnail();
                $flashSale["fs_subtitle"] = $flashSale["fs_description"];
                $flashSale["fs_description"] = $category->getDescription();
                unset($flashSale["fs_target_url"]); // delete this, because we don't need it
                ksort($flashSale);
                if($now < $startDate)
                {
                    array_push($comingFlashSales, $flashSale);
                }
            }
        }

        return $comingFlashSales;
    }

    public function getProductsForFlashSale($param)
    {
        $flashSaleID = $param["flashSaleID"]["="];
        if (empty($flashSaleID))
        {
            return;
        }

        $flashSale = Mage::getModel('PrivateSales/FlashSales');
        $flashSale = $flashSale->load($flashSaleID)->toArray();
        $productModel = Mage::getModel('catalog/product');
        $flashSaleCategory = Mage::getModel("catalog/category")->load($flashSale["fs_category_id"]);
        $productsForFlashSale = array();

        $productCollection = $flashSaleCategory->getProductCollection()->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
        foreach($productCollection as $product)
        {
            $productModel->load($product->getId());
            $productModelArray = $productModel->toArray();
            $newProduct = array();
            $newProduct["product_id"] = $productModelArray["entity_id"];
            $newProduct["type_id"] = $productModelArray["type_id"];
            $newProduct["sku"] = $productModelArray["sku"];
            $newProduct["price"] = $productModelArray["price"];
            $newProduct["price_uvp"] = $productModelArray["msrp"];
            $newProduct["name"] = $productModelArray["name"];
            $newProduct["base_image"] = $productModel->getImageUrl();
            $newProduct["base_thumbnail"] = $productModel->getSmallImageUrl();
            $newProduct["short_description"] = $productModelArray["short_description"];


            array_push($productsForFlashSale, $newProduct);
        }

        return $productsForFlashSale;
    }

    public function getProductDetails($param)
    {
        $productID = $param["productID"]["="];
        if (empty($productID))
        {
            return;
        }

        $product = Mage::getModel("catalog/product")->load($productID);

        function getAllowProducts($usedProduct)
        {
            $products = array();
            $allProducts = $usedProduct->getTypeInstance(true)->getUsedProducts(null, $usedProduct);
            foreach ($allProducts as $i) {
                $products[] = $i;
            }
            return $products;
        }
        function getAdditionalData($usedProduct, array $excludeAttr = array())
        {
            $product = $usedProduct;
            $data = array();
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
        //            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                    $value = $attribute->getFrontend()->getValue($product);

                    if (!$product->hasData($attribute->getAttributeCode())) {
                        $value = Mage::helper('catalog')->__('N/A');
                    } elseif ((string)$value == '') {
                        $value = Mage::helper('catalog')->__('No');
                    } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = Mage::app()->getStore()->convertPrice($value, true);
                    }

                    $storeLabels = $attribute->getStoreLabels();
                    if(isset($storeLabels[2]))
                        $label = $storeLabels[2];
                    else
                        $label = $attribute->getStoreLabel();
                    if (is_string($value) && strlen($value)) {
                        $data[] = array(
                            'label' => $label,
                            'value' => $value,
                        );
                    }
                }
            }
            return $data;
        }

        $newProduct = array();
        if($product)
        {
            $productModelArray = $product->toArray();
            $newProduct["product_id"] = $productModelArray["entity_id"];
            $newProduct["type_id"] = $productModelArray["type_id"];
            $newProduct["sku"] = $productModelArray["sku"];
            $newProduct["price"] = $productModelArray["price"];
            $newProduct["price_uvp"] = $productModelArray["msrp"];
            $newProduct["name"] = $productModelArray["name"];
            $newProduct["base_image"] = $product->getImageUrl();
            $newProduct["base_thumbnail"] = $product->getSmallImageUrl();
            $newProduct["description"] = $productModelArray["description"];
            $description_app = str_replace("&bull;", "•", $productModelArray["description"]);
            $description_app = str_replace("&amp;", "&", $description_app);
            $description_app = str_replace("&uuml;", "ü", $description_app);
            $description_app = str_replace("&Uuml;", "Ü", $description_app);
            $description_app = str_replace("&auml;", "ä", $description_app);
            $description_app = str_replace("&Auml;", "Ä", $description_app);
            $description_app = str_replace("&ouml;", "ö", $description_app);
            $description_app = str_replace("&Öuml;", "Ö", $description_app);
            $description_app = str_replace("&szlig;", "ß", $description_app);
            $description_app = str_replace("&ndash;", "-", $description_app);
            $description_app = str_replace("&nbsp;", " ", $description_app);
            $description_app = str_replace("&Agrave;", "À", $description_app);
            $description_app = str_replace("&agrave;", "à", $description_app);
            $description_app = str_replace("&bdquo;", "„", $description_app);
            $description_app = str_replace("&ldquo;", "“", $description_app);
            $description_app = str_replace("&rdquo;", "”", $description_app);
            $description_app = preg_replace('/(?<=\<table).*?(?=\<\/table\>)/s', '', $description_app);
            $description_app = str_replace("<table</table>", "", $description_app);
            $newProduct["description_strip_tags_UTF-8"] = utf8_encode(strip_tags(nl2br($description_app)));
            $newProduct["short_description"] = $productModelArray["short_description"];
            //$newProduct["short_description_strip_tags"] = html_entity_decode(strip_tags(str_replace('&nbsp', '', $productModelArray["short_description"])));

            if(isset($productModelArray["media_gallery"]) && (count($productModelArray["media_gallery"]) > 0))
            {
                $newMediaArray = array();
                array_push($newMediaArray, $product->getImageUrl($resize = false));
                $newMedia = $productModelArray["media_gallery"]["images"];
                foreach($newMedia as $media)
                {
                    if($media["disabled"] == "1")   //exclude pictures, that are disabled through backend
                        continue;
                    if(!isset($media["file"]))
                        continue;
                    if(strstr($media["file"], strtolower($productModelArray["sku"])."_small"))      // dirty!, but i don't know any other way
                        continue;
                    $newUrl = Mage::getBaseUrl('media') . 'catalog' . DS . 'product' . $media["file"];
                    array_push($newMediaArray, $newUrl);
                }
                $newProduct["media_gallery"] = $newMediaArray;
            }
            $newProduct["video_id"] = $productModelArray["video"];

            // get additional attributes for product
            $attributes = getAdditionalData($product);
            $newProduct["attributes"] = $attributes;
            $attributeHTML = "<table style='border:none;'>";
            foreach($attributes as $attribute)
            {
                $attributeHTML .= "<tr><td>".$attribute['label']."</td><td>".$attribute['value']."</td></tr>";
            }
            $attributeHTML .= "</table>";
            $newProduct["attributesHTML"] = $attributeHTML;

            $stockItem = $product->getStockItem();
            // limit max sale qty to 10
            if((string)$stockItem->getMaxSaleQty() > 5)
                $newProduct["max_sale_qty"] = 5;
            else
                $newProduct["max_sale_qty"] = (string)$stockItem->getMaxSaleQty();
            $newProduct["current_stock_qty"] = (string)round($stockItem->getQty(),2);

            // handle configurable products - get sku and label for attributes connected with the configurable product
            if($productModelArray["type_id"] == "configurable")
            {
                $data = array();
                foreach(getAllowProducts($product) AS $i)
                {
                    $items = array();
                    foreach($product->getTypeInstance(true)->getConfigurableAttributes($product) AS $attribute)
                    {
                        $attrValue = $i->getResource()->getAttribute( $attribute->getProductAttribute()->getAttributeCode() )->getFrontend();
                        $items[] = $attrValue->getValue($i);
                        $frontendLabel = $attrValue->getAttribute()->getFrontendLabel();
                    }
                    // limit max sale qty to 10
                    if($i->getStockItem()->getMaxSaleQty() > 10)
                        $maxSaleQty = 10;
                    else
                        $maxSaleQty = $i->getStockItem()->getMaxSaleQty();
                    $currentStockQty = $i->getStockItem()->getQty();

                    if ( $items > 0 ) {
                        $data["values"][] =  array(
                            'sku' => $i->getSku(),
                            'product_id' => $i->getId(),
                            'item' => implode($items),
                            'max_sale_qty' => (string)$maxSaleQty,
                            'current_stock_qty' => (string)round($currentStockQty, 0)
                        );
                    }
                }
                $data["label"] = $frontendLabel;
                $newProduct["configurable_attributes"] = $data;
            }
        }

        return $newProduct;
    }

    public function inviteFriend($param) {
        $customer_id = $param["customerId"];       // the customer that invites his friend
        $customer = Mage::getModel("customer/customer");
        $customer = $customer->load($customer_id);
        $invitationEmail = $param["friendEmail"];    // email address of the friend
        $customerData = $customer->getData();

        $invitationLink = Mage::getBaseUrl();
        $invitationLink .= "home/?invitation_id=".$customer_id;

        $emailTemplateID = 92;
        $sender = array('name' => "service@horsebrands.de", 'email' => "HORSEBRANDS");
        $recepient = $invitationEmail;

        $vars = array('customerFirstname' => $customerData["firstname"], 'customerLastname' => $customerData["lastname"], 'invitationLink' => $invitationLink);
        $storeID = 2;

        Mage::getModel('core/email_template')
            ->sendTransactional($emailTemplateID, $sender, $recepient, $recepient, $vars, $storeID);

        return true;
    }

    public function getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    public function getProduct($productId, $store = 2, $identifierType = null)
    {
        $product = Mage::helper('catalog/product')->getProduct($productId,
            2,
            $identifierType
        );
        return $product;
    }

    public function getQuote($quoteId, $store = 2)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel("sales/quote");

        if (!(is_string($store) || is_integer($store))) {
            $quote->loadByIdWithoutStore($quoteId);
        } else {
            $storeId = 2;

            $quote->setStoreId($storeId)
                ->load($quoteId);
        }
        if (is_null($quote->getId())) {
            $this->_fault("quote_error", "Quote existiert nicht.");
            return false;
        }

        return $quote;
    }

    public function prepareProductsData($data)
    {
        return is_array($data) ? $data : null;
    }

    public function addProductToQuote($quoteId, $productsData, $store=2)
    {
        $quote = Horsebrands_Appextensions_Model_ObjectModel_Api::getQuote($quoteId, $store);
        if (empty($store)) {
            $store = $quote->getStoreId();
        }
        $productsData = Horsebrands_Appextensions_Model_ObjectModel_Api::prepareProductsData($productsData);
        if (empty($productsData)) {
            $this->_fault("quote_error","Ungültige Produktdaten");
            return false;
        }
        $errors = array();
        $cartArray = array();
        foreach($quote->getAllItems() as $item) {
            if($item && $item->getProduct())
                $cartArray[$item->getProduct()->getId()] = $item->getQty();
        }

        foreach ($productsData as $productItem)
        {
            if (isset($productItem['product_id'])) {
                $productByItem = Horsebrands_Appextensions_Model_ObjectModel_Api::getProduct($productItem['product_id'], $store, "id");
            } else if (isset($productItem['sku'])) {
                $productByItem = Horsebrands_Appextensions_Model_ObjectModel_Api::getProduct($productItem['sku'], $store, "sku");
            } else {
                $errors[] = "Ein Artikel hat keine ID oder Sku.";
                continue;
            }
            $itemQty = $productItem['qty'];
            if(!isset($cartArray[$productItem['product_id']]))      // make sure, that the product really exists in the cartArray
            {
                $cartArray[$productItem['product_id']] = 0;
            }
            // if the newly added value would exceed the original max sale quantity, then stop action
            if($productByItem->getStockItem()->getMaxSaleQty() > 0 && ($productByItem->getStockItem()->getMaxSaleQty() < ($cartArray[$productItem['product_id']] + $itemQty)))
            {
                $errors[] = "Sie haben die maximale Bestellmenge von ".$productByItem->getStockItem()->getMaxSaleQty()." des Produkts ".$productByItem->getName()." überschritten.";
                continue;
            }
            if($productByItem->getStockItem()->getQty() < ($cartArray[$productItem['product_id']] + $itemQty))
            {
                $errors[] = "Die gewünschte Bestellmenge von ".$productByItem->getName()." ist nicht mehr auf Lager.";
                continue;
            }
            $productRequest = Horsebrands_Appextensions_Model_ObjectModel_Api::getProductRequest($itemQty);
            try {
                $result = $quote->addProduct($productByItem, $productRequest);
                if(is_string($result)) {
                    $this->_fault("quote_error", $result);
                    return false;
                }
            } catch (Mage_Core_Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        if(!empty($errors))
        {
            $this->_fault("quote_error", implode(",", $errors));
            return false;
        }

        try {
            $quote->setReservedAt((string)date('Y-m-d H:i:s', time()));
            $quote->collectTotals()->save();
        } catch(Exception $e) {
            $this->_fault("quote_error", $e->getMessage());
            return false;
        }
        // reset and return quote timer, because everything went fine before
        $returnArray = array();
        $returnArray["success"] = true;
        $reservedAt = $quote->getReservedAt();
        $expiresAt = strtotime($reservedAt) + 900;
        $returnArray["reserved_at"] = $reservedAt;
        $expiresAt = (string)date('Y-m-d H:i:s', $expiresAt);
        $returnArray["expires_at"] = $expiresAt;
        return $returnArray;
    }

    public function updateProductForQuote($quoteId, $productsData, $store=2)
    {
        $quote = Horsebrands_Appextensions_Model_ObjectModel_Api::getQuote($quoteId, $store);
        if (empty($store)) {
            $store = $quote->getStoreId();
        }
        $errors = array();

        $productsData = Horsebrands_Appextensions_Model_ObjectModel_Api::prepareProductsData($productsData);
        if (empty($productsData)) {
            $this->_fault("quote_error",'invalid_product_data');
            return false;
        }
        foreach ($productsData as $productItem) {
            if(isset($productItem["product_id"]))
                $productByItem = Horsebrands_Appextensions_Model_ObjectModel_Api::getProduct($productItem['product_id'], $store, "id");
            else
            {
                $errors[] = "Ein Artikel hat keine ID oder Sku.";
                continue;
            }
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = $quote->getItemByProduct($productByItem);
            if (is_null($quoteItem) || !is_object($quoteItem)) {
                $errors[] = "Ein Produkt konnte nicht geladen werden. Product-ID: ".$productItem["product_id"];
                continue;
            }
            $newSaleQty = (double)$productItem['qty'];
            if($newSaleQty > $productByItem->getStockItem()->getQty())
            {
                $errors[] = "Die gewünscht Bestellmenge des Artikels ".$productByItem->getName()." ist nicht mehr auf Lager.";
                continue;
            }
            if((($productByItem->getStockItem()->getMaxSaleQty() > 0) && ($productByItem->getStockItem()->getMaxSaleQty() < $newSaleQty)))    // enough items in stock, maxSaleQty is exceeded
            {
                $errors[] = "Sie haben die maximale Bestellmenge von ".$productByItem->getStockItem()->getMaxSaleQty()." des Produkts ".$productByItem->getName()." überschritten.";
                continue;
            }
            // everything fine, set the requested quantity
            $quoteItem->setQty($newSaleQty);
        }
        if (!empty($errors)) {
            $this->_fault("quote_error", implode(",", $errors));
        }

        try {
            $quote->setReservedAt((string)date('Y-m-d H:i:s', time()));
            $quote->collectTotals()->save();
        } catch(Exception $e) {
            $this->_fault("quote_error", $e->getMessage());
        }
        // reset and return quote timer, because everything went fine before
        $returnArray = array();
        $returnArray["success"] = true;
        $reservedAt = $quote->getReservedAt();
        $expiresAt = strtotime($reservedAt) + 900;
        $returnArray["reserved_at"] = $reservedAt;
        $expiresAt = (string)date('Y-m-d H:i:s', $expiresAt);
        $returnArray["expires_at"] = $expiresAt;
        return $returnArray;
    }

    public function getQuoteInfo($quoteId, $store = null)
    {
        // Call the original Api-function and extend the result by the parameter we need
        $quoteInfo = Mage::getSingleton('Mage_Checkout_Model_Cart_Api');

        $result = $quoteInfo->info($quoteId, 2);

        $quote = Horsebrands_Appextensions_Model_ObjectModel_Api::getQuote($quoteId, $store);

        // set max-sale-qty for items
        foreach($result["items"] as $key => $item)
        {
            $productId = $item["product_id"];
            $product = Mage::getModel("catalog/product")->load($productId);
            if(!$product)
                continue;

            $stockItemMaxSaleQty = $product->getStockItem()->getMaxSaleQty();
            if($stockItemMaxSaleQty > 10) $stockItemMaxSaleQty = 10;
            $result["items"][$key]["max_sale_qty"] = $stockItemMaxSaleQty;

            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            if(isset($parentIds[0])){
                $parentProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
                $result["items"][$key]["parent_product_name"] = $parentProduct->getName();

                $configurableAttributes = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);

                $productAttributesConfiguration = array();
                foreach($configurableAttributes as $attribute)
                {
                    if($product->hasData($attribute['attribute_code']))
                    {
                        $attributeCode = $product->getData($attribute['attribute_code']);
                        foreach($attribute['values'] as $valueData)
                        {
                            if($valueData['value_index'] == $attributeCode)
                            {
                                $productAttributesConfiguration[] = array('label' => $attribute['frontend_label'], 'value' => $valueData['store_label']);
                            }
                        }
                    }
                }
                $result["items"][$key]["attributes_configuration"] = $productAttributesConfiguration;
            }
            else
            {
                $result["items"][$key]["parent_product_name"] = "";
                $result["items"][$key]["attributes_configuration"] = array();
            }
        }

        $reservedAt = $quote->getReservedAt();
        $expiresAt = strtotime($reservedAt) + 900;
        $result["reserved_at"] = $reservedAt;
        $expiresAt = (string)date('Y-m-d H:i:s', $expiresAt);
        $result["expires_at"] = $expiresAt;

        // Collect Shipping rates
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectTotals();
        $rates = $quote->getShippingAddress()->getShippingRatesCollection();
        $shippingPrice = 0;

        // Search for shipping costs
        foreach ($rates as $rate) {
            if ($rate->getPrice() > 0) {
                $shippingPrice = $rate->getPrice();
                break;
            }
        }

        // Set the shipping costs
        $result["shipping_cost"] = number_format($shippingPrice, 2);

        return $result;
    }

    public function initOrder($orderIncrementId)
    {
        $order = Mage::getModel('sales/order');

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            $this->_fault("quote_error", 'Quote doesnt exist.');
        }

        return $order;
    }
    public function setOrderStatus($params, $store = 2)
    {
        if(!$params)
            return false;

        $order = Horsebrands_Appextensions_Model_ObjectModel_Api::initOrder($params["orderId"]);
        if(!$order)
        {
            $this->_fault("quote_error", "Bestellung nicht gefunden.");
        }

        // collect payment information
        if($params["status"] == "accepted")
        {
            // set real payment information
            $orderPayment = $order->getPayment();
            if(!$orderPayment)
            {
                $this->_fault("quote_error", "Das Payment-Objekt konnte nicht gefunden werden.");
                return false;
            }
            $order->setData("payment_validated", 1);
            $orderPayment->setAmountPaid($orderPayment->getAmountPaid());
            $orderPayment->setAdditionalInformation($params);
            $orderPayment->save();
            Mage::log("OrderPayment saved", Zend_Log::ALERT, "api_exception.log", true);

            /** @var Mage_Sales_Model_Order_Invoice_Api $invoiceModel */
            $invoiceModel = Mage::getModel('sales/order_invoice_api');
            try
            {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, "Zahlung durch API abgeschlossen.")->save();
                $order->sendNewOrderEmail();

                $invoiceId = $invoiceModel->create($order->getIncrementId(), array(), 'Rechnung erstellt', false, true);
                $invoiceModel->capture($invoiceId)->save();
            }
            catch (Exception $e)
            {
                // Write exception to log
                Mage::log("Exception in setOrderStatus: ".$e->getMessage(), Zend_Log::ALERT, "api_exception.log", true);
                return true;
            }

            return true;
        }
        elseif($params["status"] == "canceled")
        {
            $order->cancel();
            $order->save();
            return true;
        }
        $this->_fault("quote_error", "Unbekannter Bestellstatus.");
        return false;
    }

    public function getShippingMethodsForCountries($storeId = 2)
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shipMethods = array();
        foreach ($methods as $shippingCode => $shippingModel)
        {
            $shippingTitle = Mage::getStoreConfig('carriers/'.$shippingCode.'/title');
            $shipMethods[$shippingCode] = array("title" => $shippingTitle);
            $shippingCountries = Mage::getStoreConfig('carriers/'.$shippingCode.'/specificcountry', $storeId);
            $shipMethods[$shippingCode]["countries"] = $shippingCountries;
        }
        return $shipMethods;
    }

}
?>