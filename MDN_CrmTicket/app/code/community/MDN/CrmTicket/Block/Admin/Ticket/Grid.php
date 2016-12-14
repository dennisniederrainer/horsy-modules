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
class MDN_CrmTicket_Block_Admin_Ticket_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public $_showManager = 1;

    public function __construct() {
        parent::__construct();

        $this->setId('CrmTicketsGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Items');
        
        $this->setDefaultSort('ct_updated_at');
        $this->setDefaultDir('DESC');
    
        
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        //Set default filters
        $filter = array();

        //get from conf the default values for status filter
        $filterConf = Mage::getStoreConfig('crmticket/general/ticket_grid_default_status');
        if($filterConf){
          $confArray = explode(',', $filterConf);
          $filters = array();
          foreach($confArray as $defaultStatus){
            $filters[$defaultStatus] = 1;
          }
          $filter['ct_status'] = $filters;
        }

        //Apply filter if necessary
        if(count($filter)>0){
          $this->setDefaultFilter($filter);
        }
        
    }

    public function setManager(){
        $this->_showManager = Mage::getStoreConfig('crmticket/ticket_data/show_manager');
    }

    public function displayManagerColumn(){
        return $this->_showManager;
    }


    /**
     * load ticket collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        // table 'customer_entity' to get cutomer id | table 'customer_entity_varchar' to get fist name and last name
        $collection = Mage::getModel('CrmTicket/Ticket')
                ->getCollection()
                ->join('customer/entity', 'ct_customer_id=entity_id');

        $prefix = Mage::getConfig()->getTablePrefix();
        $collection->getSelect()->joinLeft(array('prty' => $prefix . 'crm_ticket_priority'), 'main_table.ct_priority = prty.ctp_id', array('ctp_id', 'ctp_priority_value'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->setManager();
        
        $helper = Mage::helper('CrmTicket');

        $this->addColumn('ct_id', array(
            'header' => $helper->__('Id'),
            'index' => 'ct_id',
            'type' => 'number',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_IdLink',
            'align' => 'center',
            'width' => '15px'
        ));

        $this->addColumn('ct_updated_at', array(
            'header' => $helper->__('Updated at'),
            'index' => 'ct_updated_at',
            'type' => 'datetime',
            'width' => '100px'            
        ));

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_cost')) {
            $this->addColumn('ct_deadline', array(
                'header' => $helper->__('Dead line'),
                'index' => 'ct_deadline',
                'width' => '100px',
                'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Deadline'
            ));
        }

        $this->addColumn('customer', array(
            'header' => $helper->__('Customer'),
            'index' => 'email',
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerName',
        ));

        $this->addColumn('object', array(
            'header' => $helper->__('Object'),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_CustomerObject',
            'width' => '100px',
            'filter' => false,
            'sort' => false
        ));

        if ($this->displayManagerColumn()) {
            $this->addColumn('ct_manager', array(
                'header' => $helper->__('Manager'),
                'index' => 'ct_manager',
                'type' => 'options',
                'options' => $this->getManagers()
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_subject')) {
            $this->addColumn('ct_subject', array(
                'header' => $helper->__('Subject'),
                'index' => 'ct_subject',
                'width' => '250px',
                'type' => 'varchar',
                'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Subject'
            ));
         }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_view_count')) {
          $this->addColumn('ct_msg_count', array(
              'header' => $helper->__('Msgs'),
              'index' => 'ct_msg_count',
              'width' => '20px',
              'align' => 'center'
          ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_sticky')) {
            $this->addColumn('ct_sticky', array(
                'header' => $helper->__('Sticky'),
                'index' => 'ct_sticky',
                'width' => '20px',
                'type' => 'options',
                'options' => array(
                    '1' => $helper->__('Yes'),
                    '0' => $helper->__('No'),
                ),
                'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Sticky',
                'align' => 'center'
            ));
        }

        $this->addColumn('ct_status', array(
            'header' => $helper->__('Status'),
            'index' => 'ct_status',
            'type' => 'options',
            'width' => '110px',
            'align' => 'center',
            'options' => mage::getModel('CrmTicket/Ticket')->getStatuses(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Status',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelect'
        ));

        $this->addColumn('ct_category_id', array(
            'header' => $helper->__('Category'),
            'index' => 'ct_category_id',
            'type' => 'options',
            'options' => $this->getCategories(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Category',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Category'
        ));

        $this->addColumn('ct_tags', array(
            'header' => $helper->__('Tags'),
            'index' => 'ct_id',            
            'align' => 'center',
            'options' => mage::helper('CrmTicket/Tag')->getAllTags(),
            'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_TicketTags',
            'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_MultiSelectTag'
        ));

        if ($helper->allowProductSelection()) {
            $this->addColumn('ct_product_id', array(
                'header' => $helper->__('Product'),
                'index' => 'ct_product_id',
                'type' => 'options',
                'options' => $this->getProducts()
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_data/show_priority')) {
            $this->addColumn('ct_priority', array(
                'header' => $helper->__('Priority'),
                'index' => 'ctp_priority_value',
                'align' => 'center',
                'width' => '50px',
                'type' => 'options',
                'options' => $this->getPriorities()
            ));
            //ct_priority
            //ctp_priority_value
        }

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_cost')) {
            $this->addColumn('ct_cost', array(
                'header' => $helper->__('Cost'),
                'index' => 'ct_cost',
                'type' => 'number'
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('ct_store_id', array(
                'header' => $helper->__('Store'),
                'index' => 'ct_store_id',
                'type' => 'store'
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_view_count')) {
            $this->addColumn('ct_nb_view', array(
                'header' => $helper->__('Nb View'),
                'index' => 'ct_nb_view',
                'width' => '20px',
                'type'  => 'number',
                'align' => 'center'
            ));
        }

        if (Mage::getStoreConfig('crmticket/ticket_grid/show_message_popup')) {
          $this->addColumn('ct_message', array(
              'header' => $helper->__('Messages'),
              'index' => 'ct_id',
              'align' => 'center',
              'width' => '20px',
              'filter' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Filter_Search',
              'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Messages'
          ));
        }

        //launch event
        Mage::dispatchEvent('crmticket_ticket_grid_prepare_columns', array('grid' => $this));

        
        if (Mage::getStoreConfig('crmticket/ticket_grid/display_quick_actions')) {
            $this->addColumn('ct_quickaction', array(
                  'header' => $helper->__('Quick action'),
                  'index' => 'ct_id',
                  'renderer' => 'MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_QuickActions',
                  'align' => 'center',
                  'filter' => false,
                  'sort' => false
             ));
        }

        return parent::_prepareColumns();
    }

    /**
     *
     * @return type
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    /**
     * Disable for Ajax button
     */
    public function getRowUrl($row) {
        $customer_id = $row->getCustomer()->getId();
        return $this->getUrl('CrmTicket/Admin_Ticket/Edit', array('ticket_id' => $row->getct_id(), 'customer_id' => $customer_id));
    }

    /**
     * Ajax callback
     */

    public function getGridUrl() {

        return $this->getUrl('*/*/AllGridAjax', array('_current' => true));
    }


    /**
     * add mass action to assign pruduct to a collection
     */
    protected function _prepareMassaction() {

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('TicketsList');

        $helper = Mage::helper('CrmTicket');

        $this->getMassactionBlock()->addItem('category', array(
            'label' => $helper->__('Assign to a category'),
            'url' => $this->getUrl('*/*/MassTickets/category/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'categories',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Category'),
                    'values' => $this->getCategoryMenuForMassAction()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => $helper->__('Change status'),
            'url' => $this->getUrl('*/*/MassTickets/status/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'statuses',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Status'),
                    'values' => mage::getModel('CrmTicket/Ticket')->getStatuses()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('user', array(
            'label' => $helper->__('Change user'),
            'url' => $this->getUrl('*/*/MassTickets/user/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'users',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('User'),
                    'values' => $this->getUsersMenuForMassAction()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('tag', array(
            'label' => $helper->__('Add tag'),
            'url' => $this->getUrl('*/*/MassTickets/tag/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'tags',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Tags'),
                    'values' => mage::helper('CrmTicket/Tag')->getAllTags()
                )
            )
        ));

        $this->getMassactionBlock()->addItem('spam', array(
            'label' => $helper->__('Mark as spam '),
            'url' => $this->getUrl('*/*/MassTickets/spam/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'spam',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Spam'),
                    'values' => mage::getModel('CrmTicket/EmailSpam')->getSpamActions()
                )
            )
        ));

        if (Mage::getStoreConfig('crmticket/ticket_grid/mass_delete_ticket')) {
          $this->getMassactionBlock()->addItem('delete', array(
              'label' => $helper->__('Delete ticket'),
              'url' => $this->getUrl('*/*/MassDeleteTickets/', array('_current' => true))
          ));
        }

        return $this;
    }

    private function getUsersMenuForMassAction(){
       $usersMenu = array();
       $managers = mage::getSingleton('admin/user')->getCollection()->setOrder('username', 'asc');
       foreach ($managers as $manager) {
          $manager_id = $manager->getId();
          $manager_name = ucfirst(strtolower(trim($manager->getusername())));
          $usersMenu[$manager_id] = $manager_name;
       }
       return  $usersMenu;
    }

    private function getCategoryMenuForMassAction(){
       $categoryMenu = array();


        //Private cetegories
        $privateCategories = Mage::getModel('CrmTicket/Category')->getPrivateCategories();
        if ($privateCategories->getSize() > 0) {
          $categoryMenu[] = "** PRIVATE **";
          foreach ($privateCategories as $cat) {
              $categoryMenu[$cat->getId()] = "--- " . $cat->getctc_name() . " ---";
          }
        }

        //general categories (3 level implemented)
        $mainCategories = Mage::getModel('CrmTicket/Category')->getGeneralCategories();
        if ($mainCategories->getSize() > 0) {
          $categoryMenu[] = "** GENERAL **";
          foreach ($mainCategories as $cat) {
              $catId = $cat->getId();
              $categoryMenu[$catId] = "--- " . $cat->getctc_name();
              $subCategories = Mage::getModel('CrmTicket/Category')->getSubCategories($catId);
              foreach ($subCategories as $subcat) {
                  $scatId = $subcat->getId();
                  $categoryMenu[$scatId] = "------ " . $subcat->getctc_name();
                  $ssCategories = Mage::getModel('CrmTicket/Category')->getSubCategories($scatId);
                  foreach ($ssCategories as $ssubcat) {
                      $sscatId = $ssubcat->getId();
                      $categoryMenu[$sscatId] = "--------- " . $ssubcat->getctc_name();
                  }
              }
          }
        }

        //product categories
        $products = Mage::helper('CrmTicket/Product')->getProducts();
        foreach ($products as $product) {
            $productCategories = Mage::getModel('CrmTicket/Category')->getProductCategories($product->getId());
            if ($productCategories->getSize() > 0) {
                $categoryMenu[] = "** " . $product->getName() . " **";
                foreach ($productCategories as $cat) {
                    $categoryMenu[$cat->getId()] = "--- " . $cat->getctc_name() . " ---";
                }
            }
        }
        return $categoryMenu;
    }

    /**
     * return managers (magento user)
     *
     */
    public function getManagers() {

        $users = array();

        $magentoUsers = mage::getSingleton('admin/user')->getCollection();

        foreach ($magentoUsers as $manager) {
            $users[$manager->getId()] = $manager->getusername();
        }

        return $users;
    }


    /**
     * return categories
     */
    public function getCategories() {
        $collection = Mage::getModel('CrmTicket/Category')->getCollection();
        $categories = array();
        foreach ($collection as $item) {
            $categories[$item->getId()] = $item->getctc_name();
        }

        return $categories;
    }

    /**
     * return categories
     */
    public function getPriorities() {
        $collection = Mage::getModel('CrmTicket/Ticket_Priority')->getCollection();
        $priorities = array();
        foreach ($collection as $item) {
            $priorities[$item->getId()] = $item->getctp_name();
        }

        return $priorities;
    }

    /**
     * Return products for filter
     * @return type
     */
    public function getProducts() {
        $collection = Mage::helper('CrmTicket/Product')->getProducts();
        $products = array();
        foreach ($collection as $item) {
            $products[$item->getId()] = $item->getname();
        }
        return $products;
    }



    /**
     * for ajax call to refrrhs grid if new mail comes
     */
    public function getUrlProcessNewMail() {
        return $this->getUrl('*/*/GetNewMailsAjax');
    }


    public function getRefreshRate(){
        return (int)Mage::getStoreConfig('crmticket/ticket_grid/auto_refresh_grid_rate');
    }

    public function isRefreshEnabled(){
        return (int)Mage::getStoreConfig('crmticket/ticket_grid/auto_refresh_grid');
    }
}

