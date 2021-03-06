<?php
/**
 * Dhl_Intraship_Model_Mysql4_Shipment
 *
 * @category  Models
 * @package   Dhl_Intraship
 * @author    Stephan Hoyer <stephan.hoyer@netresearch.de>
 * @copyright Copyright (c) 2010 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Dhl_Intraship_Model_Mysql4_Shipment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('intraship/shipment', 'id');
    }
}