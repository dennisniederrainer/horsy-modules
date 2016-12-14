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
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Tag_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('TagGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
    }

    /**
     * 
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
        $collection = Mage::getModel('CrmTicket/Tag')
                ->getCollection();
       
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('ctg_id', array(
            'header'=> Mage::helper('CrmTicket')->__('Id'),
            'index' => 'ctg_id',
            'width' => '10px'
        ));

        $this->addColumn('ctg_rendering', array(
            'header'=> Mage::helper('CrmTicket')->__('Rendering'),
            'index' => 'ctg_id',
            'align' => 'center',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Tag',
            'width' => '150px'
        ));
               
        $this->addColumn('ctg_name', array(
            'header'=> Mage::helper('CrmTicket')->__('Name'),
            'index' => 'ctg_name'
            
        ));
        
        $this->addColumn('ctg_bg_color', array(
            'header'=> Mage::helper('CrmTicket')->__('Background color'),
            'index' => 'ctg_bg_color',
            'width' => '60px'
        ));

        $this->addColumn('ctg_text_color', array(
            'header'=> Mage::helper('CrmTicket')->__('Text color'),
            'index' => 'ctg_text_color',
            'width' => '60px'
        ));

        
        
        return parent::_prepareColumns();
    }

    /**
     *
     * @return type 
     */
    public function getGridParentHtml()
    { 
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

     /**
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('CrmTicket/Admin_Tag/Edit', array('ctg_id' => $row->getId()));
    }
 
    
    /**
     * new category url 
     */
    public function getUrlNew(){
        return $this->getUrl('CrmTicket/Admin_Tag/Edit');
    }
    
}

