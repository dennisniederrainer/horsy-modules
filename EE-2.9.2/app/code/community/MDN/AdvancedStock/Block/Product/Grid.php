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
class MDN_AdvancedStock_Block_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('AdvancedStock')->__('No Items'));
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {	
    		
    	//Charge la collection
        $collection = Mage::getModel('Catalog/Product')
        	->getCollection()
        	->addAttributeToSelect('name')
        	->addAttributeToSelect('cost')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('waiting_for_delivery_qty')
            ->addAttributeToSelect('visibility');

        //add margin column (depending of price includes taxes setting)
        if (!mage::getStoreConfig('tax/calculation/price_includes_tax'))
        {
            $collection->addExpressionAttributeToSelect('margin',
                    'round(({{price}} - {{cost}}) / {{price}} * 100, 2)',
                     array('price', 'cost'));
            $collection->addAttributeToSelect('price');
        }
        else
        {
            $defaultTaxRate = Mage::getStoreConfig('purchase/purchase_product/pricer_default_tax_rate');
            $coef = 1 + ($defaultTaxRate / 100);
            $collection->addExpressionAttributeToSelect('margin',
                    'round((({{price}} / '.$coef.') - {{cost}}) / ({{price}} / '.$coef.') * 100, 2)',
                     array('price', 'cost'));
            $collection->addExpressionAttributeToSelect('price_excl_tax',
                    '({{price}} / '.$coef.')',
                     array('price'));
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	$this->addColumn('organiser', array(
            'header'=> Mage::helper('Organizer')->__('Organizer'),
       		'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Comments',
            'align' => 'center',
            'entity' => 'product',
            'filter' => false,
            'sort' => false
        ));

    	$this->addColumn('barcode', array(
            'header'=> Mage::helper('AdvancedStock')->__('Barcode'),
       		'renderer'  => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_Barcode',
            'filter' => 'AdvancedStock/Product_Widget_Grid_Column_Filter_Barcode',
       		'align' => 'center',
            'entity' => 'product',
            'sort' => false,
            'index' => 'entity_id'
        ));
               
        $this->addColumn('Sku', array(
            'header'=> Mage::helper('AdvancedStock')->__('Sku'),
            'index' => 'sku'
        ));
        
        $this->addColumn('name', array(
            'header'=> Mage::helper('AdvancedStock')->__('Name'),
            'index' => 'name',
        ));

        mage::helper('AdvancedStock/Product_ConfigurableAttributes')->addConfigurableAttributesColumn($this);
        
        $this->addColumn('buy_price', array(
            'header'=> Mage::helper('AdvancedStock')->__('Buy Price'),
            'index' => 'cost',
            'type'	=> 'price',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        if (!mage::getStoreConfig('tax/calculation/price_includes_tax'))
        {
            $this->addColumn('sell_price', array(
                'header'=> Mage::helper('AdvancedStock')->__('Sell Price'),
                'index' => 'price',
                'type'	=> 'price',
                'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                'align'	=> 'center'
            ));
        }
        else
        {
            $this->addColumn('sell_price', array(
                'header'=> Mage::helper('AdvancedStock')->__('Sell Price'),
                'index' => 'price_excl_tax',
                'type'	=> 'price',
                'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                'align'	=> 'center'
            ));
        }
		$this->addColumn('margin', array(
            'header'=> Mage::helper('AdvancedStock')->__('Margin %'),
            'index' => 'margin',
            'type'	=> 'number',
            'align'	=> 'center'
        ));

        $this->addColumn('stock_summary', array(
            'header'=> Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'stock_summary',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'sortable'	=> false,
            'filter'	=> false
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('AdvancedStock')->__('Status'),
            'width'     => '80',
            'index'     => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        $this->addColumn('visibility', array(
            'header'    => Mage::helper('AdvancedStock')->__('Visibility'),
            'width'     => '80',
            'index'     => 'visibility',
            'type'  => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
        ));
        
        //raise event to allow other modules to add columns
        Mage::dispatchEvent('advancedstock_product_grid_preparecolumns', array('grid'=>$this));
        
        $this->addExportType('AdvancedStock/Products/exportCsv', Mage::helper('AdvancedStock')->__('CSV'));
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        //nothing
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
    	return $this->getUrl('AdvancedStock/Products/Edit', array())."product_id/".$row->getId();
    }
    
}
