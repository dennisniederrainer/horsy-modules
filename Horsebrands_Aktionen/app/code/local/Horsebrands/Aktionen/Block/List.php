<?php

class Horsebrands_Aktionen_Block_List extends Mage_Core_Block_Template {

    protected $currentFlashsales = null;
    protected $upcomingFlashsales = null;
    protected $weekdays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

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

    public function getCurrentFlashsales() {
        if(Mage::getSingleton("customer/session")->getPreviewMode() == true) {
            $this->currentFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->setOrder('fs_start_date', 'DESC');
            return $this->currentFlashsales;
        }

        if(!$this->currentFlashsales) {
            $now = Mage::getModel('core/date')->date('Y-m-d H:i:s', time());
            $this->currentFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->addFieldToFilter('fs_end_date', array('gteq' => $now))
                                            ->addFieldToFilter('fs_start_date', array('lteq' => $now))
                                            ->setOrder('fs_start_date', 'DESC');
        }
        return $this->currentFlashsales;
    }

    public function getUpcomingFlashsales() {
        if(!$this->upcomingFlashsales) {
            $now = Mage::getModel('core/date')->date('Y-m-d H:i:s', time());
            $this->upcomingFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->addFieldToFilter('fs_start_date', array('gteq' => $now))
                                            ->setOrder('fs_start_date', 'ASC');    
        }
        return $this->upcomingFlashsales;
    }

    protected function getCampaignEndsString($enddate) {
        $html = "";

        if($enddate) {
            $dateInTime = strtotime($enddate);
            $html = "Aktion endet: ";

            // get day of week string from array
            $html .= $this->weekdays[date("w", $dateInTime)] . ", ";
            $html .= date("d.m. H", $dateInTime) . " Uhr";
        }

        return $html;
    }

    protected function getCampaignStartsString($date) {
        $html = "";

        if($date) {
            $dateInTime = strtotime($date);
            // $html = "Ab ";

            // get day of week string from array
            $html .= $this->weekdays[date("w", $dateInTime)] . ", ";
            $html .= date("d.m. H", $dateInTime) . " Uhr";
        }

        return $html;
    }

    protected function getCampaignDateString($date) { 
        $html = "";

        if($date) {
            $dateInTime = strtotime($date);

            $html .= $this->weekdays[date("w", $dateInTime)] . ", ";
            $html .= date("d.m. H", $dateInTime) . " Uhr";
        }

        return $html;
    }

    public function getTopCampaignHtml() {
        if(count($this->getCurrentFlashsales()) > 0) {
            $topCampaign = $this->getCurrentFlashsales()->getFirstItem();
            return $this->getCampaignHtml($topCampaign);
        }
        return "";
    }
    
    public function getCampaignHtml($campaign, $isUpcomingCampaign = false) {
        $html = "";
        if($campaign) {
            $category = Mage::getModel('catalog/category')->load($campaign->getFsCategoryId());
            $html .= '<div class="row campaign-block"><div class="row">';
            
            //Wenn es eine upcoming campaign ist, lege banner über den container
            if($isUpcomingCampaign) {
                $html .= '<a href="/aktionen/kommende"><div class="upcoming-banner"></div></a>';
            }

            $html .= '<div class="columns small-12 campaign-main-picture">';

            $html .= '<a href="'.($isUpcomingCampaign ? '#' : $category->getUrl()).'">';
            if($category->getImageUrl()) {
                $html .= '<img title="" src="'.$category->getImageUrl().'" alt="" class="campaign-picture"/>';
            }
            $html .= '</a></div></div>';

            $html .= '<a href="'.($isUpcomingCampaign ? '#' : $category->getUrl()).'">';
            $html .= '<div class="row campaign-titlepanel">';
            $html .= '<div class="columns small-12 large-7"><p class="campaign-name">'.$campaign->getFsName();
            if(trim($campaign->getFsDescription()) <> "") {
                    $html .= " - <span class='campaign-name description'>" . $campaign->getFsDescription() . "</span>";
            }
            $html .= '</p></div>';
            $html .= '<div class="columns small-12 large-5 campaign-ends"><p>'
                // .'<img src="'.$this->getSkinUrl().'/images/endingcampaign_clock_big.png" style="margin-right:10px"/>'
                .($isUpcomingCampaign ? "Ab ".$this->getCampaignStartsString($campaign->getFsStartDate()) 
                    : $this->getCampaignEndsString($campaign->getFsEndDate())).'</p></div>';
           

            // $html .= '<div class="columns small-12"><p class="campaign-name description">';
            //     if(trim($campaign->getFsDescription()) <> "") {
            //         $html .= $campaign->getFsDescription();
            //     }else {
            //         $html .= '&nbsp;';
            //     }
            // $html .= '</p></div>';

            $html .= '</div></a></div>';
        }
        return $html;
    }

    public function getUpcomingCampaignArticleHtml($campaign) {
        $html = "";

        if($campaign) {
            $category = Mage::getModel('catalog/category')->load($campaign->getFsCategoryId());
            
            $html .= '<article title="'.$campaign->getFsName().'">';
                $html .= '<div class="image-content">';
                    $html .= '<div class="campaign-main-picture">';
                    $html .= '<img title="" src="'.$category->getImageUrl().'" alt="" class="campaign-picture"/>';
                    $html .= '<div class="row campaign-titlepanel">';
                    
                    $html .= '<div class="columns small-12 large-6 medium-7"><p class="campaign-name">'.$campaign->getFsName();
                        if(trim($campaign->getFsDescription()) <> "") {
                            $html .= ' - '.$campaign->getFsDescription();
                        }
                    $html .= '</p></div>';
                    
                    $html .= '<div class="columns small-12 medium-4 large-6 campaign-ends"><p>'
                        .$this->getCampaignStartsString($campaign->getFsStartDate()).'</p></div>';
                    $html .= '</div>';        
                $html .= '</div>';
                $html .= '<div class="campaign-info">';
                    $html .= '<p>';
                    $html .= $category->getDescription();
                    $html .= '</p>';
                $html .= '</div>';
            $html .= '</article>';
        }
        return $html;
    }

    public function getUpcomingCampaignHtml($campaign) {
        $html = "";
        if($campaign) {
            $category = Mage::getModel('catalog/category')->load($campaign->getFsCategoryId());
            $html .= '<div class="row">';
            $html .= '<div class="columns small-12 campaign-main-picture">';
            $html .= '<a href="#">';
            if($category->getImageUrl()) {
                $html .= '<img title="" src="'.$category->getImageUrl().'" alt="" class="campaign-picture"/>';
            }
            $html .= '</a></div></div>';

            $html .= '<div class="row campaign-titlepanel">';
            // $html .= '<div class="columns small-12 large-6"><p class="campaign-name">'.$campaign->getFsName();
            // if(trim($campaign->getFsDescription()) <> "") {
            //         $html .= " - <span class='campaign-name description'>" . $campaign->getFsDescription() . "</span>";
            // }
            // $html .= '</p></div>';
            $html .= '<div class="columns small-12 campaign-starts"><p>Aktion startet: '
                // .'<img src="'.$this->getSkinUrl().'/images/endingcampaign_clock_big.png" style="margin-right:10px"/>'
                .$this->getCampaignStartsString($campaign->getFsStartDate()).'</p></div>';
            $html .= '</div>';
        }
        return $html;
    }
}