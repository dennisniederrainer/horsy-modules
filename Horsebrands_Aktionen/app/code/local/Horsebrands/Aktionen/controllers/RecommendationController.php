<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vitali Fehler
 * Date: 25.03.13
 */
class Horsebrands_Aktionen_RecommendationController extends Mage_Core_Controller_Front_Action {

    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        $flashSaleId = (int) $this->getRequest()->getParam('fs', false);
        $flashSale = null;
        if(isset($flashSaleId)) {
            $flashSale = Mage::getModel('PrivateSales/FlashSales')->load($flashSaleId);
        }

        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);


        if($flashSale) {
            $helper = Mage::helper('aktionen/date');
            $endDate = strtotime($flashSale->getFsEndDate());
            $category->setFlashSale($flashSale);
            $category->setEndDate($helper->dateToFormattedString($endDate));
        }

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }

        Mage::register('recommendation_category', $category);
        if($flashSale) {Mage::register('recommendation_flash_sale', $flashSale);}
        return $category;
    }

    protected function _initProduct()
    {
        $productID = (int) $this->getRequest()->getParam('id', false);
        $flashSaleID = (int) $this->getRequest()->getParam('fs', false);
        $flashSale = null;
        if(isset($flashSaleId)) {
            $flashSale = Mage::getModel('PrivateSales/FlashSales')->load($flashSaleId);
        }

        if(!$productID) return false;

        $product = Mage::getModel('catalog/product')->load($productID);

        Mage::register('recommendation_product', $product);
        if($flashSale) {Mage::register('recommendation_flash_sale', $flashSale);}
        return $product;
    }

    public function indexAction()
    {
        $this->loadLayout();
        //$this->getLayout()->getBlock('recommendation');
        $this->renderLayout();
    }

    public function campaignAction()
    {
        // Eine Aktion ist eine Kategorie
        if ($category = $this->_initCategory())
        {
            $this->loadLayout();
            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect())
        {
            $this->_forward('noRoute');
        }
    }

    public function productAction() {
        $product = $this->_initProduct();
        
        if($product->getId()) {
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    public function confirmAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function formPostAction()
    {
        $recommendationCampaignID = $this->getRequest()->getParam('recommendationCampaignID');
        $recommendationArticleID = $this->getRequest()->getParam('recommendationArticleID');
        $flashSaleID = $this->getRequest()->getParam('recommendationFlashSaleID');
        $receiverEmails = $this->getRequest()->getParam('recommendationEmail');
        $recommendationLink = $this->getRequest()->getParam('recommendationLink');
        $customer = Mage::helper('customer')->getCustomer();
        $inviteFriendLink = Mage::getBaseUrl();
        $inviteFriendLink .= "hello/?invitation_id=".$customer->getId();

        $sender = array('name' => "service@horsebrands.de", 'email' => "HORSEBRANDS");
        $vars = array();
        $_catalogImageHelper = Mage::helper('catalog/image');
        $_catalogOutputHelper = Mage::helper('catalog/output');
        $flashSale = null;

        if(isset($flashSaleID)) {
            $flashSale = Mage::getModel('PrivateSales/FlashSales')->load($flashSaleID);

            $helper = Mage::helper('aktionen/date');
            $endDate = strtotime($flashSale->getFsEndDate());
            $campaignEnd = $helper->dateToFormattedString($endDate);
        }

        $salesRuleId = Horsebrands_Coupon_Model_Coupon::ACTIVE_INITE_FRIEND_COUPON_RULE;
        $cartRule = Mage::getModel('salesrule/rule')->load($salesRuleId);
        $couponValue = $cartRule->getDiscountAmount();

        if(!empty($recommendationCampaignID))
        {
            $emailTemplateID = 96;
            $category = Mage::getModel('catalog/category')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($recommendationCampaignID);
            $vars = array('customerFirstname' => $customer->getFirstname(), 
                            'customerLastname' => $customer->getLastname(), 
                            'recommendationLink' => $recommendationLink, 
                            'campaignDescription' => $category->getDescription(),
                            'campaignImage' => $category->getImageUrl(), 
                            'campaignLink' => $category->getUrl(), 
                            'campaignEnd' => $campaignEnd, 
                            'inviteFriendLink' => $inviteFriendLink,
                            'couponValue' => number_format($couponValue));

            if($flashSale) {
                $vars['campaignName'] = $flashSale->getFsName(); 
                $vars['campaignSubTitle'] = $flashSale->getFsDescription();
            }

            $redirectUrl = $category->getUrlPath();
        }
        else
        {
            $emailTemplateID = 95;
            $product = Mage::getModel('catalog/product')->load($recommendationArticleID);
            $coreHelper = Mage::helper('core');
            $taxHelper = Mage::helper('tax');
            $uvp = $coreHelper->currency($product->getMsrp(), true, false);
            $price = $coreHelper->currency($taxHelper->getPrice($product, $product->getPrice()), true, false);

            // $category = Mage::getModel('catalog/category')
            //     ->setStoreId(Mage::app()->getStore()->getId())
            //     ->load($flashSale->getFsCategoryId());

            $vars = array('customerFirstname' => $customer->getFirstname(), 'customerLastname' => $customer->getLastname(), 'recommendationLink' => $recommendationLink
                         ,'productName' => $product->getName(), 'productDescription' => $_catalogOutputHelper->productAttribute($product, $product->getDescription(), 'description')
                         , 'productImage' => $_catalogImageHelper->init($product, 'image'), 'uvp' => $uvp, 'price' => $price, //'campaignName' => $flashSale->getFsName()
                         //, 'campaignLink' => $category->getUrl(), 'inviteFriendLink' => $inviteFriendLink, 
                         'couponValue' => number_format($couponValue));
            $redirectUrl = $product->getUrlPath();
        }

        $storeID = Mage::app()->getStore()->getId();
        $recipientCount = 0;
        $emailtemplate = Mage::getModel('core/email_template');
        foreach ($receiverEmails as $recipient) {
            if(strlen(trim($recipient))>0) {
                $emailtemplate
                    ->sendTransactional($emailTemplateID, $sender, $recipient, $recipient, $vars, $storeID);
                $recipientCount++;
            }
        }

        if($recipientCount>1) {
            $messagetext = "Die Empfehlung wurde erfolgreich an deine Freunde versandt.";
        } else {
            $messagetext = "Die Empfehlung wurde erfolgreich versandt.";
        }
        $response = array("success" => "true", "messageblock" => Mage::helper('hello')->getAjaxSuccessMessageBlock($messagetext));
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
?>