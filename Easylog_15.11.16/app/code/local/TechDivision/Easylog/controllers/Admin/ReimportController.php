<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category       TechDivision
 * @package        TechDivision_Easylog
 * @copyright      Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license        http://opensource.org/licenses/osl-3.0.php
 *                Open Software License (OSL 3.0)
 * @author         Johann Zelger <j.zelger@techdivision.com>
 */

require_once "Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php";

class TechDivision_Easylog_Admin_ReimportController
    extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
             ->_addBreadcrumb(Mage::helper('easylog')->__('Easylog'), Mage::helper('easylog')->__('Easylog'))
             ->_addBreadcrumb(Mage::helper('easylog')->__('Reimport'), Mage::helper('easylog')->__('Reimport'));
        $this->_setActiveMenu('easylog/reimport');

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('easylog/admin_reimport'))
             ->renderLayout();
    }

    public function trackAction()
    {
        if ($identCode = $this->getRequest()->getParam('identcode')) {
            echo '<iframe src="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=' . $identCode
                . '" width="550px" height="600px"></iframe>';
        }
    }

    public function uploadAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('easylog/admin_reimport_upload'))
             ->renderLayout();
    }

    public function uploadPostAction()
    {
        $result = array();
        $reimportModel = Mage::getModel('easylog/reimport');
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('txt'));
            $path = Mage::app()->getConfig()->getTempVarDir() . '/import/';
            $uploader->save($path);
            $resultCount = $reimportModel->import($path . $uploader->getUploadedFileName());
            if ($resultCount > 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('easylog')
                        ->__('%s Datensätze erfolgreich importiert', $resultCount)
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(
                    Mage::helper('easylog')
                        ->__('Keine neuen Datensätze.')
                );
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/upload');

            return;
        }
        $this->_redirect('*/*');
    }

    public function syncAction()
    {
        $successCount = 0;
        $errorCount = 0;
        $collection = Mage::getModel('easylog/reimport')
                          ->getCollection()
                          ->addFieldToFilter(
                              'status',
                              TechDivision_Easylog_Model_Reimport_Status::IMPORTED
                          );
        foreach ($collection as $reimportItem) {
            // check if we have a shipment registered and unregister it
            if (Mage::registry('current_shipment')) {
                Mage::unregister('current_shipment');
            }
            // try to load order by given ordernr. in easylog
            $orderModel = Mage::getModel('sales/order');
            $orderModel->loadByIncrementId(
                $reimportItem['SITEMS_ORDERNUMBER']
            );
            if ($orderModel->getIncrementId()) {
                if ($orderModel->canShip()) {
                    try {
                        // init data array
                        $data = array();
                        // build shipment items qty array
                        foreach ($orderModel->getAllItems() as $orderItem) {
                            $data['shipment']['items'][$orderItem->getId()]
                                = $orderItem->getQtyOrdered();
                        }
                        // send emails on sync
                        $data['shipment']['send_email'] = 1;
                        // build tracking items
                        $data['tracking'][1]['carrier_code'] = 'dhl';
                        $data['tracking'][1]['title'] = $reimportItem['LeistungenText'];
                        $data['tracking'][1]['number'] = $reimportItem['SITEMS_IDENTCODE'];
                        // set Params and Post vars in Request
                        $this->getRequest()->setParam('shipment', $data['shipment']);
                        $this->getRequest()->setPost('shipment', $data['shipment']);
                        $this->getRequest()->setPost('tracking', $data['tracking']);
                        $this->getRequest()->setParam('order_id', $orderModel->getEntityId());
                        // initialize ShipmentController to save Shipment
                        $shipmentController
                            = new Mage_Adminhtml_Sales_Order_ShipmentController(
                            $this->getRequest(),
                            $this->getResponse()
                        );
                        $shipmentController->saveAction();
                        $successCount++;
                        $reimportItem->setStatus(
                            TechDivision_Easylog_Model_Reimport_Status::SYNCED
                        );
                        $reimportItem->save();
                    } catch (Mage_Core_Exception $e) {
                        $this->_getSession()->addError($e->getMessage());
                    } catch (Exception $e) {
                        $this->_getSession()
                             ->addError($this->__('Die Bestellung mit der Nummer %s' .
                                 ' konte nicht abgeglichen werden.',
                                 $orderModel->getIncrementId()
                             )
                             );
                    }
                } else {
                    $reimportItem->setStatus(
                        TechDivision_Easylog_Model_Reimport_Status::SHIPPED_ALREADY
                    );
                    $reimportItem->save();
                    $errorCount++;
                }
            } else {
                $reimportItem->setStatus(
                    TechDivision_Easylog_Model_Reimport_Status::NOT_SYNCED
                );
                $reimportItem->save();
                $errorCount++;
            }
        }
        if ($successCount > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('easylog')
                    ->__('%s Bestellung(en) wurden erfolgreich abgeglichen.', $successCount)
            );
        }
        if ($errorCount > 0) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('easylog')
                    ->__('%s Bestellung(en) konnte(n) nicht abgeglichen werden.', $errorCount)
            );
        }
        if ($successCount == 0 && $errorCount == 0) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('easylog')
                    ->__('Keine Datensätze für einen Abgleich vorhanden.')
            );
        }
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/easylog/reimport');
    }
}
