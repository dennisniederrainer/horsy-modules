<?php
class Horsebrands_Aktionen_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $weekdays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

    public function hasCategoriesWithoutFlashsaleReferenceByProduct($product) {
        //category-ID-Array mit einigartigen Werten (keine doppelten Kategorien...)
        $categoryIds = array_unique($this->getAssignedCategoriesForProduct($product));

        if(count($categoryIds) > 0) {
            $fsCollection = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                ->addFieldToFilter('fs_category_id', array('in' => $categoryIds));
            $fsCollection->getSelect()->group('fs_category_id');

            //Ist categoryIds > fsCollection, gibt es Kategorien ohne FS-Referenz
            return (count($categoryIds) > count($fsCollection));
        }

        return false;
    }
    public function hasCategoriesWithoutFlashsaleReferenceByProductId($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        return $this->hasCategoriesWithoutFlashsaleReferenceByProduct($product);
    }

    public function hasProductCurrentFlashsale($product) {
      $categoryIds = array_unique($this->getAssignedCategoriesForProduct($product));
      if(count($categoryIds) > 0) {
          $fsCollection = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                              ->addFieldToFilter('fs_category_id', array('in' => $categoryIds));

          foreach ($fsCollection as $fs) {
            if($fs->getfs_enabled() &&
                  strtotime($fs->getFsEndDate()) >= Mage::getModel('core/date')->timestamp(time())) {
              return true;
            }
          }
      }

      return false;
    }

    /**
     * Methode gibt ein Array mit den Kategorien-Ids des Produkts und der Parent-Produkte zurück
     */
    public function getAssignedCategoriesForProduct($product) {
        $categoryIds = array();
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                  ->getParentIdsByChild($product->getId());

        if(count($parentIds) > 0) {
            foreach ($parentIds as $parentId) {
                $parent = Mage::getModel('catalog/product')->load($parentId);
                $categoryIds = array_merge($categoryIds, $this->getAssignedCategoriesForProduct($parent));
            }
        }

        return array_merge($categoryIds, $product->getCategoryIds());
    }

    public function getRecommendationCampaignHtml($campaign) {
        $html = "";

        if($campaign) {
            $category = Mage::getModel('catalog/category')->load($campaign->getFsCategoryId());
            $html .= '<div class="row campaign-block"><div class="row">';

            $html .= '<div class="columns small-12 campaign-main-picture">';
            $html .= '<a href="'.$category->getUrl().'">';
            $html .= '<img title="" src="'.$category->getImageUrl().'" alt="" class="campaign-picture"/>';
            $html .= '</a></div></div>';

            $html .= '<a href="'.$category->getUrl().'">';
            $html .= '<div class="row campaign-titlepanel">';
            $html .= '<div class="columns small-12 large-6 medium-8"><p class="campaign-name">'.$campaign->getFsName().'</p></div>';

            $html .= '<div class="columns small-12 large-6 medium-4 campaign-ends"><p>'
                // .'<img src="'.$this->getSkinUrl().'/images/endingcampaign_clock_big.png" style="margin-right:10px"/>'
                .$this->getCampaignEndsString($campaign->getFsEndDate()).'</p></div>';

            $html .= '</div></a></div>';
        }

        // return $campaign->getId();
        return $html;
    }

    protected function getCampaignEndsString($enddate) {
        $html = "";

        if($enddate) {
            $dateInTime = strtotime($enddate);
            $html .= "Aktion endet: ";

            // get day of week string from array
            $html .= $this->weekdays[date("w", $dateInTime)] . ", ";
            $html .= date("d.m. H", $dateInTime) . " Uhr";
        }

        return $html;
    }

  public function getCountdownDays($fsEndDate) {
    $now = time();
    $end = strtotime($fsEndDate);
    $left = $end - $now;

    return Mage::getModel('core/date')->date('j', $left);
  }

  public function getCountdownMinutesString($fsEndDate) {
    $now = time();
    $end = strtotime($fsEndDate);
    $left = $end - $now;
    $result = '';

    $days = intval(Mage::getModel('core/date')->date('j', $left));
    if( $days > 0 ) {
      if($days > 1) {
        $result .= $this->__('%s days', $days) . ', ';
      } else {
        $result .= $this->__('%s day', $days) . ', ';
      }
    }

    $hours = intval(Mage::getModel('core/date')->date('H', $left));
    if( $hours > 0 ) {
      if($hours > 1) {
        $result .= $this->__('%s hours', $hours) . ', ';
      } else {
        $result .= $this->__('%s hour', $hours) . ', ';
      }
    }

    $minutes = intval(Mage::getModel('core/date')->date('i', $left));
    if($minutes > 1) {
      $result .= $this->__('%s minutes', $minutes);
    } else {
      $result .= $this->__('%s minute', $minutes);
    }

    return $this->__('%s left', $result);
  }

  public function getEuropeanDateFormat($date) {
    $date = strtotime($date);

    return Mage::getModel('core/date')->date('j.m.Y', $date);
  }

  public function getFlashsaleByCategoryId($categoryId) {
    $collection = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                    ->addFieldToFilter('fs_enabled', '1')
                    ->addFieldToFilter('fs_category_id', $categoryId)
                    ->setOrder('fs_start_date', 'ASC')
                    ->load();

    if($collection) {
      return $collection->getFirstItem();
    }

    return null;
  }
}
