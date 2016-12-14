<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category   	TechDivision
 * @package    	TechDivision_Easylog
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 * 				Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */

class TechDivision_Easylog_Block_Admin_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setId('gridList');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easylog/pool')->getCollection();
        $this->setCollection($collection);
    }

    protected function _prepareColumns()
    {
    	$this->addColumn('pool_id',
        	array(
	            'header'    =>Mage::helper('easylog')->__('ID'),
	            'index'     =>'entity_id'
        ));
        
        $this->addColumn('order_id',
        	array(
	            'header'    =>Mage::helper('easylog')->__('BestellNr'),
	            'index'     =>'POOL_REFNR'
        ));
        
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'POOL_EMPF_NAME1'
        ));
        
        $this->addColumn('street',
            array(
                'header'=> Mage::helper('catalog')->__('Strasse'),
                'index' => 'POOL_EMPF_STRASSE'
        ));
        
        $this->addColumn('hausnr',
            array(
                'header'=> Mage::helper('catalog')->__('HausNr.'),
                'index' => 'POOL_EMPF_HAUSNR'
        ));
        
        $this->addColumn('plz',
            array(
                'header'=> Mage::helper('catalog')->__('Postleitzahl'),
                'index' => 'POOL_EMPF_PLZ'
        ));
        
        $this->addColumn('land',
            array(
                'header'=> Mage::helper('catalog')->__('Land'),
                'index' => 'POOL_EMPF_LANDCODE'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('reports')->__('CSV'));

        return parent::_prepareColumns();
    }
}
