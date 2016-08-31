<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitali Fehler
 * Date: 25.03.13
 */
    class Horsebrands_Aktionen_Block_Recommendation_View extends Mage_Core_Block_Template
    {
        protected $campaign = null;
        protected $product = null;
        protected $flashSale = null;

        public function getRecommendationCampaign()
        {
            if(Mage::registry('recommendation_category'))
            {
                $recommendationCategory = Mage::registry('recommendation_category');
                $this->campaign = $recommendationCategory;
            }

            return $this->campaign;
        }

        public function getRecommendationProduct()
        {
            if(Mage::registry('recommendation_product'))
            {
                $recommendationProduct = Mage::registry('recommendation_product');
                $this->product = $recommendationProduct;
            }

            return $this->product;
        }

        public function getFlashSale()
        {
            if(Mage::registry('recommendation_flash_sale'))
            {
                $this->flashSale = Mage::registry('recommendation_flash_sale');
            }

            return $this->flashSale;
        }
    }
?>