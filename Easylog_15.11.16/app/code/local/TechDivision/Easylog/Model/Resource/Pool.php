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

class TechDivision_Easylog_Model_Resource_Pool
	extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resources
     */
    protected function _construct() 
    {
        $this->_init('easylog/pool', 'entity_id');
    }
    
    public function clear() 
    {
        $del = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $del;
    }
    
    public function loadByOrderId(TechDivision_Easylog_Model_Pool $poolModel, $order_id) {
    	$select = $this->_getReadAdapter()->select()
            ->from($this->getTable('easylog/pool'), array($this->getIdFieldName()))
            ->where('order_id=:order_id');
        if ($id = $this->_getReadAdapter()
        	->fetchOne($select, array('order_id' => $order_id))) {
            $this->load($poolModel, $id);
        }
        else {
            $poolModel->setData(array());
        }
        return $this;
    }
    
    public function getFields() {
    	$fields = $this->_getReadAdapter()
    		->query('SHOW COLUMNS FROM ' . $this->getMainTable())
    		->fetchAll();
    	return $fields;
    }
}