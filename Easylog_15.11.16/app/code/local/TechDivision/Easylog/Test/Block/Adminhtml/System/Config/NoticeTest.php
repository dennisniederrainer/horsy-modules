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
class TechDivision_Easylog_Test_Block_Adminhtml_System_Config_NoticeTest extends EcomDev_PHPUnit_Test_Case
{
    protected $block;

    public function setUp()
    {
        $this->block = Mage::getSingleton('core/layout')
            ->createBlock('easylog/adminhtml_system_config_notice');
    }
    
    public function testBlockExists()
    {
        $this->assertTrue('TechDivision_Easylog_Block_Adminhtml_System_Config_Notice'
            == get_class($this->block));
    }

    public function testGetVersion()
    {
        Mage::getConfig()->getNode('modules')->children()->TechDivision_Easylog->version = 'foo';
        $this->assertEquals('foo', 
            $this->block->getVersion());
    }
}