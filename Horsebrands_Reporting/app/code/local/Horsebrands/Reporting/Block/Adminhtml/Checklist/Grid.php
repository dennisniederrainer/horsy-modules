<?php

class Horsebrands_Reporting_Block_Adminhtml_Checklist_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('horsebrands_checklist_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('sales/order_item_collection');

        $storeId = Mage::app()->getStore()->getId();
        $supskuid = $this->getSupplierIdAttributeId();

        $collection->getSelect()
                // ->joinLeft(
                //         array('productentity' => 'catalog_product_entity_varchar'), 'main_table.product_id = productentity.entity_id and productentity.attribute_id = ' . $supskuid, array('supplier_sku' => 'productentity.value')
                // )
          ->join(
                  array('order' => 'sales_flat_order'), 'main_table.order_id = order.entity_id', array('increment_id' => 'increment_id',
              'order.created_at' => 'created_at',
              'customer_lastname' => 'customer_lastname',
              'state' => 'state')
          );
          // ->join(
          //         array('erpoi' => 'erp_sales_flat_order_item'), 'main_table.item_id = erpoi.esfoi_item_id', array('reserved_qty' => 'reserved_qty')
          // )
          // ->joinLeft(
          //         array('product' => 'catalog_product_flat_3'), 'product.entity_id = main_table.product_id', array('product.price' => 'price', 'product.cost' => 'cost')
          // )
          // ->where('main_table.product_type <> \'configurable\'');

        //join catalog_product_flat_3 As product on product.entity_id = sfqi.product_id
        //herstellercode: ersten 3 stellen von sku
        //kategorie: ersten 10 stellen von sku
        //echo $collection->getSelect();

        $collection->setOrder('order.created_at', 'DESC');

        // echo $collection->getSelect();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        #$currency = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        //Bestelldatum
        $this->addColumn('purchased_on', array(
            'header' => $this->__('Bestelldatum'),
            'type' => 'datetime',
            'index' => 'order.created_at'
        ));
        //Bestellnummer
        $this->addColumn('increment_id', array(
            'header' => $this->__('Bestellung#'),
            'index' => 'increment_id'
        ));
        //Nachname
        $this->addColumn('customer_lastname', array(
            'header' => 'Nachname',
            'align' => 'right',
            'sortable' => false,
            'type' => 'text',
            'index' => 'customer_lastname'
        ));
        //Bestellstatus
        $this->addColumn('state', array(
            'header' => 'Status Bestellung',
            'align' => 'right',
            'sortable' => false,
            'type' => 'text',
            'index' => 'state'
        ));

        //Artikelnummer
        $this->addColumn('sku', array(
            'header' => 'Artikelnummer',
            'width' => '200px',
            'align' => 'right',
            'sortable' => true,
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'main_table.sku'
        ));
        //Hersteller-Artikelnummer
        $this->addColumn('supplier_sku', array(
            'header' => $this->__('Hersteller-Artikelnummer'),
            'align' => 'right',
            'sortable' => false,
            'type' => 'text',
            'index' => 'supplier_sku',
            'filter_index' => 'productentity.value'
        ));
        //Artikelname
        $this->addColumn('main_table.name', array(
            'header' => 'Artikelname',
            'align' => 'right',
            'sortable' => false,
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'main_table.name'
        ));

        //Bestellmenge
        $this->addColumn('qty_ordered', array(
            'header' => '# Bestellt', #Mage::helper('reports')->__('Quantity Ordered'),
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'qty_ordered',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_QtyColors'
        ));
        //Reservierte Menge
        $this->addColumn('reserved_qty', array(
            'header' => '# RESERVIERT',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'reserved_qty',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_QtyColors'
        ));
        //Stornierte Menge
        $this->addColumn('qty_canceled', array(
            'header' => '# Storniert',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'qty_canceled'
        ));
        //Gutgeschriebene Menge
        $this->addColumn('qty_refunded', array(
            'header' => '# Gutschrift',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'qty_refunded'
        ));
        //Versandte Menge
        $this->addColumn('qty_shipped', array(
            'header' => '# Versand',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'qty_shipped'
        ));

        //Einkaufspreis Einzeln
        $this->addColumn('cost', array(
            'header' => 'Einkaufspreis - Einzeln',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'product.cost',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Price'
        ));
        //Einkaufspreis Summe
        $this->addColumn('costsum', array(
            'header' => 'Einkaufspreis - Summe',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'costsum',
            'filter' => false,
            'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_CostSum'
        ));
        //Verkaufspreis Einzeln
        $this->addColumn('price', array(
            'header' => 'Verkaufspreis - Einzeln',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'product.price',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Price'
        ));
        //Verkaufspreis Summe
        $this->addColumn('pricesum', array(
            'header' => 'Verkaufspreis - Summe',
            'align' => 'right',
            'sortable' => false,
            'type' => 'number',
            'index' => 'pricesum',
            'filter' => false,
            'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_PriceSum'
        ));

        //Herstellercode
        $this->addColumn('suppliercode', array(
            'header' => 'Herstellercode',
            'index' => 'suppliercode',
            'filter' => false,
            'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Suppliercode'
        ));
        //Kategorie
        $this->addColumn('category', array(
            'header' => 'Kategorie',
            'index' => 'category',
            'filter' => false,
            'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Category'
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcelXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /*public function getRowUrl($row) {
        return $this->getUrl('AdvancedStock/Products/Edit', array()) . "product_id/" . $row->getId();
    }*/

    public function getSupplierIdAttributeId() {
        $supplierIdAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'supplier_id')->getId();

        return $supplierIdAttributeId;
    }
}
