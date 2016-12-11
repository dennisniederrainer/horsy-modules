<?php

class Horsebrands_ProductReturn_Block_Productreturn_Edit_Tab_Actions extends MDN_ProductReturn_Block_Productreturn_Edit_Tab_Actions {

	/**
     * Original getPaymentMethodAsCombo has errors. there is no Mage::getModel('Payment/Config'),
     * only mage::getSingleton('payment/config') exists.
     *
     * @param unknown_type         $name
     * @param string|\unknown_type $value
     *
     * @return unknown
     */
    public function getPaymentMethodAsCombo($name, $value = '')
    {
        $displayDisabledPaymentMethods = mage::getStoreConfig('productreturn/product_return/display_disabled_payment_methods');

        if ($value == '')
            $value = mage::getStoreConfig('productreturn/product_return/default_payment_method');
        $retour = '<select name="' . $name . '" id="' . $name . '">';

        if ($displayDisabledPaymentMethods)
            $paymentmethods = Mage::getSingleton('payment/config')->getAllMethods();
        else
            $paymentmethods = Mage::getSingleton('payment/config')->getActiveMethods();

        $retour .= '<option value="" ></option>';
        foreach ($paymentmethods as $method) {
            $selected = '';
            if ($value == $method->getId())
                $selected = ' selected="selected" ';
            $retour .= '<option value="' . $method->getId() . '" ' . $selected . '>' . $method->getTitle() . '</option>';
        }
        $retour .= '</select>';

        return $retour;
    }
}