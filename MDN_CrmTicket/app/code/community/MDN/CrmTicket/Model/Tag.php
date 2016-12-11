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
class MDN_CrmTicket_Model_Tag extends Mage_Core_Model_Abstract {

    const DEFAULT_TEXT_COLOR = '000000'; //black
    const DEFAULT_BG_COLOR = 'A4A4A4'; //grey
    const SHARP = '#';
    const MARGIN = '.1em';
    const TOP_AND_BOTTOM_PADDING = '.1em';
    const RIGTH_AND_LEFT_PADDING = '1em';
    const RADIUS = '15px';

    public function _construct() {

        $this->_init('CrmTicket/Tag', 'ctg_id');
    }

    /**
     * Called before a tag is deleted
     * delete all the tag presents in the tickets
     *
     * @return MDN_CrmTicket_Model_Tag
     */
    protected function _beforeDelete() {
        parent::_beforeDelete();

        $collection = mage::getModel('CrmTicket/TicketTag')->getCollection()->addFieldToFilter('ctt_ctg_id', $this->getId());
        foreach ($collection as $ticketTag) {
            $ticketTag->delete();
        }

        return $this;
    }

    public function getId() {
        return $this->getctg_id();
    }

    public function getName() {
        return $this->getctg_name();
    }

    public function getBgColor() {
        return $this->prepareColorForDisplay((($this->getctg_bg_color()) ? $this->getctg_bg_color() : self::DEFAULT_BG_COLOR));
    }

    public function getTextColor() {
        return $this->prepareColorForDisplay((($this->getctg_text_color()) ? $this->getctg_text_color() : self::DEFAULT_TEXT_COLOR));
    }

    private function prepareColorForDisplay($color) {
        return self::SHARP . trim(str_replace(self::SHARP, '', $color));
    }

    public function getStyle() {

        //background
        $style = 'background: ' . $this->getBgColor() . ';';
        $style .= 'padding: ' . self::TOP_AND_BOTTOM_PADDING . ' ' . self::RIGTH_AND_LEFT_PADDING . ';';
        $style .= 'border-radius: ' . self::RADIUS . ';';
        $style .= 'margin: ' . self::MARGIN . ';';

        //text        
        $style .= 'color: ' . $this->getTextColor() . ';';

        //other part are in the CSS

        return $style;
    }

    /**
     * Use that function to display a tag
     */
    public function getRendering(){
        return Mage::getSingleton('core/layout')
                    ->createBlock('CrmTicket/Admin_Tag_Rendering')
                    ->setTemplate('CrmTicket/Tag/Rendering.phtml')
                    ->setTag($this)
                    ->toHtml();
    }
}
