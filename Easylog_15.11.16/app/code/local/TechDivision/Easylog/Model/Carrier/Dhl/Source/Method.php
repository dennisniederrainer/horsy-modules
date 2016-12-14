<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category   	TechDivision
 * @package    	TechDivision_Easylog
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 * 				Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */

class TechDivision_Easylog_Model_Carrier_Dhl_Source_Method
{
    public function toOptionArray()
    {
        $dhl = Mage::getSingleton('easylog/carrier_dhl');
        $arr = array();
        foreach ($dhl->getCode('method') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('easylog')->__($v));
        }
        return $arr;
    }
}
