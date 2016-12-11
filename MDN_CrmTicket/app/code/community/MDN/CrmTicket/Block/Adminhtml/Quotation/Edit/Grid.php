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
class MDN_CrmTicket_Block_Adminhtml_Quotation_Edit_Grid extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

  public function __construct()
  {
      parent::__construct();

      $this->setId('quotation_ticket');
      $this->_parentTemplate = $this->getTemplate();

    

      $this->setEmptyText('No Items');
      $this->setDefaultSort('ct_created_at');
      $this->setDefaultDir('DESC');
    //$this->setDefaultLimit(50);//make it slow
    //$this->setRowClickCallback(false);
    $this->setSaveParametersInSession(true);
      $this->setUseAjax(true);
  }


  
  /**
   * load collection with join
   *
   * @return unknown
   */
  protected function _prepareCollection()
  {
      $collection = mage::helper('CrmTicket/Quotation')->getTicketsByQuotationId($this->getRequest()->getParam('quote_id'));

      $this->setCollection($collection);

      return parent::_prepareCollection();
  }

  /**
   * columns grid
   *
   * @return unknown
   */
  protected function _prepareColumns()
  {
      $helper = Mage::helper('CrmTicket');

      $this->addColumn('ct_id', array(
        'header' => $helper->__('Id'),
        'index' => 'ct_id',
        'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_IdLink',
        'align' => 'center',
        'width' => '20px'
    ));

      $this->addColumn('ct_created_at', array(
        'header' => $helper->__('Created at'),
        'index' => 'ct_created_at',
        'type' => 'datetime'
    ));

      $this->addColumn('ct_updated_at', array(
        'header' => $helper->__('Updated at'),
        'index' => 'ct_updated_at',
        'type' => 'datetime'
    ));

      $this->addColumn('ct_subject', array(
        'header' => $helper->__('Subject'),
        'index' => 'ct_subject',
        'type' => 'varchar'
    ));

      $this->addColumn('object', array(
        'header' => $helper->__('Object'),
        'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerObject',
        'width' => '100px',
        'filter' => false,
        'sort' => false
    ));

      $this->addColumn('ct_status', array(
        'header' => $helper->__('Status'),
        'index' => 'ct_status',
        'type' => 'options',
        'options' => Mage::getModel('CrmTicket/Ticket')->getStatuses(),
        'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status',
        'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelect'
    ));

      $this->addColumn('ct_manager', array(
        'header' => $helper->__('Manager'),
        'index' => 'ct_manager',
        'type' => 'options',
        'options' => $this->getManagers()
    ));

      return parent::_prepareColumns();
  }

  /**
   *
   * @return type
   */
  public function getGridParentHtml()
  {
      $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
      return $this->fetchView($templateName);
  }

  /**
   */
  public function getRowUrl($row)
  {
      $customer_id = $row->getCustomer()->getId();
      return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $row->getct_id(), 'customer_id' => $customer_id));
  }

  //callback de ajax
  public function getGridUrl()
  {
      //not */*/ because can be called via Customer part
    return $this->getUrl('CrmTicket/Admin_Ticket/CustomerTicketGridAjax', array('_current' => true));
  }

  /**
   * return managers (magento user)
   *
   */
  public function getManagers()
  {
      $users = array();

      $magentoUsers = mage::getSingleton('admin/user')->getCollection();

      foreach ($magentoUsers as $manager) {
          $users[$manager->getId()] = $manager->getusername();
      }

      return $users;
  }

  
  //******* tab **********************************************************************

  public function getTabLabel()
  {
      return $this->__('Tickets');
  }

    public function getTabTitle()
    {
        return $this->__('Tickets');
    }

  /**
   * Can show tab in tabs
   *
   * @return boolean
   */
  public function canShowTab()
  {
      return true;
  }

  /**
   * Tab is hidden
   *
   * @return boolean
   */
  public function isHidden()
  {
      return false;
  }

  /**
   * Defines after which tab, this tab should be rendered
   *
   * @return string
   */
  public function getAfter()
  {
      return 'commercial';
  }
}
