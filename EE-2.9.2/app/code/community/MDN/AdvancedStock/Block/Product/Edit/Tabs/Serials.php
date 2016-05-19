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
class MDN_AdvancedStock_Block_Product_Edit_Tabs_Serials extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Product get/set
	 *
	 * @var unknown_type
	 */
	private $_product = null;
	public function setProduct($Product)
	{
		$this->_product = $Product;
		return $this;
	}
	public function getProduct()
	{
		return $this->_product;
	}
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('ProductSerialsGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
		//charge les mouvements de stock
		$collection = Mage::getModel('AdvancedStock/ProductSerial')
					->getCollection()
					->addFieldToFilter('pps_product_id', $this->getProduct()->getId());
                 
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * Défini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
                               
        $this->addColumn('pps_id', array(
            'header'=> Mage::helper('AdvancedStock')->__('Id'),
            'index' => 'pps_id',
            'align' => 'center'
        ));
        
        $this->addColumn('pps_serial', array(
            'header'=> Mage::helper('AdvancedStock')->__('Serial'),
            'index' => 'pps_serial',
            'align' => 'center'
        ));    
        
        $this->addColumn('pps_purchase_order', array(
            'header'=> Mage::helper('purchase')->__('Purchase Order'),
            'align' => 'center',
            'renderer' => 'MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_PurchaseOrder',
            'sortable' => false,
            'filter' => false
        ));
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return $this->getUrl('AdvancedStock/Products/ProductSerialGrid', array('_current'=>true, 'product_id' => $this->getProduct()->getId()));
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    public function getRowUrl($row)
    {
    	//nothing
    }

}
