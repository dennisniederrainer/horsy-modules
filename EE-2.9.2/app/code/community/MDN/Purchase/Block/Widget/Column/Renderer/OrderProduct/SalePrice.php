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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalePrice extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row) {
        $currencyRate = $this->getColumn()->getcurrency_change_rate();

        $salePrice = number_format($this->convertSalePriceToExcludingTax($row->getsale_price()), 2, '.', '');

        $html = '<input type="hidden" id="product_price_' . $row->getpop_num().'" value="'.$salePrice.'">';
        $html .= '<script>var product_price_' . $row->getpop_num() . ' = ' . $salePrice . ';</script>';
        $html .= '<div id="div_sellprice_' . $row->getpop_num() . '"></div>';

        return $html;
    }

    /**
     * Convert price
     */
    private function convertSalePriceToExcludingTax($price) {
        if (Mage::helper('tax')->priceIncludesTax()) {
            //if price includes tax, calculate excl tax price
            $taxRate = Mage::getStoreConfig('purchase/purchase_product/pricer_default_tax_rate');
            $price = $price / (1 + ($taxRate / 100));
            return $price;
        }
        else
            return $price;
    }

}