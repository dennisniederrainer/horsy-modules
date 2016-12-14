<?php
class TechDivision_Easylog_Test_Model_ReimportTest extends EcomDev_PHPUnit_Test_Case
{

    public function testVerify()
    {
        $reimportModel = Mage::getModel('easylog/reimport');
        $this->assertFalse($reimportModel->verify(array()));
        $this->assertFalse($reimportModel->verify(array(1,2,3)));

        $validData = array();
        $validData[] = $this->generateValidData();
        $this->assertTrue($reimportModel->verify($validData));
        $validData[] = '';
        $this->assertTrue($reimportModel->verify($validData));
        $validData[] = $this->generateValidData();
        $this->assertTrue($reimportModel->verify($validData));
    }


    protected function generateValidData()
    {
        $returnData = array();
        for ($i = 0; $i < 41; $i++) {
            $returnData[] = $i;
        }
        return implode(';', $returnData);
    }
}
