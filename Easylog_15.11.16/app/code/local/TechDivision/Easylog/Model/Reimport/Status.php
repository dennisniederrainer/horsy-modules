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

class TechDivision_Easylog_Model_Reimport_Status
	extends Mage_Core_Model_Abstract
{
	const IMPORTED = 1;
	const SYNCED = 2;
	const NOT_SYNCED = 3;
	const SHIPPED_ALREADY = 4;

	protected $options = array (
		self::IMPORTED => 'Importiert',
		self::SYNCED => 'Abgeglichen',
		self::NOT_SYNCED => 'Bestellnummer nicht vorhanden',
		self::SHIPPED_ALREADY => 'Versand wurde bereits erstellt',
	);
	
    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('easylog/reimport_status');
    }

 	public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
	    if (!$defaultValues) {
	    	$options = array();
	    	foreach ($this->options as $value => $label) {
	    		$options[$value] = array('value'=>(string)$value, 'label'=>$label);
	    	}
	        return $options;
	    } else {
	    	return $this->options;
	    }
    }
    
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = $this->getAllOptions(false);

        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item['label'];
                }
            }
            return $values;
        }
        else {
            foreach ($options as $item) {
                if ($item['value'] == $value) {
                    return $item['label'];
                }
            }
            return false;
        }
    }

}