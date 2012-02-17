<?php
/* Programs Test cases generated on: 2012-01-24 15:39:09 : 1327408749*/
App::uses('HomeController', 'Controller');



/**
 * TestScriptsControllerController *
 */
class TestHomeController extends HomeController {
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
 * HomeController Test Case
 *
 */
class HomeControllerTestCase extends ControllerTestCase {
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
					'timezone' => 'UTC',
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

		$this->Home = new TestHomeController();
		//ClassRegistry::config(array('ds' => 'test'));
		
		$this->dropData();
		
		//$this->Home->Program->create();
		//$this->Home->Program->save($this->programData[0]['Program']);
	}

	protected function dropData() {
		//$this->Home->Program->deleteAll(true, false);
		//$this->Home->Group->deleteAll(true, false);
		
		//As this model is created on the fly, need to instantiate again
		$this->instanciateScriptModel();
		$this->Home->Script->deleteAll(true, false);
	}
	
	protected function instanciateScriptModel() {
		$options = array('database' => $this->programData[0]['Program']['database']);
		$this->Home->Script = new Script($options);
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
	public function testIndex_emptyProgram_asManager() {
		$Home = $this->generate('Home', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Home->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Home->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
					
		$Home->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls('4','2',$this->programData[0]['Program']['database'], $this->programData[0]['Program']['name']));

		//print_r($Home->Session->read());
			
		$this->testAction("/testurl/home", array('method' => 'get'));
		//print_r($this->vars);
		$this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
		$this->assertEquals($this->vars['isScriptEdit'], 'true');
		$this->assertEquals($this->vars['isParticipantAdd'], 'true');
		$this->assertEquals($this->vars['hasScriptActive'], '0');
		$this->assertEquals($this->vars['hasScriptDraft'], '0');
	}
	
	public function testIndex_existingDraftScript_asManager() {
		$Home = $this->generate('Home', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Home->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Home->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
		
		$Home->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls('4','2',$this->programData[0]['Program']['database'], $this->programData[0]['Program']['name']));

				
		$script = array('script' => 'do something');
		$this->instanciateScriptModel();
		$this->Home->Script->create();
		$this->Home->Script->save($script);
		
		$this->testAction("/testurl/home", array('method' => 'get'));
		//print_r($this->vars);
		$this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
		$this->assertEquals($this->vars['isScriptEdit'], 'true');
		$this->assertEquals($this->vars['isParticipantAdd'], 'true');
		$this->assertEquals($this->vars['hasScriptActive'], '0');
		$this->assertEquals($this->vars['hasScriptDraft'], '1');
	}
	
	public function testIndex_existingScripts_asManager() {
		$Home = $this->generate('Home', array(
			'components' => array(
				'Acl' => array('check'),
				'Session' => array('read')
			),
			'models' => array(
				'Program' => array('find', 'count'),
				'Group' => array()
			),
		));
		
		$Home->Acl
			->expects($this->any())
			->method('check')
			->will($this->returnValue('true'));
		
		$Home->Program
			->expects($this->once())
			->method('find')
			->will($this->returnValue($this->programData));
			
		$Home->Session
			->expects($this->any())
			->method('read')
			->will($this->onConsecutiveCalls('4','2',$this->programData[0]['Program']['database'], $this->programData[0]['Program']['name']));

			
		$script = array('script' => 'do something');
		$this->instanciateScriptModel();
		$this->Home->Script->create();
		$this->Home->Script->save($script);
		
		$this->testAction("/testurl/home", array('method' => 'get'));
		//print_r($this->vars);
		$this->assertEquals($this->vars['programName'], $this->programData[0]['Program']['name']);
		$this->assertEquals($this->vars['isScriptEdit'], 'true');
		$this->assertEquals($this->vars['isParticipantAdd'], 'true');
		$this->assertEquals($this->vars['hasScriptActive'], '0');
		$this->assertEquals($this->vars['hasScriptDraft'], '1');
	}
}
