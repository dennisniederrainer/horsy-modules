<?php

class Horsebrands_Reporting_Adminhtml_ChecklistController extends Mage_Adminhtml_Controller_Action {

    /*protected function _initAction() {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }*/
    public function indexAction()
    {
        $this->_title($this->__('Report'))->_title($this->__('Prüfliste Bestellabwicklung'));
        $this->loadLayout();
        $this->_setActiveMenu('reports');
        //$this->_addContent($this->getLayout()->createBlock('reporting/adminhtml_checklist'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        //$this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('reporting/adminhtml_checklist')->toHtml()
        );
    }

    public function exportCsvAction()
    {
        $date = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()));
        $fileName = (string)$date.'_pruefliste-payment.csv';
        $grid = $this->getLayout()->createBlock('reporting/adminhtml_checklist_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    public function exportExcelXmlAction()
    {
        $fileName = date("Y-m-d", Mage::getModel('core/date')->timestamp(time())).'_pruefliste-payment.xml';
        $grid = $this->getLayout()->createBlock('reporting/adminhtml_checklist_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    protected function _isAllowed() {
        return true;
    }
}
