<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitali Fehler
 * Date: 02.04.13
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */

class Horsebrands_Aktionen_Helper_Date extends Mage_Core_Helper_Abstract
{
    protected $weekDays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
    protected $monthes = array('1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni', '7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember');

    public function dateToFormattedString($date, $showWeekDay = true)
    {
        $dateString = "";
        $now = time();

        $dateArr = getdate($date);
        $nowArr = getdate($now);

        if($this->isToday($date))
        {
            $dateString .= "heute, ".$dateArr['hours']." Uhr";
        }
        elseif($this->isTomorrow($date))
        {
            $dateString .= "morgen, ".$dateArr['hours']." Uhr";
        }
        else
        {
            if($showWeekDay)
            {
                $dateString .= $this->weekDays[$dateArr['wday']].", ";
            }
            /* @dennis 2014-02-21
             *  Nach dem Tag (mday) einen (String)Punkt angefügt
             */
            $dateString .= $dateArr['mday'].". ".$this->monthes[$dateArr['mon']].", ".$dateArr['hours']." Uhr";
        }
        return $dateString;
    }

    private function isToday($date)
    {
        return date('Ymd') == date('Ymd', $date);
    }

    private function isTomorrow($date)
    {
        $today = date('Ymd', $date);
        $tomorrow = date('Ymd', strtotime('tomorrow'));
        return $today == $tomorrow;
    }
}