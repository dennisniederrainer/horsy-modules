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
 * @author      Sebastian Ertner <sebastian.ertner@netresearch.de
> */
class TechDivision_Easylog_Block_Adminhtml_System_Config_Notice extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Custom template
     *
     * @var string
     */
    protected $_template = 'easylog/system/config/notice.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $fieldset
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        $originalData = $fieldset->getOriginalData();
        $this->addData(array(
            'fieldset_label' => $fieldset->getLegend(),
        ));
        return $this->toHtml();
    }

    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode('modules')->children()->TechDivision_Easylog->version;
    }
}