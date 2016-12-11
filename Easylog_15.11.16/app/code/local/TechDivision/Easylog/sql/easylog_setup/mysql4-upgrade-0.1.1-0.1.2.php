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

CREATE TABLE IF NOT EXISTS `{$installer->getTable('easylog/reimport')}` (
	`entity_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`SITEMS_ID` INT( 11 ) NULL DEFAULT NULL COMMENT 'Interne ID der Sendung',
	`SITEMS_IDENTCODE` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT 'Identcode Aufbau für Express International: Die NVE-Nummer des ersten Packstückes',
	`SITEMS_STORNO` INT( 11 ) NULL DEFAULT NULL COMMENT '-1, Sendung storniert 0, Sendung nicht storniert',
	`SITEMS_LABELTYP` INT( 11 ) NULL DEFAULT NULL COMMENT 'Labelstatus -1, Label noch nicht gedruckt 0, Label befindet sich im Spooler & wartet auf Ausdruck 1, Label wurde gedruckt',
	`ABS_CUSTOMERDECL` INT( 11 ) NULL DEFAULT NULL COMMENT 'Werden die Versandunterlagen des Absenders durch EasyLog ausgefüllt: 0 - Nein 1.- Ja',
	`AbsHausNr` VARCHAR( 16 ) NULL DEFAULT NULL COMMENT 'Hausnumer des Absenders',
	`AbsName1` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Name1 des Absenders',
	`AbsName2` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Name2 des Absenders',
	`ORT` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Ort des Absenders',
	`AbsPlz` VARCHAR( 10 ) NULL DEFAULT NULL COMMENT 'PLZ des Absenders',
	`AbsStrasse` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Strasse des Absenders',
	`SITEMS_DATE` VARCHAR( 16 ) NULL DEFAULT NULL COMMENT 'Versandatum der Sendung',
	`SITEMS_ABS_ID` INT( 11 ) NULL DEFAULT NULL COMMENT 'Interne Absender ID',
	`EmpKundenNr` VARCHAR( 10 ) NULL DEFAULT NULL COMMENT 'Referenznummer des Empfängers',
	`VerfahrenTyp` INT( 11 ) NULL DEFAULT NULL COMMENT 'Interne Verfahrens ID',
	`SITEMS_READY_TO_SEND` INT( 11 ) NULL DEFAULT NULL COMMENT 'Versandstatus der Sendung bei Abgangsscannung 1 - Sendung ist versandvertig Leer - Sendung nicht versandfertig',
	`CircleNumber` INT( 11 ) NULL DEFAULT NULL COMMENT 'Einlieferungsnummer',
	`EmpName1` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Name 1 des Empfängers',
	`EmpName2` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Name 2 des Empfängers',
	`SITEMS_EMPBEM` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Bemerkung des Empfängers',
	`SITEMS_LEITCODE` VARCHAR( 14 ) NULL DEFAULT NULL COMMENT 'Leitcode des Empfängers',
	`SITEMS_ABSBEM` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Bemerkung des Absenders',
	`SITEMS_ORDERNUMBER` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT 'Lieferscheinnummer des Sendung',
	`KSTS_BEZEICHNUNG` VARCHAR( 30 ) NULL DEFAULT NULL COMMENT 'Bezeichnung der Kostenstelle',
	`SITEMS_GEWICHT` DECIMAL( 12,3 ) NULL DEFAULT NULL COMMENT 'Gewicht der Sendung',
	`SITEMS_ZLEISTUNG` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Kürzel der verwendeten Extras laut DP AG (durch Komma getrennt) Bsp.: R,E,S',
	`SITEMS_NACHNAHME` DECIMAL( 12,2 ) NULL DEFAULT NULL COMMENT 'Nachnahmebetrag',
	`SITEMS_WERT` DECIMAL( 12,2 ) NULL DEFAULT NULL COMMENT 'Wertpaketbetrag',
	`SITEMS_ENTGELD` DECIMAL( 12,2 ) NULL DEFAULT NULL COMMENT 'Entgelt der Sendung',
	`EmpLandName` VARCHAR( 45 ) NULL DEFAULT NULL COMMENT 'Landname des Empfängers',
	`EmpPlz` VARCHAR( 10 ) NULL DEFAULT NULL COMMENT 'PLZ des Empfängers',
	`SITEMS_PRODUKTCODE` INT( 11 ) NULL DEFAULT NULL COMMENT 'Interne Produktcode',
	`EmpOrt` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Ort des Empfängers',
	`EmpStrasse` VARCHAR( 40 ) NULL DEFAULT NULL COMMENT 'Strasse des Empfängers',
	`SITEMS_PRODUKTNAME` VARCHAR( 30 ) NULL DEFAULT NULL COMMENT 'Produktname',
	`LeistungenText` TEXT NULL DEFAULT NULL COMMENT 'Extras & Entgelte ausgeschrieben Aufbau: Preis-Extra-<ID>-Labelandruck Bsp.:[5,90] Gewicht<0>N (CR/LF) [3,50] Rückschein<110>L (CR/LF) [3,50] Eigenhändig<112>L (CR/LF) [35,00] Sperrgut<113>L (CR/LF)',
	`NNWAEHRUNG` VARCHAR( 4 ) NULL DEFAULT NULL COMMENT 'Währung für Nachnahme',
	`SITEMS_KOLLICOUNTER` INT( 11 ) NULL DEFAULT NULL COMMENT 'ID für Mehrpaketsendungen (Kolli, Multitrack)',
	`SITEMS_ZUSATZBETRAGNACHNAHME` DECIMAL( 12,2 ) NULL DEFAULT NULL COMMENT 'Übermittlungsentgelt für Nachnahme',
	`SITEMS_GEWICHT_MC` BLOB NULL DEFAULT NULL COMMENT 'Gewicht bei Mehrpaketsendungen (außer Multitrack, Verfahren 01) Aufbau: TYP (CR/LF) Anzahl der Packstücke(CR/LF)
Gewicht (CR/LF)
TYP: Art der Gewichtseingabe 1 - Gesamtgewicht aller Sendungen 2 - Einzelgewicht aller Sendungen 3 - Individualgewicht 4 – National Express (ohne Mehrkolli) 5 – DHL EUROPLUS
Anzahl der Pakstücke X - Anzahl der beinhalteten Packstücke
Gewicht: Gewichte der einzelenen Sendungen, abhängig vom TYP der Gewichtseingabe Länge; Breite; Höhe; Volumen; - falls eingegeben (wenn Eingabe optional), sonst 0; 0; 0; 0;
TYP 1: ein Gewicht für alle Packstücke; 0; Länge; Breite; Höhe; Volumen; Identcode des Packstückes getrennt durch ‘;’ gefolgt von (CR/LF)
•	ein Gewicht für alle Packstücke nur für das erste Packstück, für die weiteren -1 •	Identcode des Packstückes (Verfahren-
und Produkt abhängig)
TYP 2: Gewichte mit (CR/LF) getrennt; 0; Länge; Breite; Höhe; Volumen; Identcode des Packstückes getrennt durch ‘;’ gefolgt von (CR/LF)
•	Identcode des Packstückes (Verfahren- und Produkt abhängig)',
	`SITEMS_VORAUSVERF` TEXT NULL DEFAULT NULL COMMENT 'Vorausverfügungen National
2 (CR/LF) Nicht nachsenden, wenn verzogen mit neuer Anschrift zurück
3 (CR/LF) Rücksendung ohne Lagerfrist
5 (CR/LF) Unzustellbarkeitsanzeige Sendung nochmals dem Empfänger angeboten
5 (CR/LF) Unzustellbarkeitsanzeige an den Absender zurückgesandt
9 (CR/LF) Teilauslieferung zulässig (nur bei Kolli-Sendungen ohne Nachnahme)
Vorausverfügungen International
1 (CR/LF) Preisgabe
2 (CR/LF) Rücksenden an den Absender, Sofort, auf dem preiswertesten Weg
2 (CR/LF) Rücksenden an den Absender, Sofort, auf dem Luftweg.
2 (CR/LF) Rücksenden an den Absender, nach X Tagen auf dem preiswertesten Weg
2 (CR/LF) Rücksenden an den Absender, nach X Tagen auf dem Luftweg
Vorausverfügungen DHL EUROPACK
1 (CR/LF) Drei Zustellversuche zugelassen
2 (CR/LF) Falls unzustellbar: Rücksenden an Absender
3 (CR/LF) Falls unzustellbar: Preisgabe
Vorausverfügungen DHL EUROPLUS
1 (CR/LF) Selbstabholung (hold on Depot) 2 (CR/LF) Drei Zustellversuche zugelassen
3 (CR/LF) Falls unzustellbar: Absender kontaktieren
4 (CR/LF) Falls unzustellbar: rücksenden an den Absender
5 (CR/LF) Falls unzustellbar: Rücksendung an alternative Returnadresse
6 (CR/LF) Falls unzustellbar: Preisgabe
Vorausverfügungen Express International
In dem Verfahren sind keine Vorausverfügungen möglich
ExpressIdent
Für Sendungen mit ExpressIdent-Service werden in SITEMS_VORAUSVERF die Informationen für das IdentBlatt aufbewahrt :
Identcode der Sendung(Separator) Express Kundennummer(Separator) Name(Separator) Vorname(Separator) Strasse(Separator)
PLZ(Separator) Ort(Separator) Geburtsdatum(Separator) Staatsangehörigkeit(Separator) Ist Vertragsvorlage ausgewählt?( Separator) {0- nein, 1-ja}
Anzahl der Vertragsseiten gesamt (Separator) {0 wenn Vertragsvorlage ist nicht ausgewählt } Anzahl der Unterschriften gesamt(Separator) {0 wenn Vertragsvorlage ist nicht ausgewählt } Anzahl Vertragsexemplare für Absender (Separator) {0 wenn Vertragsvorlage ist nicht ausgewählt }
Anzal Vertragsexemplare für Empfänger (Separator) {0 wenn Vertragsvorlage ist nicht ausgewählt } Ist „proaktive Prüfung“ ausgewählt (Separator) {0-nein, 1-ja}
nicht benutzt (Separator) Ist „Identitätsprüfung extra“ ausgewählt?(Separator) {0-nein, 1-ja} Ist Prüfungsmerkmal 1ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 1(Separator) Wert von Prüfungsmerkmal 1(Separator) Ist Prüfungsmerkmal 2ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 2(Separator) Wert von Prüfungsmerkmal 2(Separator) Ist Prüfungsmerkmal 3ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 3(Separator) Wert von Prüfungsmerkmal 3(Separator) Ist Prüfungsmerkmal 4ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 4(Separator) Wert von Prüfungsmerkmal 4(Separator) Ist Prüfungsmerkmal 5ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 5(Separator) Wert von Prüfungsmerkmal 5(Separator) Ist Prüfungsmerkmal 6ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 6(Separator) Wert von Prüfungsmerkmal 6(Separator) Ist Prüfungsmerkmal 7ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 7(Separator) Wert von Prüfungsmerkmal 7(Separator) Ist Prüfungsmerkmal 8ausgefüllt?(Separator) {0-nein, 1-ja} Name von Prüfungsmerkmal 8(Separator) Wert von Prüfungsmerkmal 8(Separator) Interne Identifizierung des ersten Prüfungsdokumentes (Separator) Interne Identifizierung des zweiten Prüfungsdokumentes (Separator) Name des ersten Prüfungsdokumentes(Separator) Name des zweiten Prüfungsdokumentes
als Separator dienen Zeichen #+(Code 02)',
	`EmpHausNr` VARCHAR( 16 ) NULL DEFAULT NULL COMMENT 'Hausnummer des Empfängers',
	`WertWaehrung` VARCHAR( 4 ) NULL DEFAULT NULL COMMENT 'Währung für Wertpaket',
	`SITEMS_STORNO_REASON` VARCHAR( 80 ) NULL DEFAULT NULL COMMENT 'Stornierungsgrund',
	`TVWERTWAEHRUNG` VARCHAR( 3 ) NULL DEFAULT NULL COMMENT 'Währung der Transportversicherung',
	`SITEMS_ZOLLZUSATZ` TEXT NULL DEFAULT NULL COMMENT 'International, DHL Europack, DHL Europlus
- Versandunterlagen: {FeldNr, EintragNr} Die Bezeichnung des Inhalts(Separator) {FeldNr, EintragNr}Ursprungsland der Waren(Separator) {FeldNr, EintragNr} Zolltarifnummer(Separator) {FeldNr, EintragNr}Nettogewicht(Separator) {FeldNr, EintragNr} (Zoll)wert(Separator)
die Beschreibung der Sendung (bei DHL Europack)
Express International: die Beschreibung der Sendung',
	`EmpLandCode` VARCHAR( 3 ) NULL DEFAULT NULL COMMENT 'Landcode für das Land des Empfängers',
	`NKREIS_TYP_CN` INT( 11 ) NULL DEFAULT NULL COMMENT 'Nummernkreistyp verwendet für Vergabe vom Identcode 0 - alle weiteren Produkte 1 – Ab Werk
3 - Retoure 5 – DHL Europack 6 - IDC bei DHL Europlus 7 - Europack National PLUS 9 - EAN 10 - AWB',
	`SITEMS_AWB` VARCHAR( 10 ) NULL DEFAULT NULL COMMENT 'Die AWB-Nummer der Sendung (Beschreibung : Airwaybill)',
	`SITEMS_TVWERT` DECIMAL( 12,2 ) NULL DEFAULT NULL COMMENT 'Der Wert der Transportversicherung',
	`SITEMS_E_EMAIL` VARCHAR( 50 ) NULL DEFAULT NULL COMMENT 'Emailadresse des Empfängers',
	`SITEMS_E_GEBDATUM` VARCHAR( 16 ) NULL DEFAULT NULL COMMENT 'Geburtsdatum',
	`SITEMS_E_AUSWEISNR` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT 'Ausweisnummer',
	`SITEMS_E_AUSWEISART` VARCHAR( 2 ) NULL DEFAULT NULL COMMENT 'Ausweisart: 01 = Personalausweis 02 = Reisepass',
	`SITEMS_E_AUSWEISBEH` VARCHAR( 30 ) NULL DEFAULT NULL COMMENT 'Ausstellende Behörde',
	`SITEMS_E_MINDESTALTER` INT( 11 ) NULL DEFAULT NULL COMMENT 'Mindestalter',
	`created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  	`updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  	`status` tinyint(1) unsigned NOT NULL default '1',
	INDEX ( `SITEMS_IDENTCODE` )
) ENGINE = MYISAM DEFAULT CHARSET=UTF8;

");

$installer->endSetup();