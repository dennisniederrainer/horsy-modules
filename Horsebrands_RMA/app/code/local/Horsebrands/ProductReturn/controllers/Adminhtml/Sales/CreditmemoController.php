<?php

require_once 'Mage/Adminhtml/controllers/Sales/CreditmemoController.php';

class Horsebrands_ProductReturn_Adminhtml_Sales_CreditmemoController extends Mage_Adminhtml_Sales_CreditmemoController {

	protected $_refundEmailTemplateId = 101;

	public function refundCreditmemoMassAction() {
		$creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');

		if (!empty($creditmemosIds)) {
			try {
				$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
	                ->load();

				foreach ($creditmemos as $creditmemo) {
					if($creditmemo->getState() == Horsebrands_ProductReturn_Model_Sales_Order_Creditmemo::STATE_OPEN) {
						// complete creditmemo
						$creditmemo->setRefundedState();
						// notify customer
						$creditmemo->sendEmail(true, '', $this->_refundEmailTemplateId);

						$comment = mage::helper('ProductReturn')->__('Betrag erstattet am %s', date("d.m.Y"));
        				$creditmemo->addComment($comment, false);

						$creditmemo->save();
					}
				}

				Mage::getSingleton('adminhtml/session')->addSuccess('Gutschriften wurden erfolgreich erstattet!');

			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/');
	}

	public function refundCreditmemoMassWithoutEmailAction() {
		$creditmemosIds = $this->getRequest()->getPost('creditmemo_ids');

		if (!empty($creditmemosIds)) {
			try {
				$creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('entity_id', array('in' => $creditmemosIds))
	                ->load();

				foreach ($creditmemos as $creditmemo) {
					if($creditmemo->getState() == Horsebrands_ProductReturn_Model_Sales_Order_Creditmemo::STATE_OPEN) {
						// complete creditmemo
						$creditmemo->setRefundedState();

						$comment = mage::helper('ProductReturn')->__('Betrag erstattet am %s. Kunde wurde NICHT benachrichtigt.', date("d.m.Y"));
        				$creditmemo->addComment($comment, false);

						$creditmemo->save();
					}
				}

				Mage::getSingleton('adminhtml/session')->addSuccess('Gutschriften wurden erfolgreich erstattet!');

			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/');
	}

	public function refundCreditmemoAction() {
		$creditmemoId = $this->getRequest()->getParam('creditmemo_id');

		if ($creditmemoId) {
			try {
				$creditmemo = mage::getModel('sales/order_creditmemo')->load($creditmemoId);

				if($creditmemo->getId()) {
					if($creditmemo->getState() == Horsebrands_ProductReturn_Model_Sales_Order_Creditmemo::STATE_OPEN) {
						// complete creditmemo
						$creditmemo->setRefundedState();
						// notify customer
						$creditmemo->sendEmail(true, '', $this->_refundEmailTemplateId);

						$comment = mage::helper('ProductReturn')->__('Betrag erstattet am %s', date("d.m.Y"));
        		$creditmemo->addComment($comment, false);

						$creditmemo->save();

						Mage::getSingleton('adminhtml/session')->addSuccess('Gutschrift wurde erfolgreich erstattet!');
					}
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/');
	}

}
