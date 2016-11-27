<?php

class Horsebrands_Reporting_Block_Adminhtml_Orderoverview_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('horsebrands_orderoverview_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('sales/order_collection');

        $storeId = Mage::app()->getStore()->getId();
        $ridingstyle_attid = $this->getRidingStyleAttributeId();

        $collection->getSelect()
          ->joinLeft(
            array('payment' => 'sales_flat_order_payment'), 'main_table.entity_id = payment.parent_id', array('paymentmethod' => 'payment.method')
          )
          ->joinLeft(
            array('address' => 'sales_flat_order_address'), 'main_table.shipping_address_id = address.entity_id',
            array(
                'postcode' => 'address.postcode',
                'country' => 'address.country_id',
                'firstname' => 'address.firstname',
                'middlename' => 'address.middlename',
                'lastname' => 'address.lastname'
            )
          )
          ->joinLeft(
                  array('customer' => 'customer_entity'), 'main_table.customer_id = customer.entity_id', array('customerregistrationdate' => 'customer.created_at')
          );

        //  echo $collection->getSelect();

        $collection->setOrder('main_table.created_at', 'DESC');

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns() {
        //Kundennummer
        $this->addColumn('main_table.customer_id', array(
            'header' => $this->__('Kundennummer'),
            'type' => 'number',
            'align' => 'left',
            'index' => 'customer_id'
        ));
        //Bestellnummer
        $this->addColumn('main_table.increment_id', array(
            'header' => $this->__('Bestellung#'),
            'index' => 'increment_id'
        ));
        //Bestelldatum
        $this->addColumn('main_table.created_at', array(
            'header' => $this->__('Bestelldatum'),
            'type' => 'datetime',
            'index' => 'created_at'
        ));

        //Rechnung an
        $this->addColumn('address.lastname', array(
            'header' => $this->__('Rechnung an'),
            'type' => 'text',
            'width' => '100px',
            'index' => 'lastname',
            'filter_index' => 'address.lastname'
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_FullName'
        ));
        //Umsatz in Euro
        $this->addColumn('main_table.base_grand_total', array(
            'header' => $this->__('Umsatz [€]'),
            'type' => 'number',
            'index' => 'base_grand_total',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Price'
        ));
        //Bestellstatus
        $this->addColumn('main_table.state', array(
            'header' => 'Status',
            'align' => 'right',
            'type' => 'text',
            'index' => 'state'
        ));

        //Zahlart
        $this->addColumn('payment.method', array(
            'header' => 'Zahlart',
            'type' => 'text',
            'index' => 'paymentmethod',
        ));
        //PLZ
       $this->addColumn('address.postcode', array(
            'header' => 'PLZ',
            'type' => 'text',
            'index' => 'postcode'
        ));
        //Land
        $this->addColumn('address.country_id', array(
            'header' => 'Land',
            'type' => 'text',
            'index' => 'country',
        ));

        //angewandte Rabatte
        $this->addColumn('main_table.discount_amount', array(
            'header' => 'Rabatt [€]',
            'type' => 'number',
            'index' => 'discount_amount',
            //'renderer' => 'Horsebrands_Reporting_Block_Adminhtml_Renderer_Price'
        ));
        //Kunde seit
        $this->addColumn('customerregistrationdate', array(
            'header' => 'Kunde seit',
            'type' => 'datetime',
            'index' => 'customerregistrationdate'
        ));

        /*
        //Kategorie
        $this->addColumn('countorder', array(
            'header' => 'Bestellung Nummer X von Knd.',
            'index' => 'countorder',
            'renderer' => 'Horsebrands_Checklist_Block_Adminhtml_Report_Renderer_CountOrder'
        ));
        */

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportExcelXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRidingStyleAttributeId() {
        $ridingstyle_attid = Mage::getModel('eav/entity_attribute')->loadByCode(1, 'ridingstyle')->getId();

        return $ridingstyle_attid;
    }
}
