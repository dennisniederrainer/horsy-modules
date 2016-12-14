<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DhlTest
 *
 * @author sebastian
 */
class TechDivision_Easylog_Test_Model_Carrier_DhlTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store = null;

    public function setUp()
    {
        $this->store = Mage::app()->getStore(0)->load(0);
    }

    public function testGetCode()
    {
        // test case national
        $path = 'carriers/easylog_dhl/participation_number_national';
        $this->store->setConfig($path, 12);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','national');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(12, $codes['teilnahme']);
        $this->assertTrue(array_key_exists('verfahren', $codes));
        $this->assertEquals(1, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(101, $codes['product']);
        
        // test case nationalexpress
        $path = 'carriers/easylog_dhl/participation_number_nationalexpress';
        $this->store->setConfig($path, 10);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','nationalexpress');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(10, $codes['teilnahme']);
         $this->assertTrue(array_key_exists('verfahren', $codes));
        $this->assertEquals(72, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(7202, $codes['product']);
        
        // test case regioat
        $path = 'carriers/easylog_dhl/participation_number_regioat';
        $this->store->setConfig($path, 9);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','regioat');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(9, $codes['teilnahme']);
        $this->assertTrue(array_key_exists('verfahren', $codes));
        $this->assertEquals(1, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(666, $codes['product']);
        
        // test case euro
        $path = 'carriers/easylog_dhl/participation_number_euro';
        $this->store->setConfig($path, 8);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','euro');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(8, $codes['teilnahme']);
        $this->assertTrue(array_key_exists('verfahren', $codes));
        $this->assertEquals(54, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(5401, $codes['product']);
        
        // test case europlus
        $path = 'carriers/easylog_dhl/participation_number_europlus';
        $this->store->setConfig($path, 7);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','europlus');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(7, $codes['teilnahme']);
        $this->assertEquals(69, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(6901, $codes['product']);
        
        // test case international
        $path = 'carriers/easylog_dhl/participation_number_international';
        $this->store->setConfig($path, 5);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','international');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(5, $codes['teilnahme']);
        $this->assertEquals(53, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(5301, $codes['product']);
        
        // test case internationalexpress
        $path = 'carriers/easylog_dhl/participation_number_internationalexpress';
        $this->store->setConfig($path, 4);
        $codes = Mage::getModel('easylog/carrier_dhl')->getCode('ids','internationalexpress');
        $this->assertTrue(is_array($codes));
        $this->assertTrue(array_key_exists('teilnahme', $codes));
        $this->assertEquals(4, $codes['teilnahme']);
        $this->assertEquals(99, $codes['verfahren']);
        $this->assertTrue(array_key_exists('product', $codes));
        $this->assertEquals(7202, $codes['product']);
    }
    
    
    public function testGetParticipationNumber()
    {
        $this->store->setConfig('carriers/easylog_dhl/participation_number_national',1);
        $this->assertEquals(1,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('national'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_nationalexpress',2);
        $this->assertEquals(2,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('nationalexpress'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_regioat',3);
        $this->assertEquals(3,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('regioat'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_euro',4);
        $this->assertEquals(4,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('euro'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_europlus',5);
        $this->assertEquals(5,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('europlus'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_international',6);
        $this->assertEquals(6,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('international'));
        $this->store->setConfig('carriers/easylog_dhl/participation_number_internationalexpress',7);
        $this->assertEquals(7,Mage::getModel('easylog/carrier_dhl')->getParticipationNumber('internationalexpress'));
    }
}
?>
