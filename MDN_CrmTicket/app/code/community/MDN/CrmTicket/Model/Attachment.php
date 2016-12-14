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
class MDN_CrmTicket_Model_Attachment extends Mage_Core_Model_Abstract
{


    /**
     * When an attachment is created, values are defined like this
     *
     * $att->setFilePath($file);
     * $att->setFileName($file);
     * $att->setFullFilePath($dir.$file);
     * $att->setTicket($ticket);
     * $att->setMessage($message);
     *
     */

    /**
     * Check if attachment can be previewed
     */
    public function canPreview()
    {
        return $this->isPicture($this->getFileName());
    }

    /**
     * return true if the attachment is a Picture type disaplyable in a brower
     */
    public function isPicture($attachmentName)
    {
        $attachmentName = strtolower($attachmentName);
        return (strpos($attachmentName, '.png') > 0
                || strpos($attachmentName, '.jpg') > 0
                || strpos($attachmentName, '.jpeg') > 0
                || strpos($attachmentName, '.gif') > 0
                || strpos($attachmentName, '.bmp') > 0);
    }

    /**
     * Return attachment content
     * @return string
     */
    public function getContent()
    {
        return (file_exists($this->getFullFilePath()))?file_get_contents($this->getFullFilePath()):'';
    }

    /**
     * Disabled : not working on some server taht disable fileInfo
     */
    public function getContentType()
    {
        return ($this->getFullFilePath())?mage::helper('CrmTicket/Attachment')->getMimeTypeContent($this->getFullFilePath()):null;
    }
}
