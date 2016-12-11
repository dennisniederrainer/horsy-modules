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

class TechDivision_Easylog_Model_Resource_Pool_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	/**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('easylog/pool');
    }
    
    public function getCsv() {
    	$_fields = $this->getResource()->getFields();
    	$csv = '';
    	$sep = '';
    	foreach ($this as $item) {
    		foreach ($_fields as $field) {
    			$fieldname = $field['Field'];
    			if (!in_array($fieldname, array('entity_id', 'order_id'))) {
    				$csv .= $sep.$item->getData($fieldname);
    				$sep = ';';
    			}
    		}
    		$sep = '';
    		$csv .= "\n";
    	}
    	return $csv;	
    }
    
}