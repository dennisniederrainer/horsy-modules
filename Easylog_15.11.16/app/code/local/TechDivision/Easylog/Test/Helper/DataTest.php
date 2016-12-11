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
 * @author      Sebastian Ertner <sebastian.ertner@netresearch.de>
 */

class TechDivision_Easylog_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
   
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store;

    public function setUp()
    {
        $this->store  = Mage::app()->getStore(0)->load(0);
    }
    
    public function testGetSupportMail()
    {
        $path = 'easylog/info/support_mail';
        $this->store->resetConfig();
        $this->store->setConfig($path, 'foo@bar.com');
        $this->assertEquals('foo@bar.com', 
               Mage::helper('easylog/data')->getSupportMail());
        $this->assertNotEquals('bar',
            Mage::helper('easylog/data')->getSupportMail());
    }
}

