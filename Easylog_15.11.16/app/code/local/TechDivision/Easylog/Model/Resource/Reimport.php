<?php

/**
 * TechDivision_Easylog_Model_Resource_Reimport
 * 
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
 * @author      TechDivision GmbH - Core Team <info@techdivision.com>
 * 				Johann Zelger <j.zelger@techdivision.com>
 */

class TechDivision_Easylog_Model_Resource_Reimport
	extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize resources
     */
    protected function _construct() 
    {
        $this->_init('easylog/reimport', 'entity_id');
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
    }
    
    public function clear() 
    {
        $del = $this->_getWriteAdapter()->delete($this->getMainTable());
        return $del;
    }
    
    public function loadBy($option, array $where) {
    	$_where = null;
    	foreach ($where as $field => $value) {
    		if ($_where) $_where.=' AND ';
    		$_where .= $field.'=:'.$field;
    	}	
    	$select = $this->_getReadAdapter()->select()->from($this->getMainTable())
            ->where($_where);
    	if ($id = $this->_getReadAdapter()->fetchOne($select, $where)) {
            $this->load($option, $id);
        }
        else {
            $option->setData(array());
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