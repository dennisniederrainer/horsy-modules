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

class TechDivision_Easylog_Block_Admin_Reimport_Grid_Renderer_Tracking
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $urlModel = Mage::getModel('adminhtml/url');
        $titel = Mage::helper('easylog')->__('Sendungsverfolgung %s', $this->_getValue($row));
        $template = Mage::app()->getLayout()->createBlock('adminhtml/template');
        $href = $urlModel->getUrl('', array('_current'=>false)) . "{$row->getIdentifier()}?___store={$row->getStoreCode()}";
        return '<a href="'.$urlModel->getUrl('*/*/track',array('identcode'=>$this->_getValue($row))).'" '
        		.'" onclick="Modalbox.show(this.href, {title: \''.$titel.'\', width: 570}); return false;">'
        		.'<img src="'.$template->getSkinUrl('images/icon_export.png').'"/> '.$this->__('Sendungsverfolgung').'</a>';
    }
}