<?php

require_once 'MDN/ProductReturn/controllers/AdminController.php';

class Horsebrands_ProductReturn_AdminController extends MDN_ProductReturn_AdminController {

	/**
     *	Horsebrands Rewrite: Notify Customer, when RMA is created
     *
     */
    public function SaveAction()
    {

        $data = $this->getRequest()->getPost('data');
        $rma  = mage::getModel('ProductReturn/Rma')->load($data['rma_id']);

        try {
            //if creation, set fields default values
            $isCreation = false;
            if ($data['rma_id'] == '') {
                $isCreation             = true;
                $data['rma_created_at'] = date('Y-m-d H:n');
                $data['rma_updated_at'] = date('Y-m-d H:n');
                $customer               = mage::getModel('customer/customer')->load($data['rma_customer_id']);
                if ($customer)
                    $data['rma_customer_name'] = $customer->getName();
                $data['rma_id'] = null;

                //set default expiration date
                if ($data['rma_expire_date'] == '')
                    $data['rma_expire_date'] = date('Y-m-d', time() + 3600 * 24 * mage::getStoreConfig('productreturn/general/default_validity_duration'));
            }

            //check date
            if ($data['rma_expire_date'] == '')
                $data['rma_expire_date'] = null;
            if ($data['rma_reception_date'] == '')
                $data['rma_reception_date'] = null;
            if ($data['rma_return_date'] == '')
                $data['rma_return_date'] = null;

            //ipload shipping label
            if (isset($_FILES['returnlabel']) && $_FILES['returnlabel']['name'] != '') {

                $uploader = new Varien_File_Uploader('returnlabel');
                $uploader->setAllowedExtensions(array('pdf'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS . 'rma_return_labels';

                if (!is_dir($path)) {
                    mkdir($path);
                }

                $filename = md5($data['rma_id'] . '-' . $data['rma_ref']) . '.pdf';

                $uploader->save($path, $filename);


            }

            //save
            $rma->setData($data);
            $rmaProducts = $rma->getProducts();
            $rma->save();

            //set sub products information
            foreach ($rmaProducts as $rmaProduct) {
                if (mage::getModel('ProductReturn/RmaProducts')->productIsDisplayed($rmaProduct)) {
                    $id  = $rmaProduct->getitem_id();
                    if (isset($data['rp_qty_' . $id]))
                        $qty = $data['rp_qty_' . $id];
                    else
                        $qty = $rmaProduct->getrp_qty();
                    if (isset($data['rp_description_' . $id])) {
                        $description  = $data['rp_description_' . $id];
                        $serials      = $data['rp_serials_' . $id];
                        $reason       = $data['rp_reason_' . $id];
                        $request_type = $data['rp_request_type_' . $id];
                    } else {
                        $description  = '';
                        $serials      = '';
                        $reason       = '';
                        $request_type = '';
                    }
                    $rmaProductItem = $rma->updateSubProductInformation($rmaProduct, $qty, $description, $reason, $serials, $request_type);
                }
            }

            if (!$isCreation) {

                //retrieve datas
                $this->initDataOrder($rma);
                $this->initDataCreditMemo($rma);

                //create objects (if contain products)
                if (count($this->_CreditMemoCreationData['products']) > 0) {
									$creditMemo = mage::helper('ProductReturn/CreateCreditmemo')->CreateCreditmemo($this->_CreditMemoCreationData);
								}
                if (count($this->_OrderCreationData['products']) > 0) {
                    $order = mage::helper('ProductReturn/CreateOrder')->CreateOrder($this->_OrderCreationData);
                    mage::helper('ProductReturn/Reservation')->affectProductsToCreatedOrder($rma, $order);

                    $invoice = mage::helper('ProductReturn/CreateInvoice')->CreateInvoice($order);
                }

                //process products destination
                $rmaProducts = $rma->getProducts();
                foreach($rmaProducts as $product)
                {
                    if ($product->getrp_destination_processed())
                        continue;
                    if (!$product->getrp_destination())
                        continue;
                    $productData = array('rp_id' => $product->getrp_id(), 'product_id' => $product->getrp_product_id(), 'qty' => $product->getrp_qty(), 'destination' => $product->getrp_destination());
                    $description = mage::helper('ProductReturn')->__('Product return #%s', $rma->getrma_ref());
                    Mage::helper('ProductReturn/Stock')->manageProductDestination($productData, $rma->getSalesOrder()->getStore()->getwebsite_id(), $description, $rma);
                }

                //misc
                $rma->toggleStatusIfAllProductsProcessed();

	            //confirm
	            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Data saved'));
            } else {
            	// if isCreation, notify customer (needs to be performed via helper)
            	mage::helper('ProductReturn')->sendReturnRequestEmail($rma);

            	//confirm
	            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Retoure erfolgreich angelegt'));
	            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('customer successfully notified.'));
            }


            if ($data['rma_is_locked'] == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('RMA Locked'));
            }

        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured : ') . $ex->getMessage());
        }
        //redirect
        $this->_redirect('ProductReturn/Admin/Edit', array('rma_id' => $rma->getId()));
    }

