<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PoolTest
 *
 * @author sebastian
 */
class TechDivision_Easylog_Test_Model_PoolTest extends EcomDev_PHPUnit_Test_Case
{
    const XML_PATH_ALLGEMEIN_MANDANTID = 'easylog/allgemein/mandant_id';
    const XML_PATH_ALLGEMEIN_ABSENDERID = 'easylog/allgemein/absender_id';
    const XML_PATH_ALLGEMEIN_TEILNAHMEID = 'easylog/allgemein/teilnahme_id';
    /**
     * @var Mage_Core_Model_Store
     */
    protected $store = null;

    public function setUp()
    {
        $this->store = Mage::app()->getStore(0)->load(0);
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testSaveByOrder()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $this->store->setConfig('carriers/easylog_dhl/participation_number_national', 12);
        $this->store->setConfig(self::XML_PATH_ALLGEMEIN_ABSENDERID, 1);
        $this->store->setConfig(self::XML_PATH_ALLGEMEIN_MANDANTID, 2);
        $this->store->setConfig(self::XML_PATH_ALLGEMEIN_TEILNAHMEID, 3);
        $order->setStore($this->store);

        $ids = array(
            'verfahren' => '1',
            'product' => '101',
            'teilnahme' => '22',
        );

        $dhlModelMock = $this->getModelMock('easylog/carrier_dhl', array('getCode'));
        $dhlModelMock->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($ids));
        $this->replaceByMock('singleton', 'easylog/carrier_dhl', $dhlModelMock);

        $poolMock = $this->getModelMock('easylog/pool', array('save'));
        $poolMock->expects($this->any())
            ->method('save')
            ->will($this->returnValue(''));
        $this->replaceByMock('model', 'easylog/pool', $poolMock);

        $pool = Mage::getModel('easylog/pool');
        $pool->saveByOrder($order);
        $this->assertEquals(22,$pool->getData('POOL_V_TEILNAHME'));
    }
}
?>
