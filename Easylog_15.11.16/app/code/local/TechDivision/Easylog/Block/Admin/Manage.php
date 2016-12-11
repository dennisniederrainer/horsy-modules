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

class TechDivision_Easylog_Block_Admin_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	$this->_blockGroup = 'easylog';
        $this->_controller = 'admin_manage';
        parent::__construct();
    }

    protected function _prepareLayout()
    {
    	$this->_headerText = Mage::helper('easylog')->__('Easylog Verwaltung');
    	$this->_addButton('clearPool', array(
            'label'   => Mage::helper('catalog')->__('Liste leeren'),
            'onclick' => "setLocation('{$this->getUrl('*/*/clear')}')",
            'class'   => 'delete'
        ));
        $this->_removeButton('add');
        parent::_prepareLayout();
    }
}