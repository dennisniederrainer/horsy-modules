<?php

class MDN_AdvancedStock_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    protected function _prepareColumns()
    {

        parent::_prepareColumns();

        //replace qty column
        $this->addColumn('qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'stock_summary',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'sortable'	=> false,
            'filter'	=> false
        ));


    }

}
