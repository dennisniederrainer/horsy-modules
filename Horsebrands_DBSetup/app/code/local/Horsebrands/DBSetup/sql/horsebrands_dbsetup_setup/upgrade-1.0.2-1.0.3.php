<?php

$installer = $this;
$installer->startSetup();

$crmname = 'Horsebrands | Kundenservice Antwort Simple';

/* ************************************** */
/* Start Setup Email SHIPPING INFO */
/* ************************************** */

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($crmname);

if($emailTemplate->getId())
  $emailTemplate->delete();

$content = <<<EOF
<!--@subject {{var ct_subject}} {{var hashtag}} @-->
<p style="display: none;"><FONT COLOR="#FFFFFF">{{var responsetag}}</FONT></p>
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>
  <body bgcolor="#FFFFFF">
<!-- style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 98% !important; height: 100%; margin: 0; padding: 0;" -->
      <!-- BODY -->
           {{var latest_message}}
           <br/>
           <p>Hier siehst du den Nachrichtenverlauf deiner Anfrage:</p>
           <p>{{var messages}}</p>
     </body>
     <!-- /BODY -->
<p><FONT COLOR="#FFFFFF">{{var hashtag}}</FONT></p>
</html>
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($crmname);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject("Deine Kundenservice-Anfrage: {{var ct_subject}} {{var hashtag}}");
// Template Type 2 = HTML EMail
$emailTemplate->setTemplateType(2);
$emailTemplate->save();


/* ************ */
/* Config Setup */
/* ************ */
$configSwitch = new Mage_Core_Model_Config();
$scope = 'default';
$scopeId = 0;

// Set Shipment
$emailTemplate = Mage::getModel('core/email_template')->loadByCode($crmname);
if($emailTemplate->getId()) {
  $configSwitch->saveConfig('crmticket/notification/template', $emailTemplate->getId(), $scope, $scopeId);
}

$installer->endSetup();

?>
