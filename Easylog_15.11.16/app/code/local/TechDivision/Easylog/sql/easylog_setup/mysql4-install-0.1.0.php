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

CREATE TABLE IF NOT EXISTS `{$installer->getTable('easylog/pool')}` (
  `entity_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `POOL_REFNR` varchar(20) NOT NULL,
  `POOL_V_ABS_REFNR` varchar(20) NOT NULL DEFAULT '1',
  `POOL_ABS_BEMERKUNG` varchar(20) NOT NULL,
  `POOL_V_MAND_REFNR` varchar(20) NOT NULL DEFAULT '1',
  `POOL_V_VERFAHREN` varchar(2) NOT NULL DEFAULT '1',
  `POOL_V_TEILNAHME` varchar(2) NOT NULL DEFAULT '01',
  `POOL_V_KSTSTELLE_KUERZEL` varchar(10) NOT NULL,
  `POOL_V_PRODUKT_CN` varchar(11) NOT NULL DEFAULT '101',
  `POOL_V_EXTRASLST` varchar(255) NOT NULL,
  `POOL_V_NN_WAEHRUNG` varchar(3) NOT NULL DEFAULT 'EUR',
  `POOL_WERT_WAEHRUNG_ID` varchar(3) NOT NULL DEFAULT 'EUR',
  `POOL_EMPF_REFNR` varchar(20) NOT NULL,
  `POOL_EMPF_NAME1` varchar(40) NULL DEFAULT NULL,
  `POOL_EMPF_NAME2` varchar(40) NOT NULL,
  `POOL_EMPF_NAME3` varchar(40) NOT NULL,
  `POOL_EMPF_PLZ` varchar(10) NULL DEFAULT NULL,
  `POOL_EMPF_ORT` varchar(40) NULL DEFAULT NULL,
  `POOL_EMPF_ORTTEIL` varchar(40) NOT NULL,
  `POOL_EMPF_STRASSE` varchar(30) NULL DEFAULT NULL,
  `POOL_EMPF_HAUSNR` varchar(10) NOT NULL,
  `POOL_EMPF_TEL` varchar(30) NOT NULL,
  `POOL_EMPF_FAX` varchar(30) NOT NULL,
  `POOL_EMPF_EMAIL` varchar(50) NOT NULL,
  `POOL_EMPF_GEBDATUM` varchar(10) NOT NULL,
  `POOL_EMPF_AUSWEISNR` varchar(20) NOT NULL,
  `POOL_EMPF_AUSWEISART` varchar(2) NOT NULL,
  `POOL_EMPF_AUSWEISBEH` varchar(30) NOT NULL,
  `POOL_EMPF_MINDESTALTER` varchar(2) NOT NULL,
  `POOL_EMPF_APARTNER` varchar(30) NOT NULL,
  `POOL_EMPF_BEMERKUNG` varchar(30) NOT NULL,
  `POOL_EMPF_USTID` varchar(20) NOT NULL,
  `POOL_EMPF_LANDCODE` varchar(45) NOT NULL DEFAULT 'DE',
  `POOL_GEWICHT` text NOT NULL,
  `POOL_ANZAHL_SENDUNGEN` varchar(11) NOT NULL,
  `POOL_V_ZOLL_WARENART` varchar(11) NOT NULL,
  `POOL_V_ZOLL_WARENLISTE` text NOT NULL,
  `POOL_V_VORAUSVERF_NAT` varchar(11) NOT NULL,
  `POOL_V_VORAUSVERF_NAT_UNZUST` varchar(11) NOT NULL,
  `POOL_V_VORAUSVERF_INT` varchar(11) NOT NULL,
  `POOL_EPACK_VORAUS` varchar(11) NOT NULL,
  `POOL_V_VORAUSVERF_INT_TERMIN` varchar(11) NOT NULL,
  `POOL_V_VORAUSVERF_INT_TRANSP` varchar(11) NOT NULL,
  `POOL_ABWERK_ANZAHL` varchar(3) NOT NULL,
  `POOL_ABWERK_NR` varchar(10) NOT NULL,
  `POOL_ABWERK_DATE` varchar(10) NOT NULL,
  `POOL_ABWERK_BBNNR` varchar(10) NOT NULL,
  `POOL_SHIPMENT_ID` varchar(11) NOT NULL,
  `POOL_VORDATE` varchar(10) NOT NULL,
  `POOL_EXWORKS` varchar(10) NOT NULL,
  `POOL_CONTENTS` varchar(10) NOT NULL,
  `POOL_IDENTEXPRESS` text NOT NULL,
  `POOL_VERWENDUNGSZWECK` varchar(54) NOT NULL,
  `POOL_EXTRADATA` text NOT NULL,
  `POOL_SENDUNGSINHALTDHL` varchar(40) NOT NULL,
  `POOL_RECHNUNGSARTDHL` varchar(4) NOT NULL,
  `POOL_RECHNUNGSNUMMERDHL` varchar(30) NOT NULL,
  `POOL_RECHNUNGSDATUMDHL` varchar(10) NOT NULL,
  `POOL_UNTERZEICHNERDHL` varchar(30) NOT NULL,
  `POOL_ZOLLTARIFDHL` varchar(30) NOT NULL,
  `POOL_RECHNUNGSBEMDHL` text NOT NULL,
  `POOL_EXPORTARTDHL` varchar(11) NOT NULL,
  `POOL_EXPORTGRUNDDHL` text NOT NULL,
  `POOL_HANDELSBEDINGUNGENDHL` varchar(11) NOT NULL,
  `POOL_RECHNUNGSPOSITIONENDHL` text NOT NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8;

");

$installer->endSetup();