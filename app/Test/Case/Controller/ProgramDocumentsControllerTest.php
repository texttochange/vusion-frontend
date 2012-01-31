<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ProgramDocumentsController', 'Controller');


/**
 * TestProgramDocumentsController *
 */
class TestProgramDocumentsController extends ProgramDocumentsController {
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
 * ProgramDocumentsController Test Case
 *
 */
class ProgramDocumentsControllerTestCase extends ControllerTestCase {
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

		//$this->ProgramDocuments = new TestProgramDocumentsController();
		//$this->ProgramDocuments->constructClasses();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ProgramDocuments);

		parent::tearDown();
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		$ProgramDocuments = $this->generate('ProgramDocuments', array(
			'methods' => array(
				'paginate'
			),
			'models' => array(
				//'Program' => array('find'),
				'ProgramDocument' => array('recursive')
				),
			)
			);
		
		$ProgramDocuments
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue(array('one','two')));
		
		$this->testAction("/ProgramDocuments/index");
		$this->assertEquals(2, count($this->vars['ProgramDocuments']));
		//debug(array('toto' => 'tata'));
		//throw new Exception(serialize($result));
		//debug($result, true);
	}

/**
 * testView method
 *
 * @return void
 */
 
	public function testView() {
		$data = array(
			'ProgramDocument' => array(
				'id' => 1,
				'name' => 'm4h',
				'country' => 'uganda',
				'created' => '2012-01-24 15:29:24',
				'modified' => '2012-01-24 15:29:24'
			)
		);
		
		$ProgramDocuments = $this->generate('ProgramDocuments', array(
			'models' => array(
				//'Program' => array('find'),
				'ProgramDocument' => array('exists','read')
				),
			)
			);
		
		$ProgramDocuments->ProgramDocument
			->expects($this->once())
			->method('exists')
			->will($this->returnValue(true));
		
		$ProgramDocuments->ProgramDocument
			->expects($this->once())
			->method('read')
			->will($this->returnValue($data));
		
		
		$this->testAction("/ProgramDocuments/view/1");
		//print_r($result);
		$this->assertEquals($data, 
			$this->vars['ProgramDocument']
			);
	}

/**
 * testAdd method
 *
 * @return void
 */

 	public function testAdd() {
		 $data = array(
		 	 'ProgramDocument' => array(
		 	 	 //'id' => '3',
		 	 	 'name' => 'NewProgramDocument',
		 	 	 'country' => 'Somewhere',
		 	 	 )
		 	 );
    		 $this->testAction('/ProgramDocuments/add', array('data' => $data, 'method' => 'post'));
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
