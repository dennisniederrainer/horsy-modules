<?php

class Horsebrands_ProductReturn_Model_Rma extends MDN_ProductReturn_Model_Rma {

	public function NotifyCustomer()
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('productreturn/emails/template_' . $this->getrma_status(), $this->getCustomer()->getStoreId());
        if (!$templateId) {
            mage::log($this->getrma_status(), null, 'rma_model.log');
            return $this;
        }
        $identityId = Mage::getStoreConfig('productreturn/emails/email_identity', $this->getCustomer()->getStoreId());

        $customerEmail = $this->getCustomer()->getemail();
        if ($this->getrma_customer_email() != '')
            $customerEmail = $this->getrma_customer_email();

        //set data to use in array
        $data = array
        (
            'caption'                => $this->getcaption(),
            'customer_name'          => $this->getCustomer()->getName(),
            'customer_firstname'     => $this->getCustomer()->getFirstname(),
            'customer_is_female'     => ($this->getCustomer()->getPrefix() === 'Frau'),
            'rma_id'                 => $this->getrma_ref(),
            'order_id'               => $this->getSalesOrder()->getincrement_id(),
            'rma_expire_date'        => mage::helper('core')->formatDate($this->getrma_expire_date(), 'short'),
            'store_name'             => $this->getSalesOrder()->getStoreGroupName(),
            'rma_reason'             => mage::helper('ProductReturn')->__($this->getrma_reason()),
            'rma_status'             => mage::helper('ProductReturn')->__($this->getrma_status()),
            'rma_description'        => $this->getrma_public_description(),
            'rma_public_description' => $this->getrma_public_description(),
            'rma_action'             => mage::helper('ProductReturn')->__($this->getrma_action()),
            'product_html'           => $this->getProductsAsHtml()
        );

        //send email
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $this->getCustomer()->getStoreId()))
            ->sendTransactional(
                $templateId,
                $identityId,
                $customerEmail,
                $this->getCustomer()->getname(),
                $data,
                null);

        //send email to cc_to
        $cc = mage::getStoreConfig('productreturn/emails/cc_to');
        if ($cc != '') {
            Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'adminhtml', 'store' => $this->getCustomer()->getStoreId()))
                ->sendTransactional(
                    $templateId,
                    $identityId,
                    $cc,
                    $this->getCustomer()->getname(),
                    $data,
                    null);
        }

        $this->addHistoryRma(mage::helper('ProductReturn')->__('Customer notified for status %s', mage::helper('ProductReturn')->__($this->getrma_status())));

        $translate->setTranslateInline(true);

        return $this;
    }
}
