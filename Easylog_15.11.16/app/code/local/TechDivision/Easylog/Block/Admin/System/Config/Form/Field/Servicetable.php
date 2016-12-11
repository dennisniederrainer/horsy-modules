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

class TechDivision_Easylog_Block_Admin_System_Config_Form_Field_Servicetable
	extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	const SERVICE_TABLE_PREFIX = "carriers_easylog_dhl_servicetable_";
	
    /**
     * @var Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected $_serviceRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Mage_CatalogInventory_Block_Adminhtml_Form_Field_Customergroup
     */
    protected function _getServiceRenderer()
    {
    	$shipping_code = str_replace(
    		self::SERVICE_TABLE_PREFIX, '', $this->getElement()->getId());
        if (!$this->_serviceRenderer) {
            $this->_serviceRenderer = $this->getLayout()->createBlock(
                'easylog/admin_form_field_services', '', 
            	array( 'shipping_code' => $shipping_code)
            );
        }
        return $this->_serviceRenderer;
    }
    
    public function __construct()
    {
    	$this->addColumn('service_id', array(
            'label' => Mage::helper('easylog')->__('Service'),
    		'style' => 'width:150px',
        ));
        $this->addColumn('service_price', array(
            'label' => Mage::helper('easylog')->__('Preis'),
            'style' => 'width:100px',
        ));
        parent::__construct();
        $this->setTemplate('easylog/system/config/form/field/array.phtml');
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Service hinzufügen');
    }
    
    /**
     * Get the grid and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $this->addColumn('service_id', array(
            'label' => Mage::helper('easylog')->__('Service'),
            'renderer' => $this->_getServiceRenderer(),
        ));
        return parent::_getElementHtml( $element );
    }

}