	/**
     *	Horsebrands Rewrite: Notify the Customer automatically, when products are received
     *
     */
    public function ProductsReceivedAction()
    {
        try {
            $RmaId = $this->getRequest()->getParam('rma_id');
            $rma   = mage::getModel('ProductReturn/Rma')->load($RmaId);
            $rma->productsReceived();

            // notify customer after products received
            $rma->NotifyCustomer();

            //confirme
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Product reception processed'));
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('customer successfully notified.'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('An error occured') . ' : ' . $ex->getMessage());
        }

        //redirige sur la page RMA
        $this->_redirect('ProductReturn/Admin/Edit', array('rma_id' => $rma->getId()));
    }

    //***********************************************************************************************************************************************
    //***********************************************************************************************************************************************
    //***** Methods to collect datas for creditmemo and order creation
    //***********************************************************************************************************************************************
    //***********************************************************************************************************************************************
    // Horsebrands: Also rewritten, because they are private and not protected.
    // Rewritten because of update safety
    /**
     * Init Data for Order
     *
     */
    private function initDataOrder($rma)
    {

        $data = $this->getRequest()->getPost('data');

        //add products
        $this->_OrderCreationData['products'] = array();
        $this->_OrderCreationData['rma_ref']  = $rma->getrma_ref();
        $this->_OrderCreationData['rma_id']   = $rma->getId();

        //helper for calculate tax
        $helper          = mage::helper('tax');
        $ShippingAddress = $rma->getShippingAddress();
        $BillingAddress  = $rma->getBillingAddress();

        $CustomerTaxClass = $rma->getCustomer()->getTaxClassId();
        $storeId          = $rma->getCustomer()->getStoreId();
        $weight           = 0;

        //add products
        foreach ($rma->getProducts() as $item) {
            $rpId = $item->getrp_id();
            switch ($this->getActionForProduct($rpId)) {

                case 'return':
                    $newProduct = array('product_id'     => Mage::getStoreConfig('productreturn/product_return/fake_product_id'),
                                        'src_product_id' => Mage::getStoreConfig('productreturn/product_return/fake_product_id'),
                                        'qty'            => $item->getrp_qty(),
                                        'price'          => 0,
                                        'price_ht'       => 0,
                                        'product_name'   => mage::helper('ProductReturn')->__('Return product : ') . $item->getname(),
                                        'rp_id'          => $rpId,
                                        'action'         => 'return',
                                        'destination'    => $this->getDestinationForProduct($rpId));
                    $this->_OrderCreationData['products'][] = $newProduct;
                    $weight += $item->getweight();
                    break;

                case 'exchange':
                    $substitutionProductId = $this->getRequest()->getPost('hidden_exchange_' . $rpId);
                    $substitutionProduct   = mage::getModel('catalog/product')->load($substitutionProductId);
                    $srcProductId          = $item->getrp_product_id();

                    //if product type is configurable, set sub product as source product
                    if ($item->getproduct_type() == 'configurable') {
                        foreach ($rma->getSalesOrder()->getAllItems() as $subProduct) {
                            if ($subProduct->getparent_item_id() == $item->getitem_id())
                                $srcProductId = $subProduct->getproduct_id();
                        }
                    }

                    $price = $this->getRequest()->getPost('exhange_price_adjustment_' . $rpId);
                    if ($price <= 0)
                        $price = 0;
                    else
                        $price = str_replace('+', '', $price);

                    $coef = $helper->getPrice($substitutionProduct, 1, true, $ShippingAddress, $BillingAddress, $CustomerTaxClass, $storeId);
                    $priceHt = number_format($price / $coef, 4, '.', '');

                    $newProduct  = array('product_id'       => $substitutionProduct->getId(),
                                        'src_product_id'   => $srcProductId,
                                        'qty'              => $item->getrp_qty(),
                                        'price'            => $price,
                                        'price_ht'         => $priceHt,
                                        'rp_id'            => $rpId,
                                        'action'           => 'exchange',
                                        'price_adjustment' => $price,
                                        'product_name'     => $substitutionProduct->getname(),
                                        'destination'      => $this->getDestinationForProduct($rpId));
                    $this->_OrderCreationData['products'][] = $newProduct;
                    $weight += $substitutionProduct->getweight();
                    break;
            }
        }

        //add technical costs
        if ($data['rma_technical_cost'] > 0) {
            $fakeProductId = Mage::getStoreConfig('productreturn/product_return/fake_product_id');
            $fakeProduct   = Mage::getModel('catalog/product')->load($fakeProductId);
            $qty           = 1;

            $price   = $data['rma_technical_cost'];
            $coef = $helper->getPrice($fakeProduct, 1, true, $ShippingAddress, $BillingAddress, $CustomerTaxClass, $storeId);
            $priceHt = number_format($price / $coef, 4, '.', '');

            $product_name = $data['rma_libelle_action'];
            if (empty($product_name))
                $product_name = Mage::getStoreConfig('productreturn/product_return/fake_product_label');

            $newProduct                             = array('product_id'     => $fakeProductId,
                                                            'src_product_id' => $fakeProductId,
                                                            'qty'            => $qty,
                                                            'price'          => $price,
                                                            'price_ht'       => $priceHt,
                                                            'rp_id'          => null,
                                                            'product_name'   => $product_name);
            $this->_OrderCreationData['products'][] = $newProduct;
        }

        $this->_OrderCreationData['weight'] = $weight;

        $this->_OrderCreationData['payment_method']  = $data['payment_method'];
        $this->_OrderCreationData['shipping_method'] = $data['rma_carrier'];

        //info customer (from customer address)
        $this->_OrderCreationData['customer_firstname']  = $rma->getCustomer()->getfirstname();
        $this->_OrderCreationData['customer_lastname']   = $rma->getCustomer()->getlastname();
        $this->_OrderCreationData['customer_prefix']     = $rma->getCustomer()->getprefix();
        $this->_OrderCreationData['customer_middlename'] = $rma->getCustomer()->getmiddlename();
        $this->_OrderCreationData['customer_suffix']     = $rma->getCustomer()->getsuffix();

        //other information
        $this->_OrderCreationData['customer_email']    = $rma->getCustomer()->getemail();
        $this->_OrderCreationData['customer_id']       = $rma->getCustomer()->getid();
        $this->_OrderCreationData['customer_taxvat']   = $rma->getCustomer()->gettaxvat();
        $this->_OrderCreationData['customer_group_id'] = $rma->getCustomer()->getgroup_id();
        $this->_OrderCreationData['store_id']          = $rma->getSalesOrder()->getstore_id();
        $this->_OrderCreationData['website_id']        = $rma->getSalesOrder()->getwebsite_id();

        //billing addresses
        $this->_OrderCreationData['billing_address'] = array();
        $billingAddress                              = $rma->getBillingAddress();
        foreach ($billingAddress->getData() as $key => $value) {
            $this->_OrderCreationData['billing_address'][$key] = $value;
        }

        //billing addresses
        $this->_OrderCreationData['shipping_address'] = array();
        $shippingAddress                              = $rma->getShippingAddress();
        foreach ($shippingAddress->getData() as $key => $value) {
            $this->_OrderCreationData['shipping_address'][$key] = $value;
        }

        //for guest
        if ($rma->IamGuest())
            $this->_OrderCreationData['customer_guest'] = 1;
        else
            $this->_OrderCreationData['customer_guest'] = 0;

        //tax for shipping
        $helper = mage::helper('tax');
        $this->_OrderCreationData['shipping_cost'] = $data['rma_shipping_cost'];
        $this->_OrderCreationData['shipping_taxamount'] = $data['rma_shipping_cost'] - Mage::helper('ProductReturn/Tax')->shippingInclToExcl($data['rma_shipping_cost'], $rma->getSalesOrder());

        //info order
        $this->_OrderCreationData['order_currency_code']     = $rma->getSalesOrder()->getorder_currency_code();
        $this->_OrderCreationData['base_currency_code']      = $rma->getSalesOrder()->getbase_currency_code();
        $this->_OrderCreationData['store_currency_code']     = $rma->getSalesOrder()->getstore_currency_code();
        $this->_OrderCreationData['global_to_currency_rate'] = $rma->getSalesOrder()->getGlobalCurrencyCode();
        $this->_OrderCreationData['base_to_global_rate']     = $rma->getSalesOrder()->getBaseToGlobalRate();
        $this->_OrderCreationData['base_to_order_rate']      = $rma->getSalesOrder()->getBaseToOrderRate();
        $this->_OrderCreationData['store_to_base_rate']      = $rma->getSalesOrder()->getstore_to_base_rate();
        $this->_OrderCreationData['store_to_order_rate']     = $rma->getSalesOrder()->getstore_to_order_rate();
        $this->_OrderCreationData['state']                   = $rma->getSalesOrder()->getstate();
        $this->_OrderCreationData['customer_note_notify']    = $rma->getSalesOrder()->getcustomer_note_notify();

        $this->_OrderCreationData['order_id'] = $rma->getrma_order_id();
    }

