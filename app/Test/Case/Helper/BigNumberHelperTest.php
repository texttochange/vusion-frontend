<?php 
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('BigNumberHelper', 'View/Helper');

class BigNumberHelperTest extends CakeTestCase{
		
		public $BigNumberRenderer = null;
		
		public function setUp() 
		{
				parent::setUp();
				$Controller = new Controller();
				$View = new View($Controller);
				$this->BigNumberRenderer = new BigNumberHelper($View);
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
		
		public function testConvertBillionToAddB()
		{
				$count = 2806409003;
				
				$this->assertEqual(
						'2.806B',
						$this->BigNumberRenderer->replaceBigNumbers($count));
		}		
}
?>
