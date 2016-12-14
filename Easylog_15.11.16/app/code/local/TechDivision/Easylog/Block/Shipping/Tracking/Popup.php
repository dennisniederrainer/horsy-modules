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

class TechDivision_Easylog_Block_Shipping_Tracking_Popup
	extends Mage_Shipping_Block_Tracking_Popup
{
	/**
	 * All tracking ids in array after init.
	 * @var array
	 */
	protected $_tracking_ids = null;
	
	/**
	 * Tracking url for dhl germany.
	 * @var string
	 */
	protected $_tracking_url = 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=';
	
	/**
	 * Initialize all tracking_ids from shipments out of the order
	 * @return void
	 */
	protected function _initTrackingIds() {
		if ( sizeof( $this->getTrackingInfo() ) > 0 ) {
			$this->_tracking_ids = array();
			foreach( $this->getTrackingInfo() as $shipid => $_result ) {
				$_package = array_shift($_result);
				if (isset($_package['number'])) {
					$this->_tracking_ids[] = $_package['number'];
				} else {
					$this->_tracking_ids[] = $_package->getTracking();
				}
			}
		}
	}
	
	/**
	 * Return the complete tracking url with tracking_ids as parameters
	 * @return string
	 */
	public function getTrackingUrl() {
		return $this->_tracking_url . implode(',', $this->getTrackingIds());
	}
	
	/**
	 * Gets all tracking_ids
	 * @return array
	 */
	public function getTrackingIds() {
		if (!$this->_tracking_ids) {
			$this->_initTrackingIds();
		}
		return $this->_tracking_ids;
	}
	
	/**
	 * Returns the html content from the tracking url
	 * @return string
	 */
	public function getTrackingContents() {
		if ($this->getTrackingIds()) {
			return file_get_contents($this->getTrackingUrl());
		}
		return false;
	}
	
}