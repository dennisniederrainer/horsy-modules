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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("

ALTER TABLE `{$installer->getTable('easylog/carrier_dhl_service')}`
	CHANGE `dhl_id` `dhl_id` VARCHAR( 64 ) NOT NULL;

INSERT INTO `{$installer->getTable('easylog/carrier_dhl_service')}` VALUES
	(NULL, 'nationalexpress', 'Samstagszustellung Sonderfrüh', 		'7211#7207'),
	(NULL, 'nationalexpress', 'Samstagszustellung vor 9 Uhr', 		'7211#7208'),
	(NULL, 'nationalexpress', 'Samstagszustellung vor 10 Uhr', 		'7211#7209'),
	(NULL, 'nationalexpress', 'Samstagszustellung vor 12 Uhr', 		'7211#7210');

");
	
$installer->endSetup();