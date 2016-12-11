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
class MDN_CrmTicket_Helper_Tag extends Mage_Core_Helper_Abstract {

    /**
     * Get existing ticket tag association
     *
     * @param type $tagId
     * @param type $ticketId
     * @return type
     */
    public function getTicketTag($tagId,$ticketId){
        return mage::getModel('CrmTicket/TicketTag')->getCollection()
                ->addFieldToFilter('ctt_ctg_id',$tagId)
                ->addFieldToFilter('ctt_ct_id',$ticketId)
                ->getFirstItem();
    }

    /**
     * Create a tag association between a tag and a ticket
     * 
     * @param type $tagId
     * @param type $ticketId
     * @return boolean
     */
    public function addTicketTag($tagId,$ticketId){

        $created = false;

        $ticketTag = $this->getTicketTag($tagId, $ticketId);
        
        if(!$ticketTag->getId()){
            $newTicketTag = mage::getModel('CrmTicket/TicketTag');
            $newTicketTag->setctt_ct_id($ticketId);
            $newTicketTag->setctt_ctg_id($tagId);
            $newTicketTag->save();
            $created = true;
        }

        return $created;
    }

    /**
     * Delete a tag association between a tag and a ticket
     *
     * @param type $tagId
     * @param type $ticketId
     * @return boolean
     */
    public function deleteTicketTag($tagId,$ticketId){        
        $deleted = false;

        $ticketTag = $this->getTicketTag($tagId, $ticketId);

        if($ticketTag){
            $ticketTag->delete();
            $deleted = true;
        }
        
        return $deleted;
    }

    /**
     * Returns all available tags
     */
    public function getAllTags() {

        $collection = mage::getModel('CrmTicket/Tag')->getCollection();

        $tab = array();
        foreach ($collection as $tag) {

            $tab[$tag->getId()] = $tag->getName();
        }       
        return $tab;
    }

    /**
     * Get existing ticket tag association
     *
     * @param type $tagId
     * @param type $ticketId
     * @return type
     */
    public function getTicketIdsByTagId($tagId){
        return mage::getModel('CrmTicket/TicketTag')->getCollection()
                ->addFieldToFilter('ctt_ctg_id',$tagId)
                ->getColumnValues('ctt_ct_id');
    }


}