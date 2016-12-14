<?php

class Horsebrands_ProductReturn_Helper_Data extends MDN_ProductReturn_Helper_Data {
    
    protected $_isMinReturnValue = false;

    /**
     * Check if it is possible to request for product return on order depending of the date of the last shipment
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return unknown
     */
    public function IsOrderAvailable($order)
    {
        /** @var  $order Mage_Sales_Model_Order */
        //retrieve limit date
        $date_limite = null;
        foreach ($order->getShipmentsCollection() as $_shipment) {
            if ($date_limite < $_shipment->getupdated_at())
                $date_limite = $_shipment->getupdated_at();
        }

        //return false if order has no shipment
        if ($date_limite == null)
            return $this->__('Deine Bestellung ist noch nicht versandt.');

        $daysAvailable = Mage::getStoreConfig('productreturn/product_return/max_days_request');
        if (!$daysAvailable)
            $daysAvailable = 30;
        $date_limite   = date_create($date_limite . "+ " . $daysAvailable . " days");
        $now           = date_create();
        if ($date_limite->format('Ymd') > $now->format('Ymd')) {

            $allinrma     = array();
            
            // horsebrands 40 euro regel
            $itemPassMinPrice = array();

            $itemsinorder = $order->getItemsCollection();

            foreach ($itemsinorder as $iteminorder):
                $allinrma[$iteminorder->getProductId()] = $iteminorder->getQtyShipped();
                $allinrma[$iteminorder->getProductId()] -= $this->getIsalreadyInRma($iteminorder);
                
                if($this->_isMinReturnValue) {
                    $itemPassMinPrice[$iteminorder->getProductId()] = ($this->checkPriceRule($iteminorder) ? 1 : 0);
                }
            endforeach;

            if (array_sum($allinrma) <= 0) {
                return $this->__('Alle Artikel retourniert.');
            } else {
                // there are products to return, check pricerule
                if ($this->_isMinReturnValue && array_sum($itemPassMinPrice) <= 0) {
                    return $this->__('No products passing Return Rule: Price >= 40.00 Euro');
                }
            }

            return true;
        } else {
            return $this->__('R&uuml;ckgabefrist abgelaufen.');
        }
    }

    public function getSelectUrl($order) {
        return mage::getUrl('ProductReturn/Front/NewRequest', array('order_id' => $order->getId()));
    }

    public function sendReturnRequestEmail($rma) {
        $templateId = Mage::getStoreConfig('productreturn/emails/template_rma_request', $rma->getCustomer()->getStoreId());
        if (!$templateId) {
            mage::log('no email template id :: Horsebrands_ProductReturn_Helper_Data_sendReturnRequestEmail', null, 'rma_email.log');
            return;
        }
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity', $rma->getCustomer()->getStoreId());

        $customerEmail = $rma->getCustomer()->getemail();
        if ($rma->getrma_customer_email() != '')
            $customerEmail = $rma->getrma_customer_email();

        //set data to use in array
        $data = array
        (
            'customer_firstname'     => $rma->getCustomer()->getFirstname(),
            'rma_id'                 => $rma->getrma_ref(),
            'order_id'               => $rma->getSalesOrder()->getincrement_id(),
            'customer_is_female'     => ($rma->getCustomer()->getPrefix() === 'Frau'),
            // 'rma_expire_date'        => mage::helper('core')->formatDate($this->getrma_expire_date(), 'short'),
            // 'store_name'             => $this->getSalesOrder()->getStoreGroupName(),
            // 'rma_reason'             => mage::helper('ProductReturn')->__($this->getrma_reason()),
            // 'rma_status'             => mage::helper('ProductReturn')->__($this->getrma_status()),
            // 'rma_description'        => $this->getrma_public_description(),
            // 'rma_public_description' => $this->getrma_public_description(),
            // 'rma_action'             => mage::helper('ProductReturn')->__($this->getrma_action()),
            // 'product_html'           => $this->getProductsAsHtml()
        );

        //send email
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $rma->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $customerEmail,
                $rma->getCustomer()->getName(),
                $data,
                null);

        //send email to cc_to
        $cc = mage::getStoreConfig('productreturn/emails/cc_to');
        if ($cc != '') {
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'adminhtml', 'store' => $rma->getCustomer()->getStoreId()))
                ->sendTransactional(
                    $templateId,
                    $identityId,
                    $cc,
                    $rma->getCustomer()->getName(),
                    $data,
                    null);
        }

        $rma->addHistoryRma('Retouren-Email an Kunde versandt.');

        return;
    }

    public function checkPriceRule($item) {
        $price = mage::helper('checkout')->getPriceInclTax($item);

        if($price >= 40.00 || !$this->_isMinReturnValue)
            return true;

        return false;
    }
}