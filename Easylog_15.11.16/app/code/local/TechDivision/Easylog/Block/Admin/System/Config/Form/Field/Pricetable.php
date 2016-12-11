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

class TechDivision_Easylog_Block_Admin_System_Config_Form_Field_Pricetable
	extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('weight', array(
            'label' => Mage::helper('adminhtml')->__('Gewicht (bis kg)'),
            'style' => 'width:120px',
        ));
        $this->addColumn('price', array(
            'label' => Mage::helper('adminhtml')->__('Preis'),
            'style' => 'width:120px',
        ));
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Preis hinzufügen');
        parent::__construct();
    }
    
}