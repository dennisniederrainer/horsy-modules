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

class TechDivision_Easylog_Block_Admin_Form_Field_Services
	extends Mage_Core_Block_Html_Select
{
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_services;

    /**
     * Retrieve services by shipping_code
     *
     * @param string shipping_code
     * @return array|string
     */
    protected function _getServices($shipping_code = null)
    {
        if (is_null($this->_services)) {
            $this->_services = array();
            $collection = Mage::getModel('easylog/carrier_dhl_service')
            				->getCollection();
            if ($this->getShippingCode()) {
            	$collection->addFieldToFilter('shipping_code',
            		$this->getShippingCode()
            	);
            }
            foreach ($collection as $service) {
                /* @var $item TechDivision_Easylog_Model_Carrier_Dhl_Service */
                $this->_services[$service->getEntityId()]
                	= $service->getName();
            }
        }
        return $this->_services;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getServices() as $serviceId => $serviceLabel) {
                $this->addOption($serviceId, $serviceLabel);
            }
        }
        return parent::_toHtml();
    }
}