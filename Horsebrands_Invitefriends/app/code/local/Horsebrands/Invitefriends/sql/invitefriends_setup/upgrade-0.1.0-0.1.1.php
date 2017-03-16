<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$templatename = 'Horsebrands | Invite Friends';
$emailTemplate = Mage::getModel('core/email_template')->loadByCode($templatename);

if($emailTemplate->getId())
  $emailTemplate->delete();

$content = <<<EOF
{{template config_path="design/email/header"}}
{{inlinecss file="hb-email-inline.css"}}
  <tr>
    <td class="one-column hello">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <p class="title">
              Persönliche Nachricht von {{var customer_firstname}} {{var customer_lastname}}:
            </p>
            <p>
              "Hallo, ich möchte dich zu Horsebrands einladen, einem exklusiven Home & Living Shopping Club für alle Pferdebegeisterten.<br />
              Hier findest du alles für dich und dein Pferd – von der Reithose bis zur Pferdedeacke! Täglich starten neue Verkaufsaktionen mit Reitmode und Equipment bis zu 75% reduziert!<br />
              Viele Grüße"
            </p>
            <p>
              <strong>Wenn du meine Einladung annehmen möchtest, dann klicke bitte hier:</strong>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="one-column">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <a class="button full-width gold" href="{{var invite_link}}">
              <span>Einladung annehmen</span>
            </a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="one-column">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <p>&nbsp;</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="one-column">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <p style="font-size: 10px;">
            Diese Einladung hat dir {{var customer_firstname}} {{var customer_lastname}} geschickt.<br />
            Wenn du auch Mitglied bei Horsebrands werden möchtest, kannst du dich <a href="{{var invite_link}}">hier</a> registrieren.<br />
            Falls du keine Benachrichtigungen dieser Art mehr erhalten möchtest, wende dich bitte an unseren Kundenservice (Hotline unter 0251-67448-555 oder per Email an service@horsebrands.de).<br />
            Der Gutschein ist für 30 Tage ab Registrierung gültig und an einen Mindestbestellwert von 70 € gebunden.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
{{template config_path="design/email/footer"}}
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($templatename);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject("Einladung & 10 € Gutschein für dich!");
// Template Type 2 = HTML EMail
$emailTemplate->setTemplateType(2);
$emailTemplate->save();

/* ************ */
/* Config Setup */
/* ************ */

$configSwitch = new Mage_Core_Model_Config();
$scope = 'default';
$scopeId = 0;

if($emailTemplate->getId()) {
  $configSwitch->saveConfig('customer/invitefriends/invitefriends_invite_email', $emailTemplate->getId(), $scope, $scopeId);
}

$installer->endSetup();

?>
