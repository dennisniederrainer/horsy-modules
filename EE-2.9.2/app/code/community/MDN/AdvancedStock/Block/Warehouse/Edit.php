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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Block_Warehouse_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	
	private $_supplier = null;
	
	/**
	 * Constructeur: on charge le devis
	 *
	 */
	public function __construct()
	{
        $this->_objectId = 'id';
        $this->_controller = 'Warehouse';
        $this->_blockGroup = 'AdvancedStock';
		
		parent::__construct();
				
	}
	
	public function getHeaderText()
    {
        return $this->__('Edit warehouse');
    }
	
	/**
	 * Return url to submit form
	 *
	 * @return unknown
	 */
	public function getSaveUrl()
	{
		return $this->getUrl('AdvancedStock/Warehouse/Save');
	}
	
	public function getBackUrl()
	{
		return $this->getUrl('AdvancedStock/Warehouse/Grid');
	}
	
}