    /*
     * Init data for credit memo
     *
     */
    private function initDataCreditMemo($rma)
    {
        $data                                      = $this->getRequest()->getPost('data');
        $this->_CreditMemoCreationData['products'] = array();

        foreach ($rma->getProducts() as $item) {
            $rpId = $item->getrp_id();

            //check if product action is refund or exchange
            if (($this->getActionForProduct($rpId) != 'refund') && ($this->getActionForProduct($rpId) != 'exchange'))
                continue;

            //if exchange & price adjustment is negative
            $priceAdjustment = $this->getRequest()->getPost('exhange_price_adjustment_' . $rpId);
            if (($this->getActionForProduct($rpId) == 'exchange') && ($priceAdjustment >= 0))
                continue;
            if ($priceAdjustment < 0)
                $priceAdjustment = -$priceAdjustment;

            //add product
            $newProduct = array('product_id'     => $item->getrp_product_id(),
                                'order_item_id' => $item->getrp_orderitem_id(),
                                'src_product_id' => $item->getrp_product_id(),
                                'qty'            => $item->getrp_qty(),
                                'rp_id'          => $rpId,
                                'price'          => $priceAdjustment,
                                'price_ht'       => $priceAdjustment,
                                'product_name'   => $item->getname(),
                                'destination'    => $this->getDestinationForProduct($rpId));
            $this->_CreditMemoCreationData['products'][] = $newProduct;

            //if product type is configurable, add sub products
            if ($item->getproduct_type() == 'configurable') {
                foreach ($rma->getSalesOrder()->getAllItems() as $subProduct) {
                    if ($subProduct->getparent_item_id() == $item->getitem_id()) {
                        $newProduct = array('product_id'     => $subProduct->getproduct_id(),
                                            'order_item_id' => $subProduct->getId(),
                                            'src_product_id' => $item->getproduct_id(),
                                            'qty'            => $item->getrp_qty(),
                                            'rp_id'          => null,
                                            'price'          => $priceAdjustment,
                                            'price_ht'       => $priceAdjustment,
                                            'product_name'   => $subProduct->getname(),
                                            'destination'    => $this->getDestinationForProduct($rpId));
                        $this->_CreditMemoCreationData['products'][] = $newProduct;
                    }
                }
            } 

            //if product is child of bundle product, add bundle product
            if ($bundleItemArray = $this->productIsChildOfBundleProduct($item, $rma->getProducts())) {
                $this->_CreditMemoCreationData['products'][] = $bundleItemArray;
            }
        }

        $this->_CreditMemoCreationData['refund_shipping_fees']   = (isset($data['refund_shipping_fees']));
        $this->_CreditMemoCreationData['refund_shipping_amount'] = $data['refund_shipping_amount']; //Mage::helper('ProductReturn/Tax')->shippingInclToExcl($data['refund_shipping_amount'], $rma->getSalesOrder());

        $this->_CreditMemoCreationData['order_id']               = $rma->getrma_order_id();
        $this->_CreditMemoCreationData['rma_id']                 = $rma->getrma_id();

        $this->_CreditMemoCreationData['refund'] = $data['credit_memo_fee'];
        $this->_CreditMemoCreationData['fee']    = $data['credit_memo_refund'];

        if (isset($data['rma_refund_online'])) {
            $this->_CreditMemoCreationData['refund_online'] = $data['rma_refund_online'];
        } else {
            $this->_CreditMemoCreationData['refund_online'] = 0;
        }
    }
}
