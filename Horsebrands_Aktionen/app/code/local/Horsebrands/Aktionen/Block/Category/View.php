<?php

/**
 * Created by JetBrains PhpStorm.
 * User: Vitali Fehler
 * Date: 07.02.13
 */
class Horsebrands_Aktionen_Block_Category_View extends Mage_Catalog_Block_Category_View {

    protected $currentFlashSale;
    protected $currentFsEndDate;
    protected $weekDays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
    protected $monthes = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni', '7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');

    public function __construct() {
        // if (isset($_GET["previewMode"]) && $_GET["previewMode"] == "sBpRaCKkLBv2y5Wrdn66") {
        if (isset($_GET["previewMode"]) 
            && $_GET["previewMode"] == "be548bb6350a41284d652f877329b6ef7d9462f610681062833bca5a06cfd4e6") {
            Mage::getSingleton("customer/session")->setPreviewMode(true);
            Mage::log(Mage::getSingleton("customer/session")->getCustomer()->getEmail() . ': preview ACTIVATED.',
                null, 'previewmode.log');
        } elseif (Mage::getSingleTon("customer/session")->getPreviewMode() != true 
            || (isset($_GET["previewMode"]) && $_GET["previewMode"] == "disable")) {
            Mage::getSingleton("customer/session")->setPreviewMode(false);
        }

        parent::__construct();
    }

    public function getFlashSale() {
        $currentCategory = $this->getCurrentCategory();
        // set last viewed action - needed in checkout/cart
        Mage::getModel('customer/session')->setData('lastViewedAction', $currentCategory->getId());
        $flashSales = Mage::getModel('PrivateSales/FlashSales');
        $collection = $flashSales->getCollection();
        $collection->addFieldToFilter('fs_enabled', '1');
        $collection->addFieldToFilter('fs_category_id', $currentCategory->getId());
        $collection->setOrder('fs_start_date', 'ASC');
        $collection->load();

        // return print_r($collection, true);
        foreach ($collection as $flashSale) {
            if ($flashSale->getFsCategoryId() == $currentCategory->getId()) {
                // $endDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale->getFsEndDate()));
                $this->currentFsEndDate = Mage::getModel('core/date')->timestamp(strtotime($flashSale->getFsEndDate()));
                $flashSale->setEndDate($this->dateToFormattedString($this->currentFsEndDate));

                $this->currentFlashSale = $flashSale;
            }
        }

        return $this->currentFlashSale;
    }

    private function dateToFormattedString($date) {
        $dateString = "";
        $now = time();

        $dateArr = getdate($date);
        $nowArr = getdate($now);

        if ($this->isToday($date)) {
            $dateString .= "heute, " . $dateArr['hours'] . " Uhr";
        } elseif ($this->isTomorrow($date)) {
            $dateString .= "morgen, " . $dateArr['hours'] . " Uhr";
        } else {
            /* @dennis 2014-02-21
             *  Nach dem Tag (mday) einen (String)Punkt angefügt
             */
            $dateString .= $this->weekDays[$dateArr['wday']] . ", " . $dateArr['mday'] . ". " . $this->monthes[$dateArr['mon']] . ", " . $dateArr['hours'] . " Uhr";
        }
        return $dateString;
    }

    private function isToday($date) {
        return date('Ymd') == date('Ymd', $date);
    }

    private function isTomorrow($date) {
        $today = date('Ymd', $date);
        $tomorrow = date('Ymd', strtotime('tomorrow'));
        return $today == $tomorrow;
    }
}

?>