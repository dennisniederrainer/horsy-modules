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

class TechDivision_Easylog_Model_Carrier_Dhl
	extends Mage_Shipping_Model_Carrier_Abstract {

	/**
	 * Unique internal shipping method identifier
	 * @var string [a-z0-9_]
	 */
	protected $_code = 'easylog_dhl';
	protected $_euroLandcodes = 'PT,ES,FR,BE,LU,IT,MT,GB,IE,SE,FI,EE,LT,LV,PL,CZ,SK,AT,HU,SI,NL,DK,CY,GR,RO,BG';
	
	
	/**
	 * Method service collection option array
	 * @var unknown_type
	 */
	protected $_serviceCollection = null;
	
	const CODE_NATIONAL				= 'national';
	const CODE_NATIONALEXPRESS		= 'nationalexpress';
	const CODE_REGIOAT				= 'regioat';
	const CODE_EURO					= 'euro';
	const CODE_EUROPLUS				= 'europlus';
	const CODE_INTERNATIONAL		    = 'international';
	const CODE_INTERNATIONALEXPRESS	= 'internationalexpress';

	const XML_PATH_PRICETABLE_PREFIX = 'carriers/easylog_dhl/pricetable_';
	const XML_PATH_SERVICETABLE_PREFIX = 'carriers/easylog_dhl/servicetable_';
    
    const XML_PATH_PARTICIPATION_NUMBER_PREFIX = 'carriers/easylog_dhl/participation_number_';
    const XML_PATH_PARTICIPATION_NUMBER_SERVICETABLE_PREFIX = 'carriers/easylog_dhl/participation_number_servicetable';
	
	public function getCode($type, $code='', $store_Id = null)
    {
        $codes = array(
            'method'=>array(
                self::CODE_NATIONAL 			    => Mage::helper('easylog')->__('National Standard'),
                self::CODE_NATIONALEXPRESS  	=> Mage::helper('easylog')->__('National Express'),
                self::CODE_REGIOAT    			=> Mage::helper('easylog')->__('Regionalpaket AT'),
                self::CODE_EURO  				=> Mage::helper('easylog')->__('Europaket'),
                self::CODE_EUROPLUS				=> Mage::helper('easylog')->__('Europlus'),
                self::CODE_INTERNATIONAL 		=> Mage::helper('easylog')->__('International'),
        		self::CODE_INTERNATIONALEXPRESS => Mage::helper('easylog')->__('International Express'),
            ),
            'ids'=>array(
            	self::CODE_NATIONAL	=> array(
            		'verfahren'	=> '1',
            		'product'	=> '101',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_NATIONAL, $store_Id),
            	),
            	self::CODE_NATIONALEXPRESS	=> array(
            		'verfahren'	=> '72',
            		'product'	=> '7202',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_NATIONALEXPRESS, $store_Id),
            	),
            	self::CODE_REGIOAT	=> array(
            		'verfahren'	=> '1',
            		'product'	=> '666',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_REGIOAT, $store_Id),
            	),
            	self::CODE_EURO	=> array(
            		'verfahren'	=> '54',
            		'product'	=> '5401',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_EURO, $store_Id),
            	),
            	self::CODE_EUROPLUS	=> array(
            		'verfahren'	=> '69',
            		'product'	=> '6901',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_EUROPLUS, $store_Id),
            	),
            	self::CODE_INTERNATIONAL => array(
            		'verfahren'	=> '53',
            		'product'	=> '5301',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_INTERNATIONAL, $store_Id),
            	),
            	self::CODE_INTERNATIONALEXPRESS	=> array(
            		'verfahren'	=> '99',
            		'product'	=> '7202',
                    'teilnahme' => $this->getParticipationNumber(self::CODE_INTERNATIONALEXPRESS, $store_Id),
            	),
            )
        );
        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
    
    /**
     * returns the configured participation number based on code and per store
     * 
     * @param string $code
     * @param int $store_Id
     * @return string
     */
    public function getParticipationNumber($code, $store_Id = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PARTICIPATION_NUMBER_PREFIX. $code, $store_Id);
    }
    
	/**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		// skip if not enabled
		if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
			return false;
		}
		// init bulky
		$bulkyShipping = false;
		$bulkyDisableFreeShipping = false;
		$bulkyShippingPrice = 0.00;
		
		// init DefaultCountry
		$defaultCountry = Mage::getStoreConfig('general/country/default');
		
		// check if freeshipping amount is incl or excl tax.
		if (Mage::getStoreConfig('carriers/'.$this->_code.'/free_shipping_incl_tax')) {
			foreach ($request->getAllItems() as $item) {
				$request->setPackageValue($request->getPackageValue() + $item->getTaxAmount());
			}
		}
		// check if there is bulky shipping going on.
		foreach ($request->getAllItems() as $item) {
			if ($item->getProduct()->getIsBulky()) {
				$bulkyShipping = true;
				$bulkyShippingPrice = Mage::app()->getLocale()->getNumber(Mage::getStoreConfig('carriers/'.$this->_code.'/price_bulky'));
				$bulkyDisableFreeShipping = Mage::getStoreConfig('carriers/'.$this->_code.'/no_freeshipping_on_bulky');
				break;
			}	
		}
				
		// check if option no_shipping_on_bulky is active and a product with bulky shipping is in cart
		if ((Mage::getStoreConfig('carriers/'.$this->_code.'/no_shipping_on_bulky')) && ($bulkyShipping == true)) {
		    
		    return false;
		}
		
		$result = Mage::getModel('shipping/rate_result');
		$_selected_methods = explode(',',
			Mage::getStoreConfig('carriers/'.$this->_code.'/selected_methods')
		);
		$_euroLands = explode(',', $this->_euroLandcodes);
		if ($_selected_methods && is_array($_selected_methods)) {
			foreach ($_selected_methods as $_methodCode) {	
				$_methodName = $this->getCode('method', $_methodCode);
				
				if ((($_methodCode == self::CODE_NATIONAL)
				   ||($_methodCode == self::CODE_NATIONALEXPRESS))
				   //&&($request->getDestCountryId() != 'DE')) {
				   &&($request->getDestCountryId() != $defaultCountry)) {
					continue;
				}
				
				if (($_methodCode == self::CODE_REGIOAT)
				  &&($request->getDestCountryId() != 'AT')) {
					continue;
				}
				
				if ((($_methodCode == self::CODE_EURO)
				   ||($_methodCode == self::CODE_EUROPLUS))) {
					if ((!in_array($request->getDestCountryId(),$_euroLands))
					  ||(($request->getDestCountryId() == 'AT')
					  &&(in_array('regioat', $_selected_methods)))) {
						continue;
					}
				}
				
				if ($_methodCode == self::CODE_INTERNATIONAL) {
					if (((in_array($request->getDestCountryId(),$_euroLands))
					   	&&(in_array(self::CODE_EURO, $_selected_methods)))
					  ||((in_array($request->getDestCountryId(),$_euroLands))
					   	&&(in_array(self::CODE_EUROPLUS, $_selected_methods)))
					  ||(($request->getDestCountryId() == 'AT')
					   	&&(in_array(self::CODE_REGIOAT, $_selected_methods)))
					  ||($request->getDestCountryId() == 'DE')) {
						continue;
					}
				}
				
				if (($_methodCode == self::CODE_INTERNATIONALEXPRESS)
				   &&($request->getDestCountryId() == 'DE')) {
					continue;   	
				}
				
				$_priceTable = unserialize(Mage::getStoreConfig(
					self::XML_PATH_PRICETABLE_PREFIX.$_methodCode)
				);
				
				$_serviceTable = unserialize(Mage::getStoreConfig(
					self::XML_PATH_SERVICETABLE_PREFIX.$_methodCode)
				);
				
				if ($_priceTable && is_array($_priceTable)) {
					asort($_priceTable);
					foreach ($_priceTable as $key => $value) {
						$value['price'] = Mage::app()->getLocale()->getNumber($value['price']);
						$value['weight'] = Mage::app()->getLocale()->getNumber($value['weight']);
						if ($request->getPackageWeight() < $value['weight']) {
							$method = Mage::getModel('shipping/rate_result_method');
							$method->setCarrier($this->_code);
							$method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
							$method->setMethod($_methodCode);
							$method->setMethodTitle($_methodName);
							$method->setCost($value['price'] + $bulkyShippingPrice);
							$method->setPrice($value['price'] + $bulkyShippingPrice);
							// check if free shipping for national
							if (($_methodCode == self::CODE_NATIONAL)
							   &&(Mage::getStoreConfig('carriers/'.$this->_code.'/free_shipping_enable'))
							   &&(Mage::getStoreConfig('carriers/'.$this->_code.'/free_shipping_subtotal'))
							   &&($request->getPackageValue() >= Mage::app()->getLocale()->getNumber(Mage::getStoreConfig('carriers/'.$this->_code.'/free_shipping_subtotal')))
							   &&(!$bulkyDisableFreeShipping))
							{
								$method->setMethodTitle($_methodName . Mage::helper('easylog')->__(' (Versandkostenfrei)'));
								$method->setCost('0');
								$method->setPrice('0');
							}
							// check if global freeshiping is here (salesrule etc...)
							if (($request->getFreeShipping() === true)&&(!$bulkyDisableFreeShipping)) {
								$method->setMethodTitle($_methodName . Mage::helper('easylog')->__(' (Versandkostenfrei)'));
								$method->setCost('0');
								$method->setPrice('0');
							}
							$result->append($method);
							// check services and add them
							if ($_serviceTable) {
								foreach ($_serviceTable as $service_key => $_service_value) {
									$serviceModel = Mage::getModel('easylog/carrier_dhl_service')
										->load($_service_value['service_id']);
									$method = Mage::getModel('shipping/rate_result_method');
									$method->setCarrier($this->_code);
									$method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
									$method->setMethod(
										$_methodCode . 
										TechDivision_Easylog_Model_Pool::SERVICE_DELIMITER . 
										$_service_value['service_id']
									);
									$method->setMethodTitle(
										$_methodName . ' (' .
										Mage::helper('easylog')->__($serviceModel->getName()) .
										')'
									);
									$method->setCost($_service_value['service_price'] + $value['price'] + $bulkyShippingPrice);
									$method->setPrice($_service_value['service_price'] + $value['price'] + $bulkyShippingPrice);
									$result->append($method);
								}
							}
							break;
						}
					}
				}								
			}
		}	
		return $result;
	}
	
	public function getRealCarrierCode() {
		return $this->_code;
	}
	
	
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
        	$arr[$k] = $this->getCode('method', $k);
        }
        return $arr;
    }

}