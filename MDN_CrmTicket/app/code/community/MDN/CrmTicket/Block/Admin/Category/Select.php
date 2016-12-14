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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_CrmTicket_Block_Admin_Category_Select extends Mage_Adminhtml_Block_Widget_Form
{

    private $_tab = '&nbsp;&nbsp;&nbsp;&nbsp;';

    /**
     *
     * @param type $selectedValue
     * @param type $productId
     */
    public function getHtmlMenu($value, $name, $classes, $productId, $id = null, $showPrivate = false, $displayChild = true)
    {
        if ($id == null) {
            $id = $name;
        }
        $html = '<select name="' . $name . '" ' . $classes . ' id="' . $id . '">';
        $html .= '<option value=""' . (!$value ? ' selected="selected"' : '') . '></option>';

        //general categories
        $mainCategories = Mage::getModel('CrmTicket/Category')->getGeneralCategories();
        if ($showPrivate) {
            $html .= '<optgroup label="' . $this->__('General') . '">';
        }
        $level = 1;
        foreach ($mainCategories as $cat) {
            $selected = ($value == $cat->getId() ? ' selected ' : '');
            $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . $cat->getTranslatedName(Mage::app()->getStore()->getStoreId(), true) . '</option>';
            //append childs
            if ($displayChild) {
                $html .= $this->appendChild($cat->getId(), $level + 1, $value);
            }
        }
        if ($showPrivate) {
            $html .= '</optgroup>';
        }

        //private
        if ($showPrivate) {
            $mainCategories = Mage::getModel('CrmTicket/Category')->getPrivateCategories();
            $html .= '<optgroup label="' . $this->__('Private') . '">';
            foreach ($mainCategories as $cat) {
                $selected = ($value == $cat->getId() ? ' selected ' : '');
                $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . $this->_tab . $cat->getTranslatedName(Mage::app()->getStore()->getStoreId(), true) . '</option>';
            }
            $html .= '</optgroup>';
        }

        //product categories
        $products = Mage::helper('CrmTicket/Product')->getProducts();
        foreach ($products as $product) {
            if ($productId && ($productId != $product->getId())) {
                continue;
            }
            $productCategories = Mage::getModel('CrmTicket/Category')->getProductCategories($product->getId());
            if ($productCategories->getSize() > 0) {
                $html .= '<optgroup label="' . $product->getName() . '">';
                foreach ($productCategories as $cat) {
                    $selected = ($value == $cat->getId() ? ' selected ' : '');
                    $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . $this->_tab . $cat->getTranslatedName(Mage::app()->getStore()->getStoreId(), true) . '</option>';
                }
                $html .= '</optgroup>';
            }
        }

        //end
        $html .= '</select>';
        return $html;
    }

    /**
     *
     * @param type $parentId
     * @param type $level
     */
    protected function appendChild($parentId, $level, $value)
    {
        $categories = Mage::getModel('CrmTicket/Category')
                ->getCollection()
                ->addFieldToFilter('ctc_parent_id', $parentId)
                ->addFieldToFilter('ctc_is_private', 0);

        $html = '';
        foreach ($categories as $cat) {
            $selected = ($value == $cat->getId() ? ' selected ' : '');
            $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . str_repeat($this->_tab, $level) . $cat->getTranslatedName(Mage::app()->getStore()->getStoreId(), true) . '</option>';
            $html .= $this->appendChild($cat->getId(), $level + 1, $value);
        }
        return $html;
    }

    public function getMainCategoriesAsJson($currentStoreId)
    {

        //general categories
        $mainCategories = Mage::getModel('CrmTicket/Category')->getGeneralCategories();
        $level = 1;
        $array = array();
        foreach ($mainCategories as $cat) {
            if (!$cat->getctc_is_private()) {
                $childs = $this->appendChildAsJson($cat->getId(), $level + 1, $currentStoreId);
            }

                //a main category is valid only if it have at least 1 child for contact page
                if ($childs && count($childs)>0) {
                    $array[$cat->getId()] =
                            array(
                                'id' => $cat->getId(),
                                'name' => $cat->getTranslatedName($currentStoreId, true),
                                'childs' => $childs
                    );
                }
        }

        return json_encode($array);
    }

    
    protected function appendChildAsJson($parentId, $level, $currentStoreId)
    {
        $categories = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_parent_id', $parentId);
        if (count($categories) == 0) {
            return false;
        }

        $array = array();
        foreach ($categories as $cat) {
            if (!$cat->getctc_is_private()) {
                $array[$cat->getId()] =
                        array(
                            'id' => $cat->getId(),
                            'name' => $cat->getTranslatedName($currentStoreId, true),
                            'childs' => $this->appendChildAsJson($cat->getId(), $level + 1, $currentStoreId)
                );
            }
        }
        return $array;
    }

    /**
     * Return all categories as HTML
     * @param type $value
     * @return type
     */
    public function getPublicCategoriesAsHtml($selectedValue)
    {
        $html = '';
        $storeId = Mage::app()->getStore()->getStoreId();

        $parentId = 0;
        $nextDisplayLevel = 1;
        
        $mainCategories = Mage::getModel('CrmTicket/Category')->getPublicCategoriesByStore($parentId, $storeId);

        //recursively
        foreach ($mainCategories as $cat) {
            $selected = ($selectedValue == $cat->getId() ? ' selected ' : '');
            $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . $cat->getTranslatedName($storeId, true) . '</option>';
            $html .= $this->appendChildByStore($cat->getId(), $nextDisplayLevel, $selectedValue, $storeId);
        }

        return $html;
    }

    /**
     *
     * @param type $parentId
     * @param type $level
     */
    protected function appendChildByStore($parentId, $level, $value, $storeId)
    {
        $html = '';

        $categories = Mage::getModel('CrmTicket/Category')->getPublicCategoriesByStore($parentId, $storeId);

        foreach ($categories as $cat) {
            $selected = ($value == $cat->getId() ? ' selected ' : '');
            $html .= '<option ' . $selected . ' value="' . $cat->getId() . '">' . str_repeat($this->_tab, $level) . $cat->getTranslatedName($storeId, true) . '</option>';
            $html .= $this->appendChild($cat->getId(), $level+1, $value);
        }
        
        return $html;
    }
}
