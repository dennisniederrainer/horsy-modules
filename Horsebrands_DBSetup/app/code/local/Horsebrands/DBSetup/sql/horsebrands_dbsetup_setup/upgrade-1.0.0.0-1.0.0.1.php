<?php

// $storeviewId = Mage::getModel('core/website')->load('shop_de')->getId();
// if($storeviewId && $storeviewId > 0) {
//   $scope = 'websites';
// } else {
  $scope = 'default';
  $storeviewId = 0;
// }

$configSwitch = new Mage_Core_Model_Config();
$configSwitch->saveConfig('intraship/general/active', '1', $scope, $storeviewId);

$configSwitch->saveConfig('intraship/account/user', 'horsebrands2', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/account/signature', 'H0Rse2016!', $scope, $storeviewId);

$configSwitch->saveConfig('intraship/shipper/companyName1', 'Horsebrands GmbH', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/contactPerson', 'Jochen Haget', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/streetName', 'Robert-Bosch-Strasse', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/streetNumber', '21', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/zip', '48153', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/city', 'Münster', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/phone', '+4925167448555', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/email', 'service@horsebrands.de', $scope, $storeviewId);

$configSwitch->saveConfig('intraship/shipper/bank_data_accountOwner', 'Horsebrands GmbH', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/bank_data_bankName', 'GLS Bank', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/bank_data_iban', 'DE57430609674088585100', $scope, $storeviewId);
$configSwitch->saveConfig('intraship/shipper/bank_data_bic', 'GENODE1GLS', $scope, $storeviewId);
