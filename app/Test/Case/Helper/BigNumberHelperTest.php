<?php 
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('BigNumberHelper', 'View/Helper');

class BigNumberHelperTest extends CakeTestCase
{
    
    public $BigNumberRenderer = null;
    
    
    public function setUp() 
    {
        parent::setUp();
        $Controller = new Controller();
        $View = new View($Controller);
        $this->BigNumberRenderer = new BigNumberHelper($View);
    }
    
    
    public function testNoConvert()
    {
        $count = 120;
        
        $this->assertEqual(
            '120',
            $this->BigNumberRenderer->replaceBigNumbers($count));
    }
    
    
    public function testConvertThousandToAddK()
    {
        $count = 2806;
        
        $this->assertEqual(
            '2.81K',
            $this->BigNumberRenderer->replaceBigNumbers($count));
    }
    
    
    public function testConvertMillionToAddM()
    {
        $count = 2806809;
        
        $this->assertEqual(
            '2.807M',
            $this->BigNumberRenderer->replaceBigNumbers($count));
    }


    public function testConvertLimitMaxCharacter()
    {
        $count = 2806809;
        
        $this->assertEqual(
            '2.8M',
            $this->BigNumberRenderer->replaceBigNumbers($count, 3));
    }
    
    
    public function testConvertBillionToAddB()
    {
        $count = 2806409003;
        
        $this->assertEqual(
            '2.806B',
            $this->BigNumberRenderer->replaceBigNumbers($count));
    }
    
    
    
    public function testroundOffNumbers()
    {
        $bigNumbers = array(
            'John'=> 1500000,
            'Tom' => 4500000);
        
        $this->assertEqual(array(
            'John'=> array(
                'exact' => 1500000,
                'rounded' => '1.5M'),                
            'Tom' => array(
                'exact' => 4500000,
                'rounded' => '4.5M')),
            $this->BigNumberRenderer->roundOffNumbers($bigNumbers));
    }
    
}
?>
