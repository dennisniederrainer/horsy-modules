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

class TechDivision_Easylog_Model_Pool 
	extends Mage_Core_Model_Abstract
{
	const XML_PATH_ALLGEMEIN_MANDANTID 		= 'easylog/allgemein/mandant_id';
	const XML_PATH_ALLGEMEIN_ABSENDERID		= 'easylog/allgemein/absender_id';
	const XML_PATH_ALLGEMEIN_TEILNAHMEID	= 'easylog/allgemein/teilnahme_id';
	const XML_PATH_EXTRAS_RETOURE			= 'easylog/extras/retoure';
	const SERVICE_DELIMITER					= '#';
	
	protected $_extras = array();
		
    /**
     * Initialize resources
     * @return void
     */
    protected function _construct()
    {
        $this->_init('easylog/pool');
    }
    
    /**
     * Clears the Database table
     * @return bool
     */
    protected function _clearTable()
    {
        return $this->getResource()->clear();
    }
    
    /**
     * Loads a poolData by order_id
     * @param $order_id
     * @return TechDivision_Easylog_Model_Pool
     */
    public function loadByOrderId($order_id)
    {
        $this->_getResource()->loadByOrderId($this, $order_id);
        return $this;
    }
    
    /**
     * Clears the Database table public
     * @return bool
     */
    public function clear() {
    	$del = $this->_clearTable();
    	return $del;
    }
    
    /**
     * Saves and converts an order to poolData
     * @param Mage_Sales_Model_Order $_order
     * @return void
     */
    public function saveByOrder($_order) {
    	if (($_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_MANDANTID)) &&
    		($_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_ABSENDERID)) &&
    		($_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_TEILNAHMEID))) 
    	{
    		// set general settings
			$this->setData('order_id', $_order->getId());
			$this->setData('POOL_V_MAND_REFNR', $_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_MANDANTID));
			$this->setData('POOL_V_ABS_REFNR', $_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_ABSENDERID));
			$this->setData('POOL_V_TEILNAHME', $_order->getStore()->getConfig(self::XML_PATH_ALLGEMEIN_TEILNAHMEID));
			$this->setData('POOL_REFNR', $_order->getData('increment_id'));
			
			// set recipient data
			$_shippingAddress = $_order->getShippingAddress();
			if ($_shippingAddress->getData('company')) {
				$this->setData('POOL_EMPF_NAME1', $_shippingAddress->getData('company'));
				$this->setData('POOL_EMPF_NAME2', $_shippingAddress->getName());
			} else {
				$this->setData('POOL_EMPF_NAME1', $_shippingAddress->getName());
			}
			$this->setData('POOL_EMPF_NAME3', $_shippingAddress->getStreet2());
			$this->setData('POOL_EMPF_PLZ', $_shippingAddress->getData('postcode'));
			$this->setData('POOL_EMPF_ORT', $_shippingAddress->getData('city'));
			$this->setData('POOL_EMPF_TEL', $_shippingAddress->getData('telephone'));
			$this->setData('POOL_EMPF_EMAIL', $_order->getData('customer_email'));
			$this->setData('POOL_EMPF_LANDCODE', $_shippingAddress->getData('country_id'));
	
			// parsed street setter
			$_street = preg_match("/([^0-9]+)([0-9]+.*)?/",
				$_shippingAddress->getStreet1(), $street);
			if (isset($street[1])) $this->setData('POOL_EMPF_STRASSE', trim($street[1]));
			if (isset($street[2])) $this->setData('POOL_EMPF_HAUSNR', trim($street[2]));
			
			// get full weight for the whole package / order
			$this->setData('POOL_GEWICHT', $_order->getWeight());
			
			// check global extras
			if (($_order->getStore()->getConfig(self::XML_PATH_EXTRAS_RETOURE))
			   &&($_shippingAddress->getData('country_id') == 'DE')) {
				$this->_addExtra('703');	
			}

			// check shipping services
			$_service = explode(
				self::SERVICE_DELIMITER, $_order->getShippingMethod());
			if (isset($_service[1])) {
				$_serviceModel = Mage::getModel('easylog/carrier_dhl_service')
					->load($_service[1]);
				$_order->setShippingMethod($_service[0]);
				$this->_addExtra($_serviceModel->getDhlId());
			}
			
			// get ids from carrier dhl model
			$dhl = Mage::getSingleton('easylog/carrier_dhl');
			$ids = $dhl->getCode(
				'ids', str_replace(
					$dhl->getRealCarrierCode() . '_',
					'', $_order->getShippingMethod()
				), $_order->getStoreId()
			);
            
			// set extras
			$this->setData('POOL_V_EXTRASLST',$this->getExtras());

			// set ids
			if ((is_array($ids))&&(isset($ids['verfahren']))) {
				$this->setData('POOL_V_VERFAHREN', $ids['verfahren']);
				$this->setData('POOL_V_PRODUKT_CN', $ids['product']);
                // to override default teilnahme number with specific ones if set
                if (array_key_exists('teilnahme', $ids) && !empty($ids['teilnahme'])) {
                    $this->setData('POOL_V_TEILNAHME', $ids['teilnahme']);
                }
				$this->save();
			}
		} else {
			throw new Exception(
            	Mage::helper('easylog')->__('Bitte geben die Mandant, Absender- und Teilnahme ID unter Easylog Einstellungen an.')
            );
		}
    }
    
    /**
     * Adds an extra ID
     * 
     * @param string $serviceId
     * @return void 
     */
    protected function _addExtra( $serviceId ) {
    	$this->_extras[] = $serviceId;
    }
    
    /**
     * Gets all Extras
     * 
     * @return string
     */
    public function getExtras() {
    	return implode(';', $this->_extras);
    }
}