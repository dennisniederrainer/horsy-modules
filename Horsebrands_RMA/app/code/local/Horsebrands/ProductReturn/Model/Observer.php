<?php

class Horsebrands_ProductReturn_Model_Observer {

	public function exportReturnsDaily() {
		// choose correct path
		$path = $_SERVER['DOCUMENT_ROOT'] . 'files/dashboard_exports/';
		$currencyHelper = Mage::helper('core');
		$taxHelper = Mage::helper('tax');

		if (!is_dir($path)) {
				mkdir($path);
		}

		// date today
		$today = date('Y-m-d');
		$returns = Mage::getModel('ProductReturn/Rma')
				->getCollection()
				// ->addFieldToFilter('rma_status', array('eq' => 'complete'))
				->addFieldToFilter('rma_reception_date', array('eq' => $today));

		// create filename
		$filename = $today.'_dashboard-export.csv';

		// create content prefilled with headings
		$header = array(
			'Bestellnummer',
			'Datum Retoure',
			'Datum Bestellung',
			'Kundennummer',
			'Kundenname',
			'Artikelnummer',
			'Kuerzel',
			'Artikelbeschreibung',
			'Kategorie',
			'Wert',
			'Zahlungsart',
			'Grund',
			'Datum Erstattung',
			'Erstattung Versandkosten',
			'Retouren Pauschale',
			'Gesamtbetrag Erstattung');

			$csv = fopen($path . $filename, 'w+');
			fputcsv($csv, $header, ",");

		foreach ($returns as $return) {
			$order = $return->getSalesOrder();
			$customer = $return->getCustomer();


			foreach ($return->getProducts() as $product) {
				if(!$product->getrp_action_processed()) {
					continue;
				}

				$sku = $product->getSku();

				$total = 0.00;
				if($product->getrp_object_id() && $product->getrp_object_type() == 'creditmemo') {
					$cm = Mage::getModel('sales/order_creditmemo')->load($product->getrp_object_id());
					$total = $cm->getGrandTotal();
					$fee = $cm->getAdjustmentNegative();
				}

				$line = array(
					$order->getIncrementId(),
					$return->getRmaCreatedAt(),
					$order->getCreatedAt(),
					$customer->getId(),
					$customer->getFirstname() . ' ' . $customer->getLastname(),
					$sku,
					substr($sku,0,3),
					$product->getName(),
					substr($sku,3,2),
					$currencyHelper->currency($taxHelper->getPrice($product, $product->getPrice()), true, false),
					$order->getPayment()->getMethod(),
					$product->getrp_reason(),
					$product->getrp_associated_object(),
					$currencyHelper->currency($order->getBaseShippingRefunded() * 1.19, true, false),
					$currencyHelper->currency($fee, true, false),
					$currencyHelper->currency($total, true, false)
				);

				fputcsv($csv, $line, ",");
			}
		}

		fclose($csv);

		$this->sendMail($path . $filename, $filename);
	}

	protected function sendMail($path, $filename) {
		$mail = new Zend_Mail();
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setBodyHtml('Taeglicher Dashboad Exports im Anhang.<br /><br />');
		$mail->setFrom('service@horsebrands.de', 'Horsebrands System');
		$mail->addTo('vera.brinck@horsebrands.de', 'Vera Brinck');
		$mail->setSubject('Dashboad Export');
		$file = $mail->createAttachment(file_get_contents($path));
		$file ->type        = 'text/csv';
		$file ->disposition = Zend_Mime::DISPOSITION_INLINE;
		$file ->encoding    = Zend_Mime::ENCODING_BASE64;
		$file ->filename    = $filename;
		$mail->send();
	}
}
