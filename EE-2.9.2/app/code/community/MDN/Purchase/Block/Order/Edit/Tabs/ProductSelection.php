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
class MDN_Purchase_Block_Order_Edit_Tabs_ProductSelection extends Mage_Adminhtml_Block_Widget_Grid {

    private $_order = null;

    /**
     * D�finit l'order
     *
     */
    public function setOrderId($value) {
        $this->_order = mage::getModel('Purchase/Order')->load($value);
        return $this;
    }

    /**
     * Retourne la commande
     *
     */
    public function getOrder() {
        return $this->_order;
    }

    public function __construct() {
        parent::__construct();
        $this->setId('ProductSelection');
        //$this->_parentTemplate = $this->getTemplate();
        //$this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //$this->setVarNameFilter('product_selection');
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText($this->__('No items'));
    }

    /**
     * Charge la collection des devis
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $allowProductTypes = array();
        $allowProductTypes[] = 'simple';
        $allowProductTypes[] = 'virtual';

        $alreadyAddedProducts = array();
        foreach ($this->getOrder()->getProducts() as $item) {
            $alreadyAddedProducts[] = $item->getpop_product_id();
        }

        $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addFieldToFilter('type_id', $allowProductTypes)
                        ->addAttributeToSelect('name')
                        ->addAttributeToSelect('ordered_qty')
                        ->addAttributeToSelect('reserved_qty')
                        ->addAttributeToSelect('manufacturer')
                        ->addAttributeToSelect('waiting_for_delivery_qty')
                        ->joinField('stock',
                                'cataloginventory/stock_item',
                                'qty',
                                'product_id=entity_id',
                                '{{table}}.stock_id=1',
                                'left');

        if (mage::helper('purchase')->requireProductSupplierAssociationToAddProductInPo()) {
            $supplierNum = $this->getOrder()->getpo_sup_num();
            $collection->joinField('ref',
                    'Purchase/ProductSupplier',
                    'pps_reference',
                    'pps_product_id=entity_id',
                    'pps_supplier_num=' . $supplierNum,
                    'inner');
        }

        if (count($alreadyAddedProducts) > 0)
            $collection->addFieldToFilter('entity_id', array('nin' => $alreadyAddedProducts));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->getSelectedProducts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('purchase')->__('Qty'),
            'name' => 'qty',
            'type' => 'number',
            'index' => 'qty',
            'width' => '70',
            'editable' => true,
            'edit_only' => false,
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('sn_details', array(
            'header' => Mage::helper('purchase')->__('Details'),
            'index' => 'sn_details',
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsDetails',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'product_id_field_name' => 'entity_id',
            'product_name_field_name' => 'name'
        ));

        $this->addColumn('Sku', array(
            'header' => Mage::helper('purchase')->__('Sku'),
            'index' => 'sku',
        ));

        $this->addColumn('Name', array(
            'header' => Mage::helper('purchase')->__('Name'),
            'index' => 'name'
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this);

        $this->addColumn('stock_summary', array(
            'header' => Mage::helper('purchase')->__('Stock summary'),
            'renderer' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => false,
            'sortable' => false,
            'index' => 'stock_summary'
        ));


        $this->addColumn('supply_needs', array(
            'header' => Mage::helper('purchase')->__('Supply needs'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SupplyNeeds',
            'filter' => false,
            'sortable' => false,
            'align' => 'center'
        ));

        $this->addColumn('waiting_for_delivery_qty', array(
            'header' => Mage::helper('purchase')->__('Waiting<br>for delivery'),
            'index' => 'waiting_for_delivery_qty',
            'type' => 'number'
        ));


        $this->addColumn('manufacturer', array(
            'header' => Mage::helper('purchase')->__('Manufacturer'),
            'index' => 'manufacturer',
            'type' => 'options',
            'options' => $this->getManufacturersAsArray(),
        ));

        $this->addColumn('Suppliers', array(
            'header' => Mage::helper('purchase')->__('Suppliers'),
            'renderer' => 'MDN_Purchase_Block_Widget_Column_Renderer_ProductSuppliers',
            'filter' => 'Purchase/Widget_Column_Filter_ProductSupplier',
            'index' => 'entity_id'
        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/ProductSelectionGrid', array('_current' => true, 'po_num' => $this->getOrder()->getId()));
    }

    public function getSelectedProducts() {
        $products = $this->getRequest()->getPost('products', null);
        if (!is_array($products)) {
            $products = array();
        }
        return $products;
    }

    /**
     * Return manufacturers list as array
     *
     */
    public function getManufacturersAsArray() {
        $retour = array();

        //recupere la liste des manufacturers
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter($product->getResource()->getTypeId())
                        ->addFieldToFilter('attribute_code', 'manufacturer') // This can be changed to any attribute code
                        ->load(false);
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);

        //ajoute au menu
        foreach ($manufacturers as $manufacturer) {
            $retour[$manufacturer['value']] = $manufacturer['label'];
        }

        return $retour;
    }

}
