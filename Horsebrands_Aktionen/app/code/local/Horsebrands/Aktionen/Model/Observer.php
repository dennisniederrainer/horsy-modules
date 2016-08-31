<?php

class Horsebrands_Aktionen_Model_Observer {

    public function saveUpcomingFlashsales() {
        $upcomingflashsales = mage::getModel('PrivateSales/FlashSales')
                        ->getCollection()
                        ->addFieldToFilter('fs_end_date', array("gteq"=>now()))
                        ->addFieldToFilter('fs_enabled', array('eq' => '1'));

        $debug = '<h1>'.count($upcomingflashsales).' Flashsales wurden gespeichert</h1>';

        foreach ($upcomingflashsales as $fs) {            
            try {
                $fs->save();
                $debug .= $fs->getId().' :: '.$fs->getFsName().'<br/>';
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }

        echo $debug;
    }
}
