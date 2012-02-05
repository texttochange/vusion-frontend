<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ScriptsController', 'Controller');


/**
 * TestScriptsControllerController *
 */
class TestScriptsController extends ScriptsController {
/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

/**
 * ScriptsController Test Case
 *
 */
class ScriptsControllerTestCase extends ControllerTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	//public $fixtures = array('app.ProgramDocument');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Scripts = new TestScriptsController();
		$this->Scripts->constructClasses();
		
		$options = array('database' => 'm4h');
		$this->Scripts->Script = new Script($options);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Scripts);

		parent::tearDown();
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		/*$Scripts = $this->generate('Scripts', array(
			'methods' => array(
				'paginate'
			),
			'models' => array(
				//'Program' => array('find'),
				'Script' => array('recursive')
				),
			)
			);
		
		$Scripts
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue(array('one','two')));
		*/
		
		
		$this->testAction("/m4h/scripts", array('data' => '', 'method' => 'get'));
		$this->assertEquals($this->vars['programName'], 'm4h');
		//debug(array('toto' => 'tata'));
		//throw new Exception(serialize($result));
		//debug($result, true);
	}

/**
 * testView method
 *
 * @return void
 */
 /*
	public function testView() {
		$data = array(
			'Script' => array(
				'id' => 1,
				'name' => 'm4h',
				'country' => 'uganda',
				'created' => '2012-01-24 15:29:24',
				'modified' => '2012-01-24 15:29:24'
			)
		);
		
		$Scripts = $this->generate('Scripts', array(
			'models' => array(
				//'Program' => array('find'),
				'Script' => array('exists','read')
				),
			)
			);
		
		$Scripts->Script
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		
		$Scripts->Script
			->expects($this->once())
			->method('read')
			->will($this->returnValue($data));
		
		
		$this->testAction("/Scripts/view/1");
		//print_r($result);
		$this->assertEquals($data, 
			$this->vars['Script']
			);
	}
*/
/**
 * testAdd method
 *
 * @return void
 */

 	public function testAdd() {
		 $data = array(
		 	 'script' => array(
		 	 	 //'id' => '3',
		 	 	 'name' => 'NewProgramDocument',
		 	 	 'country' => 'Somewhere',
		 	 	 )
		 	 );
		 	 
    		 $this->testAction('/m4h/scripts/add', array('data' => $data, 'method' => 'post'));
	}


/**
 * testEdit method
 *
 * @return void
 */
	public function testEdit() {

	}

/**
 * testDelete method
 *
 * @return void
 */
	public function testDelete() {

	}

}
