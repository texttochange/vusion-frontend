<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('ScriptsController', 'Controller');
App::uses('Program', 'Model');


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
 * Data
 *
 */
	
	var $programData = array(
			0 => array( 
				'Program' => array(
					'name' => 'Test Name',
					'country' => 'Test Country',
					'url' => 'testurl',
					'database' => 'testdbprogram'
				)
			));
	
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Scripts = new TestScriptsController();
		ClassRegistry::config(array('ds' => 'test'));
		
		$this->dropData();
		
		//$this->Scripts->Program->create();
		//$this->Scripts->Program->save($this->programData[0]['Program']);
		
		
	}

	protected function dropData() {
		//$this->Scripts->Program->deleteAll(true, false);
		//$this->Scripts->Group->deleteAll(true, false);
		
		//As this model is created on the fly, need to instantiate again
		$this->instanciateScriptModel();
		$this->Scripts->Script->deleteAll(true, false);
	}
	
	protected function instanciateScriptModel() {
		$options = array('database' => $this->programData[0]['Program']['database']);
		$this->Scripts->Script = new Script($options);
	}
	
/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		
		$this->dropData();
		
		unset($this->Scripts);

		parent::tearDown();
	}

/**
 * testIndex method
 *
 * @return void
 */
	public function testIndex() {
		$Scripts = $this->generate('Scripts', array(
			'components' => array(
				'Acl' => array('check')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Scripts->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Scripts->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
		
		$this->testAction("/testurl/scripts", array('method' => 'get'));
		//print_r($this->vars);
		$this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
	}
	
	
	public function testIndex_returnDraft() {
				$Scripts = $this->generate('Scripts', array(
			'components' => array(
				'Acl' => array('check')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Scripts->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Scripts->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
			
		$draft = array('somescript' => 'do something');
		$this->instanciateScriptModel();
		$this->Scripts->Script->create();
		$this->Scripts->Script->save($draft);
		
		$this->testAction("/testurl/scripts", array('method' => 'get'));
		$this->assertEquals($this->vars['script']['somescript'], $draft['somescript']);
	}

/**
 * testView method
 *
 * @return void
 */
	public function testView() {
		$Scripts = $this->generate('Scripts', array(
			'components' => array(
				'Acl' => array('check')
			),
			'models' => array(
				'Program' => array('find'),
				'Group' => array()
			),
		));
		
		$Scripts->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Scripts->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
		
		$this->testAction('/testurl/scripts/draft', array('method' => 'get'));
		
	}



 	public function testAdd() {
 		
 		$Scripts = $this->generate('Scripts', array(
			'components' => array(
				'Acl' => array('check')
			),
			'models' => array(
				'Program' => array('find'),
				'Group' => array()
			),
		));
		
		$Scripts->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Scripts->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
		
			
 		$draft = array(
				'script' => array(
					'do' => 'something',
					)
		);
		
 		$this->testAction('/testurl/scripts/add', array('data' => $draft, 'method' => 'post'));
    	
 		//For the second testAction, need to remock the models
 		$Scripts = $this->generate('Scripts', array(
			'components' => array(
				'Acl' => array('check')
			),
			'models' => array(
				'Program' => array('find'),
				'Group' => array()
			),
		));
		
		$Scripts->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Scripts->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
 		
    		$updateDraft = array(
				'script' => array(
					'do' => 'something else',
					)
			);
    		
		$this->testAction('/testurl/scripts/add', array('data' => $updateDraft, 'method' => 'post'));
    		
    		$this->instanciateScriptModel();
		$currentDraft = $this->Scripts->Script->find('draft');
		$this->assertEquals(count($draft), 1);
    		$this->assertEquals($currentDraft[0]['Script']['script']['do'], $updateDraft['script']['do']);
    	}

    	/*
    	public function testActivateDraft(){
    		$Scripts = $this->generate('Script', array)
    	}
    	*/
    	
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
