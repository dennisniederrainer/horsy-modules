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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    const INDENT = '&nbsp;&nbsp';
    const CR = '<br>';
    const MAX_CATEGORY_TREE_DEEPNESS = 30;

    public function render(Varien_Object $row) {

        $html = '';

        $cat_id = $this->getCategoryId($row);

        if ($cat_id) {
            
            $html = $this->getCategoryNameTree($html, $cat_id);
        }

        return $html;
    }

    //recursive call to dispaly all parents
    private function getCategoryNameTree($html, $catId) {
        $cat = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_id', $catId)->getFirstItem();

        if ($cat->getId() > 0) {
            $html = $this->getCategoryParents($html, $cat);
            $html = $html . $cat->getctc_name();
        }

        return $html;
    }

    /**
     * get parent name recursively
     * @param string $html
     * @param type $cat
     * @return string
     */
    private function getCategoryParents($html, $cat) {

        $loopCount = 0;
        
        $currentCat = $cat;
        while ($currentCat->getctc_parent_id() > 0) {
            
            $loopCount ++;
            if($loopCount>self::MAX_CATEGORY_TREE_DEEPNESS)
                break;
            
            $parentCat = Mage::getModel('CrmTicket/Category')->getCollection()->addFieldToFilter('ctc_id', $currentCat->getctc_parent_id())->getFirstItem();
            if ($parentCat->getId() > 0) {
                $html .= $parentCat->getctc_name() . self::CR.str_repeat(self::INDENT,$loopCount);
            }
            $currentCat = $parentCat;
        }
        return $html;
    }

    private function getCategoryId($row) {
        $cat_id = $row->getct_category_id();
        if (!$cat_id) {
            $cat_id = $row->getcerr_category_id();
        }
        return $cat_id;
    }

}
