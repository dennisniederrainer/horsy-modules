<?php
/**
 * Dhl_Intraship_Block_Checkout_Onepage_Parcelannouncement
 *
 * @category  Block
 * @package   Dhl_Account
 * @author    Michael Lühr <michael.luehr@netresearch.de>
 * @copyright Copyright (c) 2012 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Dhl_Account_Block_Checkout_Onepage_Parcelannouncement
    extends Mage_Core_Block_Template
{

    /**
     * Internal constructor, that is called from real constructor.
     *
     * 
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('account/checkout/onepage/parcelannouncement.phtml');
    }

    /**
     * wrapper for config getter
     *
     * @return bool
     */
    public function isParcelAnnouncementEnabled()
    {
        return Mage::getModel('dhlaccount/config')->isParcelAnnouncementEnabled();
    }

    /**
     * wrapper for config getter
     *
     * @return bool
     */
    public function isPreferredDeliveryDateEnabled()
    {
        return Mage::getModel('dhlaccount/config')->isPreferredDeliveryDateEnabled();
    }

}
