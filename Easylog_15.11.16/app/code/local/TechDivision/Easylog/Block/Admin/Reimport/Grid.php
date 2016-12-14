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

class TechDivision_Easylog_Block_Admin_Reimport_Grid 
	extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('easylogReimportGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('easylog/reimport')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();
        $this->addColumn('SITEMS_ID', array(
            'header'    => Mage::helper('easylog')->__('ID'),
            'align'     => 'left',
            'index'     => 'SITEMS_ID',
            'width'     => '50px'
        ));
        $this->addColumn('SITEMS_ORDERNUMBER', array(
            'header'    => Mage::helper('easylog')->__('Bestellnummer'),
            'align'     => 'left',
            'index'     => 'SITEMS_ORDERNUMBER',
        ));
        $this->addColumn('SITEMS_IDENTCODE', array(
            'header'    => Mage::helper('easylog')->__('Paketnummer'),
            'align'     => 'left',
            'index'     => 'SITEMS_IDENTCODE',
        ));
        $this->addColumn('EmpName1', array(
            'header'    => Mage::helper('easylog')->__('Empfänger Name 1'),
            'align'     => 'left',
            'index'     => 'EmpName1'
        ));
        $this->addColumn('EmpName2', array(
            'header'    => Mage::helper('easylog')->__('Empfänger Name 2'),
            'align'     => 'left',
            'index'     => 'EmpName2'
        ));
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('easylog')->__('Erstellt am'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('easylog')->__('Geändert am'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('easylog')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('easylog/reimport_status')
        					->getAllOptions(true, true),
        ));
        $this->addColumn('tracking', array(
            'header'    => Mage::helper('easylog')->__('Sendungsverfolgung'),
            'align'     => 'right',
            'index'     => 'SITEMS_IDENTCODE',
        	'renderer'  => 'easylog/admin_reimport_grid_renderer_tracking',
        ));
        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        //$this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
