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

class TechDivision_Easylog_Block_Admin_Reimport_Upload
	extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
    	$this->_objectId = 'entity_id';
    	$this->_blockGroup = 'easylog';
        $this->_controller = 'admin_reimport';
        $this->_mode = 'upload';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('easylog')->__('Upload'));
        $this->_updateButton('save', 'onclick', 'editForm.submit();');
        $this->removeButton('back');
        $this->removeButton('reset');

        $this->_formScripts[] = "
        ";
    }

    public function getHeaderText()
    {
		return Mage::helper('easylog')->__('Upload Easylog Datei');
    }

}
