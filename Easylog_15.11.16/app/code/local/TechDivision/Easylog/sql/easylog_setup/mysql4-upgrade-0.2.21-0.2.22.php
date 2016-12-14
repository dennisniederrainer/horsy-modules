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

CREATE TABLE IF NOT EXISTS `{$installer->getTable('easylog/carrier_dhl_service')}` (
  `entity_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_code` varchar(45) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dhl_id` int(4) NOT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

INSERT INTO `{$installer->getTable('easylog/carrier_dhl_service')}` VALUES
(NULL, 'nationalexpress', 'Sonderfrüh', 				'7207'),
(NULL, 'nationalexpress', 'vor 9 Uhr', 					'7208'),
(NULL, 'nationalexpress', 'vor 10 Uhr', 				'7209'),
(NULL, 'nationalexpress', 'vor 12 Uhr', 				'7210'),
(NULL, 'nationalexpress', 'Samstagszustellung', 		'7211'),
(NULL, 'nationalexpress', 'Sonn-/Feiertagszustellung', 	'7212');

");

$installer->endSetup();