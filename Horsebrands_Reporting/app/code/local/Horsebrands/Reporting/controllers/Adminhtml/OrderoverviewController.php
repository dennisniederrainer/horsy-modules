
<?php
class Horsebrands_Reporting_Adminhtml_OrderoverviewController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
        $this->loadLayout();
        return $this;
    }
    
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('reporting/adminhtml_orderoverview')->toHtml()
        );
    }
    
    public function exportCsvAction()
    {
        $date = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()));
        $fileName = (string)$date.'_bestelluebersicht.csv';
        $grid = $this->getLayout()->createBlock('reporting/adminhtml_orderoverview_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    public function exportExcelXmlAction()
    {
        $fileName = date("Y-m-d", Mage::getModel('core/date')->timestamp(time())).'_bestelluebersicht.xml';
        $grid = $this->getLayout()->createBlock('reporting/adminhtml_orderoverview_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}