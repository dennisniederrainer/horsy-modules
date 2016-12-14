<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 BoostMyshop (http://www.boostmyshop.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Model_Category extends Mage_Core_Model_Abstract
{

    const MAX_CATEGORY_TREE_DEEPNESS = 30;
    
    // category type
    const TYPE_GENERAL = 'general';
    const TYPE_PRODUCT_GENERAL = 'product_general';
    const TYPE_PRODUCT_SPECIFIC = 'product_specific';

    
    public function _construct()
    {
        $this->_init('CrmTicket/Category', 'ctc_id');
    }

    
    public function getTickets($displayPrivate = false)
    {
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

       // else the category JSON is returned with an error
       $this->setctc_name(trim(str_replace('"', " ", $this->getctc_name())));
    }

    /**
     * return type of category
     *
     */
    public function getCategoryTypes()
    {
        $retour = array();
        $helper = Mage::helper('CrmTicket');
        $retour[MDN_CrmTicket_Model_Category::TYPE_GENERAL] = $helper->__('General');
        $retour[MDN_CrmTicket_Model_Category::TYPE_PRODUCT_GENERAL] = $helper->__('Product general');
        $retour[MDN_CrmTicket_Model_Category::TYPE_PRODUCT_SPECIFIC] = $helper->__('Product specific');
        return $retour;
    }

    /**
     * Return generic categories (not associated to a product)
     */
    public function getAllCategories()
    {
        return $this->getCollection()->setOrder('ctc_name', 'ASC');
    }

    /**
     * Return all root categories (not associated to a product)
     */
    public function getPublicCategories()
    {
        return $this->getCollection()
                        ->addFieldToFilter('ctc_produit_id', 0)
                        ->addFieldToFilter('ctc_is_private', 0)
                        ->setOrder('ctc_name', 'ASC');
    }

    /**
     * Return generic categories (not associated to a product)
     */
    public function getGeneralCategories()
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter('ctc_produit_id', 0)
                            ->addFieldToFilter('ctc_is_private', 0)
                            ->addFieldToFilter('ctc_parent_id', 0)
                            ->setOrder('ctc_name', 'ASC');
        return $collection;
    }

    /**
     * Return generic categories (not associated to a product)
     */
    public function getPublicCategoriesByStore($parentId, $storeId)
    {
        return $this->getPublicCategories()
                            ->addFieldToFilter('ctc_parent_id', $parentId)
                            ->addFieldToFilter('ctc_store_id', array('in' => array(0, $storeId)));
    }


    /**
     * Return all root categories (not associated to a product)
     */
    public function getRootCategories()
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter('ctc_produit_id', 0)
                            ->addFieldToFilter('ctc_parent_id', 0)
                            ->setOrder('ctc_name', 'ASC');

        return $collection;
    }


    /**
     * Return sub categories associate to a parent
     */
    public function getSubCategories($ctc_parent_id)
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter('ctc_produit_id', 0)
                            ->addFieldToFilter('ctc_parent_id', $ctc_parent_id)
                            ->setOrder('ctc_name', 'ASC');
        return $collection;
    }

    /**
     * return the number of sub categories
     * @return type
     */
    public function getSubCategoriesCount()
    {
        return $this->getOwnSubCategories()->getSize();
    }

    /**
     * return a collection of CrmTicket/Category which are the sub categories of the current category
     * @return type
     */
    public function getOwnSubCategories()
    {
        $collection = $this->getCollection()
                          ->addFieldToFilter('ctc_parent_id', $this->getId());
       
        return $collection;
    }
    
    /**
     * Return categories for one product
     * @param type $productId
     * @return type
     */
    public function getProductCategories($productId)
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter('ctc_is_private', 0)
                            ->addFieldToFilter('ctc_produit_id', $productId)
                            ->setOrder('ctc_name', 'ASC');
        return $collection;
    }
        
    /**
     * Return generic categories (not associated to a product)
     */
    public function getPrivateCategories()
    {
        $collection = $this->getCollection()
                            ->addFieldToFilter('ctc_produit_id', 0)
                            ->addFieldToFilter('ctc_is_private', 1)
                            ->setOrder('ctc_name', 'ASC');
        return $collection;
    }
    
    /**
     * return product associated to category
     */
    public function getProduct()
    {
        if ($this->getctc_produit_id() == 0) {
            return null;
        } else {
            return Mage::getModel('catalog/product')->load($this->getctc_produit_id());
        }
    }
    
    /**
     * Return category full name (with parents)
     */
    public function getFullName()
    {
        $name = '';
        
        //add product
        $product = $this->getProduct();
        if ($product) {
            $name = $product->getName().' > ';
        }
        
        //add category name
        $name .= $this->getctc_name();
        
        return $name;
    }
    
    public function getName()
    {
        return $this->getctc_name();
    }

    /**
     * Get name with the name of the parent categorys
     * @return type
     */
    public function getCompleteName()
    {
        $name = $this->getName();
        $parentId = $this->getctc_parent_id();

        //prevent infinite loop if bad category parent manipulations
        $loopcount = 0;
        while ($parentId != 0) {

            $loopcount++;
            if($loopcount>self::MAX_CATEGORY_TREE_DEEPNESS)
                break;

            $currentId = $parentId;
            $cat = Mage::getModel('CrmTicket/category')->load($currentId);
            if ($cat) {
                $name = $cat->getName()." > ".$name;
                //avoid infinite loop
                if($currentId != $cat->getctc_parent_id()){
                    $parentId = $cat->getctc_parent_id();
                }else{
                   // if current = parent, mean that databse has been manipulated, or there has been a bad problem while deleting categories
                   //this avoid infinite loop
                   $parentId = 0;
                }
            }
        }

        return $name;
    }
    
    /**
     * Update msg count
     */
    public function updateTicketCount()
    {
        //apply only if category is saved
        if ($this->getId()) {
            $ids = Mage::getModel('CrmTicket/Ticket')
                            ->getCollection()
                            ->addFieldToFilter('ct_category_id', $this->getId())
                            ->getAllIds();
            $this->setctc_ticket_count(count($ids))->save();
        }
    }

    /**
     * get serialized translated name for the current store
     *
     * @return type
     */
    public function getTranslatedName($currentStoreId, $useDefault)
    {
        $name = '';
      
        if ($useDefault) {
            $name = $this->getctc_name();
        }

        $transaltions = $this->getctc_name_translation_by_store();
        if ($transaltions) {
            $translationList  = unserialize($transaltions);
            if ($translationList) {
                foreach ($translationList as $storeId => $categoryNameTranslation) {
                    if ($storeId == $currentStoreId) {
                        if (!empty($categoryNameTranslation)) {
                            $name = $categoryNameTranslation;
                        }
                    }
                }
            }
        }
        return $name;
    }
}
