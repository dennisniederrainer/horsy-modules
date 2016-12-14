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

class TechDivision_Easylog_Model_Reimport
	extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('easylog/reimport');
    }

    /**
     * receives the id
     *
     * @return int The id of the Item
     */
    public function getId() {
    	return $this->getData('entity_id');
    }

    /**
     * import easylog file
     *
     * @return int $importCount The Counter for imported rows
     */
    public function import($file)
    {
        $easylogFile = file($file);
    	if (false !== $easylogFile && $this->verify($easylogFile)) {
    		$fields = $this->getResource()->getFields();
    		$reimportModel = Mage::getModel('easylog/reimport');
    		$importCount = 0;
    		foreach ($easylogFile as $easylogFileRow)
			{
				// convert from ISO-8859-1 to UTF-8 (windows...)
				$easylogFileRow = utf8_encode($easylogFileRow);
				// get new instance of reimport model
				$reimportModel->unsetData();
				// get item fields
				$fieldValues = explode(";", $easylogFileRow);
				if (!isset($fieldValues[55])) $fieldValues[55] = "";
				// set fields to not use for import
				$noImportFields = array('entity_id', 'created_at',
					'updated_at', 'status');
				// 'cause entity_id is our first field
				$fieldKeyOffset = 1;
				foreach ($fields as $key => $field) {
					if (!in_array($field["Field"],$noImportFields)) {
						$fieldValueKey = $key - $fieldKeyOffset;
						// try to load by SITEMS_ID
						if ($field["Field"] == 'SITEMS_ID') {
							$reimportModel
								->loadBySitemsId($fieldValues[$fieldValueKey]);
							// if id exists continue with next
							if ($reimportModel->getId()) {
								$importCount--;
								break;
							}
						}
                        if (!array_key_exists($fieldValueKey, $fieldValues)) {
                            $fieldValues[$fieldValueKey] = '';
                        }
						$reimportModel->setData($field["Field"],
							$fieldValues[$fieldValueKey]);
					}
				}
				$reimportModel->save();
				$importCount++;
			}
    	} else {
    		Mage::throwException(Mage::helper('easylog')->__('Invalid easylog export file'));
    	}
    	return $importCount;
    }

    /**
     *
     * checks if in every (except) empty rows were 35 fields present
     *
     * @param array $fileData
     * @return boolean true if every non-empty rows containing 35 fields, false otherwise
     */
    public function verify(array $fileData) {
        $isValid = false;
        $rowsContainingData = 0;
        foreach ($fileData as $row) {
            $rowFields = array();
            // ignore empty rows
            if (0 < strlen(trim($row))) {
                $rowFields = explode(';', $row);
                // last field used for synch is located in field 35 (LeistungenText)
                if (35 > count($rowFields)) {
                    break;
                }
                ++ $rowsContainingData;
            }
        }
        // data found -> data seems to be valid
        if (0 < $rowsContainingData) {
            $isValid = true;
        }
        return $isValid;
    }

    public function loadBy(array $where)
    {
        $this->_getResource()->loadBy($this, $where);
        return $this;
    }

    public function loadBySitemsId($id)
    {
    	$code = $this->loadBy(array('SITEMS_ID' => $id));
    	if ($code->getData('SITEMS_ID') != $id) $this->unsetData();
    	return $this;
    }

}