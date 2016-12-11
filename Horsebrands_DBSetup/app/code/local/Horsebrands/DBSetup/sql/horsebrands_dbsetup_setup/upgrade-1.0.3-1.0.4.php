<?php

$installer = $this;
$installer->startSetup();

$requestname = 'Horsebrands | Retoure beauftragt';
$receivedname = 'Horsebrands | Retoure eingetroffen';

/* ************************************ */
/* Start Setup Email Retoure beauftragt */
/* ************************************ */

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($requestname);

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
            Liebe/r {{var customer_firstname}},
          </p>
          <p>
            <strong>deine Retourenanfrage zur Bestellnummer {{var order_id}} ist bei uns eingegangen.</strong>
          </p>
          <p>
            Im Folgenden findest du einen Link, der dich zu einer DHL Seite führt. Hier musst du nur noch deine Adressdaten eingeben und erstellst dir somit dein Retourenlabel.<br/>
            Drucke dieses aus und klebe es deutlich sichtbar auf dein zu retournierendes Paket. Dieses kannst du deutschlandweit bei allen DHL Filialen, Packstationen und bei deinem Paketzusteller von DHL abgeben.
          </p>
          <p style="text-align: center; margin: 15px 0;">
            <a href="https://amsel.dpwn.net/abholportal/gw/lp/portal/horsebrands/customer/RpOrder.action?delivery=RetourenPortal01">
          		DHL Retourenlabel erstellen
          	</a>
          </p>
          <p>
            Bitte sende deine Retoure <b>innerhalb der kommenden 7 Tage</b> an uns zur&uuml;ck.<br/>
            Bitte achte darauf, dass die Ware vollst&auml;ndig ist und sich in einem ungebrauchten, unbesch&auml;digten und wiederverkaufsf&auml;higen Zustand befindet.<br/>
            Nach Erhalt der fristgerechten R&uuml;cksendung und Pr&uuml;fung der Ware, wird der Zahlungsbetrag entsprechend deiner gew&auml;hlten Zahlungsart zurückerstattet. Ein direkter Umtausch gegen einen anderen Artikel ist leider nicht m&ouml;glich.<br/>
            Sobald deine Ware bei uns im Lager eingetroffen ist und registriert wurde, erh&auml;ltst du eine Best&auml;tigungs-E-Mail.
          </p>
          <p>
            <strong>Hinweis zur Erstattung:</strong><br />
            Bei der Rücksendung fehlerfreier und korrekt gelieferter Ware übernehmen wir ab einem Rücksendewert der einzelnen Artikel <b>von 40 €</b> die kompletten Rücksendekosten. Bei einem Rücksendewert der einzelnen Artikel von <b>unter 40 €</b> ziehen wir eine Retouren Pauschale in Höhe von 4,95 Euro vom Erstattungswert ab.
          </p>
          <p>
            Detaillierte Informationen zu all deinen Bestellungen erh&auml;ltst Du rund um die Uhr in der Bestellansicht deines Kundenkontos auf unserer Homepage unter „Mein Konto“. Hier kannst Du jederzeit den Status deiner Bestellung oder die Erstattung deiner Rücksendung überprüfen.
          </p>
          <p>
            Wir freuen uns auf Deinen n&auml;chsten Einkauf.
          </p>
          <p>
            Liebe Gr&uuml;ße,<br />dein Horsebrands-Team
          </p>
        </td>
      </tr>
    </table>
  </td>
</tr>
{{template config_path="design/email/footer"}}
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($requestname);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject("Deine Retourenanfrage für Bestellung #{{var order_id}}");
// Template Type 2 = HTML EMail
$emailTemplate->setTemplateType(2);
$emailTemplate->save();

/* ************************************ */
/* Start Setup Email Retoure beauftragt */
/* ************************************ */

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($requestname);

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
            Liebe/r {{var customer_firstname}},
          </p>
          <p>
            <strong>deine Retourensendung der Bestellnummer {{var order_id}} ist bei uns eingetroffen.</strong>
          </p>
          <p>
            Nach eindringlicher Pr&uuml;fung der Ware, wird der Zahlungsbetrag <b>entsprechend deiner gew&auml;hlten Zahlungsart</b> zurückerstattet. Du erhältst eine Bestätigungsmail, wenn die	Erstattung erfolgt ist.
          </p>
          <p>
            Die Bearbeitung und Erstattung ist je nach Zahlungsart unterschiedlich und erfolgt innerhalb von 14 Tagen nach Erhalt der Retoure.
          </p>
          <p>
            Bei der R&uuml;cksendung fehlerfreier und korrekt gelieferter Ware &uuml;bernehmen wir ab einem R&uuml;cksendewert der einzelnen Artikel <b>von 40 €</b> die kompletten R&uuml;cksendekosten. Bei einem R&uuml;cksendewert der einzelnen Artikel von <b>unter 40 €</b> ziehen wir eine Retouren Pauschale in H&ouml;he von 4,90 Euro vom Erstattungswert ab.
          </p>
          <p>
            Detaillierte Informationen zu all deinen Bestellungen erh&auml;ltst Du rund um die Uhr in der Bestellansicht deines Kundenkontos auf unserer Homepage unter „Mein Konto“. Hier kannst Du jederzeit den Status deiner Bestellung oder die Erstattung deiner R&uuml;cksendung &uuml;berpr&uuml;fen.
          </p>
          <p>
            Wir freuen uns auf Deinen n&auml;chsten Einkauf.
          </p>
          <p>
            Liebe Gr&uuml;ße,<br />dein Horsebrands-Team
          </p>
        </td>
      </tr>
    </table>
  </td>
</tr>
{{template config_path="design/email/footer"}}
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($receivedname);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject("Wir haben deine Retoure erhalten - Bestellung #{{var order_id}}");
// Template Type 2 = HTML EMail
$emailTemplate->setTemplateType(2);
$emailTemplate->save();


/* ************ */
/* Config Setup */
/* ************ */
$configSwitch = new Mage_Core_Model_Config();
$scope = 'default';
$scopeId = 0;

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($requestname);
if($emailTemplate->getId()) {
  $configSwitch->saveConfig('productreturn/emails/template_rma_request', $emailTemplate->getId(), $scope, $scopeId);
}

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($receivedname);
if($emailTemplate->getId()) {
  $configSwitch->saveConfig('productreturn/emails/template_product_received', $emailTemplate->getId(), $scope, $scopeId);
}

$installer->endSetup();

?>
