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
class MDN_CrmTicket_Block_Admin_Widget_Grid_Column_Renderer_MailStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $color = 'black';
        $status = $row->getctm_status();

        if ($status) {
            switch ($status) {
                case MDN_CrmTicket_Model_Email::STATUS_NEW:
                    $color = 'black';
                    break;
                case MDN_CrmTicket_Model_Email::STATUS_ASSOCIATED:
                    $color = 'green';
                    break;
                case MDN_CrmTicket_Model_Email::STATUS_SPAM:
                case MDN_CrmTicket_Model_Email::STATUS_ERROR:
                    $color = 'red';
                    break;
                case MDN_CrmTicket_Model_Email::STATUS_IGNORED:
                    $color = 'orange';
                    break;
            }
        }

        return '<font color="' . $color . '">' . $status . '</font>';
    }

}
