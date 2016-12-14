<?php

$installer = $this;
$installer->startSetup();

$shippingname = 'Horsebrands | Versandbenachrichtigung';
$creditmemoname = 'Horsebrands | Gutschrift';
$order = '$order';
$shipment = '$shipment';
$creditmemo = '$creditmemo';

/* ************************************** */
/* Start Setup Email SHIPPING INFO */
/* ************************************** */

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($shippingname);

if($emailTemplate->getId())
  $emailTemplate->delete();

$content = <<<EOF
{{template config_path="design/email/header"}}
{{inlinecss file="hb-email-inline.css"}}

  <!-- Anschreiben -->
  <tr>
    <td class="one-column hello">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <p class="title">
              Liebe/r {{var order.getCustomerFirstname()}},
            </p>
            <p>
              <strong>deine Bestellung ist auf dem Weg zu Dir!</strong>
            </p>
            <p>
              Die unten stehende Bestellung wurde heute an dich versendet. Klicke auf den Trackinglink um den Lieferstatus Deiner Bestellung zu verfolgen. Solltest Du darüber hinaus Fragen haben, sende uns gerne eine E-Mail an <a href="mailto:service@horsebrands.de" >service@horsebrands.de</a>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- Addresses -->
  <tr>
    <td class="two-column">
      <!--[if (gte mso 9)|(IE)]>
      <table width="100%">
      <tr>
      <td width="50%" valign="top">
      <![endif]-->
      <div class="column">
        <table width="100%">
          <tr>
            <td class="inner">
              <table class="contents">
                <tr>
                  <td>
                    <h2 class="order-title thin-border">Versandadresse</h2>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{var order.getShippingAddress().format('html')}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <!--[if (gte mso 9)|(IE)]>
      </td><td width="50%" valign="top">
      <![endif]-->
      <div class="column">
        <table width="100%">
          <tr>
            <td class="inner">
              <table class="contents">
                <tr>
                  <td>
                    <h2 class="order-title thin-border">Sendungsverfolgung</h2>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{block type='core/template' area='frontend' template='email/order/shipment/track.phtml' shipment=$shipment order=$order}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <!--[if (gte mso 9)|(IE)]>
          </td>
        </tr>
      </table>
      <![endif]-->
    </td>
  </tr>
  <!-- Delivery and Payment -->
  <tr>
  <td class="two-column">
    <!--[if (gte mso 9)|(IE)]>
    <table width="100%">
    <tr>
    <td width="50%" valign="top">
    <![endif]-->
    <div class="column">
      <table width="100%">
        <tr>
          <td class="inner">
            <table class="contents">
              <tr>
                <td>
                  <h2 class="order-title thin-border">Versandart</h2>
                </td>
              </tr>
        <tr>
          <td>
            {{var order.shipping_description}}
          </td>
        </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
    <!--[if (gte mso 9)|(IE)]>
    </td><td width="50%" valign="top">
    <![endif]-->
    <div class="column">
      <table width="100%">
        <tr>
          <td class="inner">
            <table class="contents">
              <tr>
                <td>
                  <h2 class="order-title thin-border">Zahlungsart</h2>
                </td>
              </tr>
              <tr>
                <td>
                  {{var payment_html}}
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
    <!--[if (gte mso 9)|(IE)]>
    </td>
    </tr>
  </table>
  <![endif]-->
  </td>
  </tr>
  {{if comment}}
  <tr>
    <td class="one-column">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <h2 class="order-title thin-border">Kommentar</h2>
            <p>
              {{var comment}}
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  {{/if}}

  {{layout handle="sales_email_order_shipment_items" shipment=$shipment order=$order}}
  {{template config_path="design/email/footer"}}
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($shippingname);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject("Deine Lieferung ist auf dem Weg!");
// Template Type 2 = HTML EMail
$emailTemplate->setTemplateType(2);
$emailTemplate->save();

/* **************************** */
/* Start Setup Email CREDITMEMO */
/* **************************** */

$emailTemplate = Mage::getModel('core/email_template')->loadByCode($creditmemoname);

if($emailTemplate->getId())
  $emailTemplate->delete();

$content = <<<EOF
{{template config_path="design/email/header"}}
{{inlinecss file="hb-email-inline.css"}}

  <!-- Anschreiben -->
  <tr>
    <td class="one-column hello">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <p class="title">
              Liebe/r {{var order.getCustomerFirstname()}},
            </p>
            <p>
            Deine Gutschrift f&uuml;r die Bestellnummer {{var order.incremend_id}} wird von uns bearbeitet. Die Gutschrift sollte je nach Art der Zahlung in spätestens 10 Tagen auf Deinem Konto eingehen. Solltest du noch Fragen haben, sende uns gerne eine E-Mail an <a href="mailto:service@horsebrands.de">service@horsebrands.de</a>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- Creditmemo Start -->
  <tr>
    <td class="one-column">
      <table width="100%">
        <tr>
          <td class="inner contents">
            <h2 class="order-title">
              Deine Gutschrift in der &Uuml;bersicht
            </h2>
            <dl class="order-details-data">
              <dt>
                Datum:
              </dt>
              <dd>
                {{var creditmemo.getCreatedAtFormated('short')}}
              </dd>
              <dt>
                Gutschriftsnummer:
              </dt>
              <dd>
                <span class="no-link">{{var creditmemo.increment_id}}</span>
              </dd>
            </dl>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <!-- Addresses -->
  <tr>
    <td class="two-column">
      <!--[if (gte mso 9)|(IE)]>
      <table width="100%">
      <tr>
      <td width="50%" valign="top">
      <![endif]-->
      <div class="column">
        <table width="100%">
          <tr>
            <td class="inner">
              <table class="contents">
                <tr>
                  <td>
                    <h2 class="order-title thin-border">Rechnungsadresse</h2>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{var order.getBillingAddress().format('html')}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <!--[if (gte mso 9)|(IE)]>
      </td><td width="50%" valign="top">
      <![endif]-->
      <div class="column">
        <table width="100%">
          <tr>
            <td class="inner">
              <table class="contents">
                <tr>
                  <td>
                    <h2 class="order-title thin-border">Zahlungsart</h2>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{var payment_html}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <!--[if (gte mso 9)|(IE)]>
      </td>
      </tr>
    </table>
    <![endif]-->
    </td>
  </tr>

  {{layout handle="sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}
  {{template config_path="design/email/footer"}}
EOF;

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate->setTemplateCode($creditmemoname);
$emailTemplate->setTemplateText($content);
$emailTemplate->setTemplateSubject('Deine Gutschrift für Bestellung #{{var order.increment_id}}');
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
$emailTemplate = Mage::getModel('core/email_template')->loadByCode($shippingname);
if($emailTemplate->getId()) {
  $configSwitch->saveConfig('sales_email/shipment/template', $emailTemplate->getId(), $scope, $scopeId);
  $configSwitch->saveConfig('sales_email/shipment/guest_template', $emailTemplate->getId(), $scope, $scopeId);
}

// Set creditmemo
$emailTemplate = Mage::getModel('core/email_template')->loadByCode($creditmemoname);
if($emailTemplate->getId()) {
  $configSwitch->saveConfig('sales_email/creditmemo/template', $emailTemplate->getId(), $scope, $scopeId);
  $configSwitch->saveConfig('sales_email/creditmemo/guest_template', $emailTemplate->getId(), $scope, $scopeId);
}

$installer->endSetup();

?>
