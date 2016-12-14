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

class TechDivision_Easylog_Block_Admin_Reimport_Upload_Form
	extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/uploadPost'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('easylog')->__('Upload'), 'class' => 'fieldset-wide'));
    	$fieldset->addField('file', 'file', array(
            'name'      => 'file',
            'label'     => Mage::helper('easylog')->__('Easylog Datei'),
            'title'     => Mage::helper('easylog')->__('Easylog Datei'),
            'required'  => true
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
