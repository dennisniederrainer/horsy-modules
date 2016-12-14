<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category       TechDivision
 * @package        TechDivision_Easylog
 * @copyright      Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license        http://opensource.org/licenses/osl-3.0.php
 *                Open Software License (OSL 3.0)
 * @author         Johann Zelger <j.zelger@techdivision.com>
 */
class TechDivision_Easylog_Model_Observer
    extends Mage_Adminhtml_Block_Template
{
    protected $_targetBlockTypes = array(
        'enterprise_salesarchive/adminhtml_sales_order_grid_massaction',
        'adminhtml/widget_grid_massaction',
    );

    protected $_targetControllerNames = array(
        'sales_order',
    );

    public function addMassAction(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        /* @var $block Mage_Adminhtml_Block_Template */
        $request = Mage::app()->getRequest();
        /* @var $request Mage_Core_Controller_Request_Http */

        if (in_array($block->getType(), $this->_targetBlockTypes)
            && Mage::getSingleton('admin/session')->isAllowed('admin/easylog/manage')
        ) {
            if (in_array($request->getControllerName(), $this->_targetControllerNames)) {
                $block->addItem('easylog_addMass',
                    array(
                        'label' => Mage::helper('easylog')->__('Easylog export'),
                        'url'   => $this->getUrl('adminhtml/manage/addmass'),
                    )
                );
            }
        }
    }
}
