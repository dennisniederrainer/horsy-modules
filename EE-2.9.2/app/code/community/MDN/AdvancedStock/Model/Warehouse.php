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
class MDN_AdvancedStock_Model_Warehouse extends Mage_Core_Model_Abstract {

    private $_assignments = null;
    private $_resourceModel = null;
    private $_stocks = null;

    public function _construct() {
        parent::_construct();
        $this->_init('AdvancedStock/Warehouse');
    }

    /**
     * Return assignments for this stock
     *
     * @return unknown
     */
    public function getAssignments() {
        if ($this->_assignments == null) {
            $this->_assignments = mage::getModel('AdvancedStock/Assignment')
                    ->getCollection()
                    ->addFieldToFilter('csa_stock_id', $this->getId());
        }
        return $this->_assignments;
    }

    /**
     * Check if a stock is assign to a website
     *
     * @param unknown_type $website
     * @param unknown_type $assignment
     */
    public function isAssigned($website, $assignment) {
        foreach ($this->getAssignments() as $item) {
            if (($item->getcsa_website_id() == $website->getId()) && ($item->getcsa_assignment() == $assignment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * return product stocks for current warehouse
     *
     * @return unknown
     */
    public function getStocks() {
        if ($this->_stocks == null) {
            $this->_stocks = mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('name')
                    ->joinField('stock_qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=' . $this->getstock_id(), 'inner')
                    ->joinField('stock_location', 'cataloginventory/stock_item', 'shelf_location', 'product_id=entity_id', '{{table}}.stock_id=' . $this->getstock_id(), 'inner');
        }
        return $this->_stocks;
    }

    /**
     * return resource model
     *
     * @return unknown
     */
    public function getResource() {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('AdvancedStock/Warehouse_collection');
        }
        return $this->_resourceModel;
    }

    public function getListAsArray() {
        $retour = array();
        $collection = $this->getCollection();
        foreach ($collection as $item) {
            $retour[$item->getId()] = $item->getstock_name();
        }
        return $retour;
    }

    public function getAvailableQty($productId) {
        $value = 0;

        $stocks = mage::getModel('cataloginventory/stock_item')
                ->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('stock_id', $this->getId());

        foreach ($stocks as $stock)
            $value += $stock->getAvailableQty();

        return $value;
    }

    /**
     * return stock item for one product
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function getProductStockItem($productId) {
        return mage::getModel('cataloginventory/stock_item')
                        ->getCollection()
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('stock_id', $this->getId())
                        ->getFirstItem();
    }

    /**
     * Return product location in current warehouse
     *
     * @param unknown_type $productId
     * @return unknown
     */
    public function getProductLocation($productId) {
        $value = '';
        $stockItem = $this->getProductStockItem($productId);
        if ($stockItem)
            $value = $stockItem->getshelf_location();
        return $value;
    }

    /**
     * Set product location
     *
     * @param unknown_type $productId
     * @param unknown_type $location
     */
    public function setProductLocation($productId, $location) {
        $stockItem = $this->getProductStockItem($productId);
        if ($stockItem)
            $stockItem->setshelf_location($location)->save();
    }

}