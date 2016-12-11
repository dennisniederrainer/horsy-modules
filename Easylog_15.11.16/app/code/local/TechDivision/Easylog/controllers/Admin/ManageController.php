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
class TechDivision_Easylog_Admin_ManageController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if ((!Mage::getStoreConfig(TechDivision_Easylog_Model_Pool::XML_PATH_ALLGEMEIN_MANDANTID))
            && (!Mage::getStoreConfig(TechDivision_Easylog_Model_Pool::XML_PATH_ALLGEMEIN_ABSENDERID))
            && (!Mage::getStoreConfig(TechDivision_Easylog_Model_Pool::XML_PATH_ALLGEMEIN_TEILNAHMEID))
        ) {
            $action = $this->getRequest()->getActionName();
            if (!preg_match('/^(index|clear)/i', $action)) {
                $this->_getSession()->addWarning(
                    Mage::helper('easylog')
                        ->__('Bitte geben die Mandant, Absender- und Teilnahme ID unter <a href="%s">Easylog Einstellungen</a> an.',
                            $this->getUrl('adminhtml/system_config/edit', array('section' => 'easylog'))
                        )
                );
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                $this->getResponse()->setRedirect(
                    $this->getUrl('*/*')
                );
            }
        }
    }

    public function _initAction()
    {
        $this->loadLayout()
             ->_addBreadcrumb(Mage::helper('easylog')->__('Easylog'), Mage::helper('easylog')->__('Easylog'));
        $this->_setActiveMenu('easylog/manage');

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
             ->_addBreadcrumb(Mage::helper('easylog')->__('Easylog Verwaltung'),
                 Mage::helper('easylog')->__('Easylog Verwaltung')
             )
             ->_addContent($this->getLayout()->createBlock('easylog/admin_manage'))
             ->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'easylog_' . time() . '.csv';
        $_pooling = Mage::getModel('easylog/pool')->getCollection();
        $content = utf8_decode($_pooling->getCsv());
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function clearAction()
    {
        if (Mage::getModel('easylog/pool')->clear()) {
            $this->_getSession()->addSuccess('Die Liste wurde erfolgreich geleert.');
        } else {
            $this->_getSession()->addError('Fehler beim Leeren');
        }
        $this->_redirect('*/*');
    }

    public function addAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $c_existed = 0;
        $c_added = 0;
        $_pool = Mage::getModel('easylog/pool')->loadByOrderId($orderId);
        // if there is no poolentry yet
        if (!$_pool->getId()) {
            // try to load order by given Id
            try {
                $_order = Mage::getModel('sales/order')->load($orderId);
                $_pool->saveByOrder($_order);
                $c_added++;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $c_existed++;
        }
        $_pool->unsetData();
        if ($c_added == 1) {
            $this->_getSession()->addSuccess('Es wurde eine Bestellungen hinzugefuegt.');
        } else {
            $this->_getSession()->addError('Es wurde keine Bestellung hinzugefuegt.');
        }

        if ($c_existed > 0) {
            $this->_getSession()->addNotice($c_existed . ' Bestellung(en) schon vorhanden.');
        }
        $this->_redirect('*/*');
    }

    public function addMassAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $c_existed = 0;
        $c_added = 0;
        foreach ($orderIds as $orderId) {
            $_pool = Mage::getModel('easylog/pool')->loadByOrderId($orderId);
            // if there is no poolentry yet
            if (!$_pool->getId()) {
                // load order by given Id
                try {
                    $_order = Mage::getModel('sales/order')->load($orderId);
                    $_pool->saveByOrder($_order);
                    $c_added++;
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $c_existed++;
            }
            $_pool->unsetData();
        }
        if ($c_added > 1) {
            $this->_getSession()->addSuccess('Es wurden ' . $c_added . ' Bestellungen hinzugefuegt.');
        } elseif ($c_added > 0) {
            $this->_getSession()->addSuccess('Es wurde eine Bestellungen hinzugefuegt.');
        } else {
            $this->_getSession()->addError('Es wurde keine Bestellung hinzugefuegt.');
        }
        if ($c_existed > 0) {
            $this->_getSession()->addNotice($c_existed . ' Bestellung(en) schon vorhanden.');
        }
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/easylog/manage');
    }
}
