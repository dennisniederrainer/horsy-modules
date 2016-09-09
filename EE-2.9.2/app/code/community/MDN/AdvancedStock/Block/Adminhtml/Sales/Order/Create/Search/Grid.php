<?php


class MDN_AdvancedStock_Block_Adminhtml_Sales_Order_Create_Search_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        //replace qty column
        $this->addColumn('stock', array(
            'header'=> Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'stock_summary',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'sortable'	=> false,
            'filter'	=> false
        ));
    }
}
