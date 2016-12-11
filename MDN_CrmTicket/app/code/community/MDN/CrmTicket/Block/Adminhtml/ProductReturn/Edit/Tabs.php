<?php

/*
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_CrmTicket_Block_Adminhtml_ProductReturn_Edit_Tabs extends MDN_ProductReturn_Block_Productreturn_Edit_Tabs
{


    public function getRmaId()
    {
        return mage::app()->getRequest()->getParam('rma_id');
    }

    /**
     * Set tabs
     */
    protected function _beforeToHtml()
    {
        $collection = mage::helper('CrmTicket/ProductReturn')->getTicketsByProductReturnId($this->getRmaId());

        $this->addTabAfter('tickets', array(
            'label' => Mage::helper('CrmTicket')->__('Tickets').' ('.count($collection).')',
            'content' => $this->getLayout()
                ->createBlock('CrmTicket/Adminhtml_ProductReturn_Edit_Grid')
                ->setTemplate('CrmTicket/ProductReturn/ProductReturn/Grid.phtml')
                ->toHtml(),
            'active' => true
        ),
        'action');

        return parent::_beforeToHtml();
    }
}
