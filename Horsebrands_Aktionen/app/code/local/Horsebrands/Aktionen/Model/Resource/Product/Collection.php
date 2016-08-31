<?php

class Horsebrands_Aktionen_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {

    protected function _buildClearSelectInDefaultOrder($select = null) {

        if (is_null($select)) {
            $select = clone $this->getSelect();
        }
        
        //Mage::log('SELECT before: \n'.$select, null, 'PRODUCTcollection.log');
        
        //$select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);

        return $select;
    }

    public function getAllIdsInDefaultOrder() {

        $idsSelect = $this->_buildClearSelectInDefaultOrder();
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->resetJoinLeft();
        
        //Mage::log('SELECT after: \n'.$idsSelect, null, 'PRODUCTcollection.log');

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

}
