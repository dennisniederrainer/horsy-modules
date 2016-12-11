<?php

require_once 'MDN/ProductReturn/controllers/FrontController.php';
// require('MDN/ProductReturn/controllers/FrontController.php');

class Horsebrands_ProductReturn_FrontController extends MDN_ProductReturn_FrontController {

    /**
     * Submit new request
     *
     */
    public function SubmitRequestAction()
    {
        //create request
        $data = $this->getRequest()->getPost('data');

        //set main information
        $rma = mage::getModel('ProductReturn/Rma')->load($data['rma_id']);


        //if creation, set fields default values
        if ($data['rma_id'] == '') {
            $data['rma_created_at'] = date('Y-m-d H:n');
            $data['rma_updated_at'] = date('Y-m-d H:n');
            $customer               = mage::getModel('customer/customer')->load($data['rma_customer_id']);
            if ($customer)
                $data['rma_customer_name'] = $customer->getName();
            $data['rma_id']          = null;
            $data['rma_expire_date'] = date('Y-m-d', time() + 3600 * 24 * mage::getStoreConfig('productreturn/general/default_validity_duration'));
            $data['rma_status']      = mage::getStoreConfig('productreturn/product_return/new_status_for_request');
        }

        $rma->setData($data);
        $rmaProducts = $rma->getProducts();

        //on va verifier si il ya des quantit�s pour au moin 1 produit.
        //Sinon erreur pas d'insertion de rma
        $p_qty = false;

        // Horsebrands Rueckgaberegel >= 40euro
        $p_minPrice = false;
        $helper = mage::helper('ProductReturn');

        foreach ($rmaProducts as $rmaProduct) {
            // Step 1: check if product has a qty > 0
            $id = $rmaProduct->getitem_id();

            if (isset($data['rp_qty_' . $id]) && $data['rp_qty_' . $id] > 0) {
                $p_qty = true;

                // Step 2: check if product price is >= 40.00 euro
                if($helper->checkPriceRule($rmaProduct)){
                    $p_minPrice = true;
                }
            }

        }

        if ($p_qty && $p_minPrice) {

            $rma->save();

            //set sub products information
            foreach ($rmaProducts as $rmaProduct) {
                $id = $rmaProduct->getitem_id();

                //check
                if (isset($data['rp_qty_' . $id])) {
                    $qty          = $data['rp_qty_' . $id];
                    $description  = $data['rp_description_' . $id];
                    $reason       = $data['rp_reason_' . $id];
                    $request_type = $data['rp_request_type_' . $id];
                    $rma->updateSubProductInformation($rmaProduct, $qty, $description, $reason, null, $request_type);
                }

            }

            //notify admin
            $rma->NotifyCreationToAdmin();

            // horsebrands: send email with return link to customer
            mage::helper('ProductReturn')->sendReturnRequestEmail($rma);

            //confirm & redirect
            Mage::getSingleton('customer/session')->addSuccess($this->__('Wir haben deine Retourenanmeldung erhalten und werden diese schnellstm&ouml;glich bearbeiten.'));
            // Mage::getSingleton('customer/session')->addSuccess($this->__('Product Return successfully sent.'));
            $this->_redirect('ProductReturn/Front/View', array('rma_id' => $rma->getId()));
        } else {

            if(!$p_qty)
                Mage::getSingleton('customer/session')->addError($this->__('Please select quantities to return.'));

            if(!$p_minPrice)
                Mage::getSingleton('customer/session')->addError($this->__('None of your selected Products passed the Return Rule: Price >= 40.00 Euro'));

            $this->_redirect('ProductReturn/Front/NewRequest', array('order_id' => $data['rma_order_id']));
        }
    }
}
